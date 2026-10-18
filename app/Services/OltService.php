<?php

namespace App\Services;

use App\Models\OltDevice;
use App\Models\OltOnu;

class OltService
{
    public function __construct(protected OltDevice $olt)
    {
    }

    public function buildAddOntCommands(OltOnu $onu): array
    {
        return match ($this->olt->brand) {
            'vsol' => [
                'config',
                "interface pon {$onu->pon_id}",
                "pon {$onu->serial} add",
                "pon {$onu->serial} desc {$onu->name}",
                "pon {$onu->serial} bind-vlan {$onu->vlan}",
                'save',
            ],
            'huawei' => [
                'config',
                "interface gpon 0/{$onu->pon_id}",
                "ont add {sn {$onu->serial}} profile default",
                "service-port vlan {$onu->vlan} ont {$onu->onu_id}",
                'save',
            ],
            default => [ // fiberhome
                'config',
                "interface gpon 0/{$onu->pon_id}",
                "ont add {$onu->onu_id} {$onu->serial}",
                "service-port vlan {$onu->vlan} ont {$onu->onu_id}",
                'save',
            ],
        };
    }

    public function provisionOnt(OltOnu $onu): array
    {
        if ($this->olt->status !== 'online') {
            $onu->update(['status' => 'failed']);

            return ['success' => false, 'error' => 'OLT sedang offline.'];
        }

        $result = $this->executeCommands($this->buildAddOntCommands($onu));

        if ($result['success']) {
            $onu->update(['status' => 'active', 'provisioned_at' => now()]);
            $this->olt->increment('onu_count');
        } else {
            $onu->update(['status' => 'failed']);
        }

        return $result;
    }

    protected function executeCommands(array $commands): array
    {
        $target = escapeshellarg(($this->olt->username ?? 'admin').'@'.$this->olt->management_host);
        $command = sprintf(
            'ssh -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p %d %s 2>&1',
            $this->olt->ssh_port ?: 22,
            $target
        );

        $process = @proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

        if (! is_resource($process)) {
            return ['success' => false, 'error' => 'Gagal menjalankan proses SSH.'];
        }

        fwrite($pipes[0], implode("\n", $commands)."\nexit\n");
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            return ['success' => false, 'error' => trim($output) ?: 'Perintah SSH gagal dijalankan.'];
        }

        return ['success' => true, 'output' => $output];
    }
}
