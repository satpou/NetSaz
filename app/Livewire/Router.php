<?php

namespace App\Livewire;

use App\Models\RadiusHub;
use App\Models\RadiusHubMember;
use App\Models\NasServer;
use Livewire\Component;

class Router extends Component
{
    public string $view = 'list';
    public string $newDbName = '';
    public string $newDbServerHost = '';
    public string $selectedNasId = '';
    public string $generatedScript = '';
    public string $scriptContext = '';

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function createRadiusHub(): void
    {
        $this->validate([
            'newDbName' => 'required|string|max:100',
            'newDbServerHost' => 'required|string|max:255',
        ]);

        $tenantId = session('active_tenant_id');

        RadiusHub::create([
            'tenant_id' => $tenantId,
            'db_name' => $this->newDbName,
            'server_host' => $this->newDbServerHost,
            'radius_port' => 1812,
            'radius_secret' => bin2hex(random_bytes(16)),
            'status' => 'active',
            'provisioned_at' => now(),
        ]);

        $this->newDbName = '';
        $this->newDbServerHost = '';
    }

    public function joinNas(): void
    {
        $this->validate([
            'selectedNasId' => 'required|exists:nas_servers,id',
        ]);

        $tenantId = session('active_tenant_id');
        $hub = RadiusHub::where('tenant_id', $tenantId)->latest()->first();

        if (! $hub) {
            $this->addError('selectedNasId', 'Tidak ada Radius Hub aktif.');
            return;
        }

        RadiusHubMember::create([
            'radius_hub_id' => $hub->id,
            'nas_server_id' => $this->selectedNasId,
            'joined_at' => now(),
        ]);

        $this->selectedNasId = '';
    }

    public function leaveNas(int $memberId): void
    {
        $member = RadiusHubMember::find($memberId);
        if ($member) {
            $member->delete();
        }
    }

    public function generateScript(int $memberId): void
    {
        $member = RadiusHubMember::with(['hub', 'nasServer'])->find($memberId);

        if (! $member) {
            return;
        }

        $this->scriptContext = $member->nasServer->name ?? '';
        $hub = $member->hub;

        $this->generatedScript = implode("\n", [
            "# NetSaz RADIUS Hub Configuration",
            "# NAS: {$this->scriptContext}",
            "/radius add address={$hub->server_host} secret={$hub->radius_secret} service=login",
            "/radius add address={$hub->server_host} port={$hub->radius_port} secret={$hub->radius_secret} service=ppp",
        ]);
    }

    public function render()
    {
        return view('livewire.router');
    }
}
