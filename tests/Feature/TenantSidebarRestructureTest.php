<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSidebarRestructureTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->tenant = Tenant::factory()->create([
            'status' => 'active',
            'slug' => 'test-isp',
        ]);

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
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->user->assignRole('super_admin');
        $this->withSession(['active_tenant_id' => $this->tenant->id]);
    }

    public function test_tenant_can_access_all_sidebar_pages(): void
    {
        $this->actingAs($this->user);
        $slugParam = ['tenant_slug' => $this->tenant->slug];

        // Group 1: Router
        $this->get(route('routers.index', $slugParam))->assertStatus(200)->assertSee('Router MikroTik');
        $this->get(route('devices.radius', $slugParam))->assertStatus(200)->assertSee('FastRADIUS Hub');
        $this->get(route('devices.olt', $slugParam))->assertStatus(200)->assertSee('Manajemen OLT');
        $this->get(route('devices.genieacs', $slugParam))->assertStatus(200)->assertSee('GenieACS');

        // Group 2: Network
        $this->get(route('network.monitoring', $slugParam))->assertStatus(200)->assertSee('Network Monitoring');
        $this->get(route('network.ip', $slugParam))->assertStatus(200)->assertSee('IP Management');
        $this->get(route('network.logs', $slugParam))->assertStatus(200)->assertSee('Network');
        $this->get(route('network.bandwidth', $slugParam))->assertStatus(200)->assertSee('Bandwidth Utilization');
        $this->get(route('network.fault', $slugParam))->assertStatus(200)->assertSee('Fault Detection');

        // Group 3: Pelanggan & Billing
        $this->get(route('customers.index', $slugParam))->assertStatus(200);
        $this->get(route('areas.index', $slugParam))->assertStatus(200);
        $this->get(route('packages.index', $slugParam))->assertStatus(200);
        $this->get(route('invoices.index', $slugParam))->assertStatus(200);
        $this->get(route('payments.index', $slugParam))->assertStatus(200);
        $this->get(route('billing.auto-isolir', $slugParam))->assertStatus(200)->assertSee('Auto Isolir');
        $this->get(route('partners.index', $slugParam))->assertStatus(200);
        $this->get(route('tickets.index', $slugParam))->assertStatus(200);

        // Group 4: Operasional
        $this->get(route('operasional.staff', $slugParam))->assertStatus(200)->assertSee('Manajemen Staff');
        $this->get(route('import.index', $slugParam))->assertStatus(200);
        $this->get(route('operasional.export', $slugParam))->assertStatus(200)->assertSee('Pusat Ekspor Data Operasional');
        $this->get(route('reports.index', $slugParam))->assertStatus(200);

        // Group 5: Deployment Center
        $this->get(route('deployment.index', $slugParam))->assertStatus(200)->assertSee('Deployment Center');

        // Group 6: WhatsApp
        $this->get(route('tenant.settings.edit', $slugParam))->assertStatus(200);
        $this->get(route('whatsapp.templates', $slugParam))->assertStatus(200)->assertSee('Template Notifikasi WhatsApp');
        $this->get(route('whatsapp.broadcast', $slugParam))->assertStatus(200)->assertSee('Broadcast WhatsApp');
    }
}
