<?php

namespace Tests\Unit\Services;

use App\Models\OltDevice;
use App\Models\OltOnu;
use App\Models\Tenant;
use App\Services\OltService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->tenant = Tenant::factory()->create(['status' => 'active']);
    }

    protected function makeOlt(string $brand): OltDevice
    {
        return OltDevice::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'OLT Tes',
            'brand' => $brand,
            'management_host' => '192.168.1.1',
            'username' => 'admin',
            'password' => 'secret',
            'ssh_port' => 22,
            'status' => 'offline',
            'onu_count' => 0,
        ]);
    }

    protected function makeOnu(OltDevice $olt, array $overrides = []): OltOnu
    {
        return OltOnu::create(array_merge([
            'olt_id' => $olt->id,
            'tenant_id' => $this->tenant->id,
            'name' => 'RT01-ONU-01',
            'serial' => '484C4C-ABCDEF',
            'pon_id' => '1',
            'onu_id' => 1,
            'vlan' => 110,
            'status' => 'provisioning',
        ], $overrides));
    }

    public function test_fiberhome_builds_fiberhome_add_ont_commands(): void
    {
        $olt = $this->makeOlt('fiberhome');
        $onu = $this->makeOnu($olt);

        $commands = (new OltService($olt))->buildAddOntCommands($onu);

        $this->assertIsArray($commands);
        $this->assertContains('config', $commands);
        $this->assertContains('interface gpon 0/1', $commands);
        $this->assertContains('ont add 1 484C4C-ABCDEF', $commands);
        $this->assertContains('service-port vlan 110 ont 1', $commands);
        $this->assertContains('save', $commands);
    }

    public function test_vsol_builds_vsol_specific_add_ont_commands(): void
    {
        $olt = $this->makeOlt('vsol');
        $onu = $this->makeOnu($olt);

        $commands = (new OltService($olt))->buildAddOntCommands($onu);

        $this->assertContains('pon 484C4C-ABCDEF add', $commands);
        $this->assertContains('pon 484C4C-ABCDEF bind-vlan 110', $commands);
        $this->assertContains('save', $commands);
    }

    public function test_provision_marks_onu_failed_when_offline(): void
    {
        $olt = $this->makeOlt('huawei');
        $onu = $this->makeOnu($olt);

        $result = (new OltService($olt))->provisionOnt($onu);

        $this->assertFalse($result['success']);
        $onu->refresh();
        $this->assertEquals('failed', $onu->status);
        $this->assertNull($onu->provisioned_at);
    }
}
