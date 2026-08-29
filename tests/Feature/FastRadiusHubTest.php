<?php

namespace Tests\Feature;

use App\Livewire\Router;
use App\Models\NasServer;
use App\Models\RadiusHub;
use App\Models\RadiusHubMember;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FastRadiusHubTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->tenant = Tenant::factory()->create(['status' => 'active']);

        $plan = SubscriptionPlan::create([
            'code' => 'pro',
            'name' => 'Pro',
            'price' => 299000,
            'billing_period' => 'monthly',
            'max_active_customers' => 500,
            'max_routers' => 10,
            'max_staff' => 10,
            'features' => ['radius_hub', 'olt_management'],
            'sort_order' => 3,
        ]);

        TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->user->assignRole('super_admin');

        $this->withSession(['active_tenant_id' => $this->tenant->id]);
    }

    public function test_create_hub_provisions_with_secret_and_active_status(): void
    {
        Livewire::actingAs($this->user)
            ->test(Router::class)
            ->call('setView', 'buatdb')
            ->set('newDbName', 'rad_test_isp')
            ->set('newDbServerHost', '103.1.2.3')
            ->call('createRadiusHub')
            ->assertHasNoErrors();

        $hub = RadiusHub::first();

        $this->assertNotNull($hub);
        $this->assertEquals('rad_test_isp', $hub->db_name);
        $this->assertEquals('103.1.2.3', $hub->server_host);
        $this->assertEquals('active', $hub->status);
        $this->assertNotNull($hub->radius_secret);
        $this->assertNotNull($hub->provisioned_at);
    }

    public function test_nas_can_join_hub_and_script_is_generated(): void
    {
        $hub = RadiusHub::create([
            'tenant_id' => $this->tenant->id,
            'db_name' => 'rad_test_isp',
            'server_host' => '103.1.2.3',
            'radius_port' => 1812,
            'radius_secret' => 'secret123',
            'status' => 'active',
            'provisioned_at' => now(),
        ]);

        $nas = NasServer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router Pusat',
            'shortname' => 'pusat',
            'host' => '192.168.1.1',
            'status' => 'offline',
            'connection_type' => 'secure_tunnel',
        ]);

        Livewire::actingAs($this->user)
            ->test(Router::class)
            ->call('setView', 'hub')
            ->set('selectedNasId', (string) $nas->id)
            ->call('joinNas')
            ->assertHasNoErrors();

        $member = RadiusHubMember::where('radius_hub_id', $hub->id)
            ->where('nas_server_id', $nas->id)
            ->first();

        $this->assertNotNull($member);

        Livewire::actingAs($this->user)
            ->test(Router::class)
            ->call('setView', 'hub')
            ->call('generateScript', $member->id)
            ->assertSet('scriptContext', 'Router Pusat');

        $script = RadiusHub::first();
        $this->assertStringContainsString('103.1.2.3', Livewire::actingAs($this->user)
            ->test(Router::class)
            ->call('setView', 'hub')
            ->call('generateScript', $member->id)
            ->get('generatedScript'));
    }

    public function test_nas_can_leave_hub(): void
    {
        $hub = RadiusHub::create([
            'tenant_id' => $this->tenant->id,
            'db_name' => 'rad_test_isp',
            'server_host' => '103.1.2.3',
            'radius_port' => 1812,
            'radius_secret' => 'secret123',
            'status' => 'active',
            'provisioned_at' => now(),
        ]);

        $nas = NasServer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router Cabang',
            'shortname' => 'cabang',
            'host' => '192.168.2.1',
            'status' => 'offline',
            'connection_type' => 'secure_tunnel',
        ]);

        $member = RadiusHubMember::create([
            'radius_hub_id' => $hub->id,
            'nas_server_id' => $nas->id,
            'joined_at' => now(),
        ]);

        Livewire::actingAs($this->user)
            ->test(Router::class)
            ->call('setView', 'hub')
            ->call('leaveNas', $member->id);

        $this->assertDatabaseMissing('radius_hub_members', ['id' => $member->id]);
    }
}
