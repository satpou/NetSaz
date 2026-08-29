<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PlanDowngradeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected SubscriptionPlan $planHighLimit;
    protected SubscriptionPlan $planLowLimit;
    protected TenantSubscription $tenantSubscription;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super admin user
        $user = \App\Models\User::factory()->create([
            'role' => 'super_admin',
        ]);
        Auth::login($user);

        // Setup subscription plans manually
        $this->planHighLimit = SubscriptionPlan::create([
            'code' => 'growth',
            'name' => 'Growth Plan',
            'price' => 150000,
            'billing_period' => 'monthly',
            'max_active_customers' => 5,
            'max_routers' => 2,
            'max_staff' => 3,
            'features' => ['whatsapp_broadcast', 'api_access'],
            'sort_order' => 2,
        ]);

        $this->planLowLimit = SubscriptionPlan::create([
            'code' => 'launch',
            'name' => 'Launch Plan',
            'price' => 99000,
            'billing_period' => 'monthly',
            'max_active_customers' => 2,
            'max_routers' => 1,
            'max_staff' => 1,
            'features' => ['whatsapp_broadcast'],
            'sort_order' => 1,
        ]);

        // Create tenant
        $this->tenant = Tenant::factory()->create(['status' => 'active']);

        // Create an active subscription
        $this->tenantSubscription = TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'subscription_plan_id' => $this->planHighLimit->id,
            'status' => 'active',
            'started_at' => Carbon::now()->subMonth(),
            'current_period_start' => Carbon::now()->subMonth(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        // Create 5 active customers (exceeds low plan limit)
        $package = \App\Models\Package::factory()->create(['tenant_id' => $this->tenant->id]);
        for ($i = 0; $i < $this->planHighLimit->max_active_customers; $i++) {
            $customer = Customer::factory()->create([
                'tenant_id' => $this->tenant->id,
                'status' => 'active',
                'package_id' => $package->id,
            ]);
        }
    }

    public function test_downgrade_plan_when_customer_count_exceeds_new_limit(): void
    {
        // Create 3 more customers to exceed low plan limit (which is 2)
        $package = \App\Models\Package::factory()->create(['tenant_id' => $this->tenant->id]);
        for ($i = 0; $i < 3; $i++) {
            Customer::factory()->create([
                'tenant_id' => $this->tenant->id,
                'status' => 'active',
                'package_id' => $package->id,
            ]);
        }

        // Downgrade to a lower limit plan (limit 2)
        $response = $this->put(route('admin.tenant-subscriptions.update', $this->tenantSubscription->id), [
            'subscription_plan_id' => $this->planLowLimit->id,
        ]);

        // Should block downgrade when customer count exceeds new limit
        $response->assertSessionHas('error');
    }
}