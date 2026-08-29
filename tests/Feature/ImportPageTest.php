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

class ImportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_index_renders(): void
    {
        $this->seed(RoleSeeder::class);
        $tenant = Tenant::factory()->create(['status' => 'active']);

        $plan = SubscriptionPlan::create([
            'code' => 'growth',
            'name' => 'Growth',
            'price' => 199000,
            'billing_period' => 'monthly',
            'max_active_customers' => 300,
            'max_routers' => 2,
            'max_staff' => 2,
            'features' => ['export_data'],
            'sort_order' => 2,
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)
            ->withSession(['active_tenant_id' => $tenant->id])
            ->get(route('import.index', ['tenant_slug' => $tenant->slug]));

        $response->assertOk();
        $response->assertSee('Import Cepat (Deteksi Otomatis)');
        $response->assertSee('Import Manual (CSV)');
    }
}
