<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\PlanLimitChecker;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SubscriptionPlan::create([
            'code' => 'launch',
            'name' => 'Launch',
            'price' => 99000,
            'billing_period' => 'monthly',
            'max_active_customers' => 150,
            'max_routers' => 1,
            'max_staff' => 1,
            'features' => ['whatsapp_broadcast'],
            'sort_order' => 1,
        ]);

        SubscriptionPlan::create([
            'code' => 'growth',
            'name' => 'Growth',
            'price' => 199000,
            'billing_period' => 'monthly',
            'max_active_customers' => 300,
            'max_routers' => 2,
            'max_staff' => 2,
            'features' => [],
            'sort_order' => 2,
        ]);
    }

    public function test_launch_plan_enforces_customer_limit(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = $tenant->packages()->create([
            'name' => 'Basic',
            'description' => 'Basic package',
            'price' => 50000,
            'speed' => '10Mbps',
        ]);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $checker = new PlanLimitChecker($tenant);
        $this->assertTrue($checker->canAddCustomer(), 'Should allow customers under limit');

        for ($i = 0; $i < 149; $i++) {
            $tenant->customers()->create([
                'name' => "Customer {$i}",
                'email' => "c{$i}@example.com",
                'address' => 'Test',
                'package_id' => $package->id,
                'status' => 'active',
                'join_date' => Carbon::now(),
            ]);
        }

        $checker = new PlanLimitChecker($tenant);
        $this->assertTrue($checker->canAddCustomer(), 'Should allow up to 150 (149 + 1)');

        $tenant->customers()->create([
            'name' => 'Customer 150',
            'email' => 'c150@example.com',
            'address' => 'Test',
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => Carbon::now(),
        ]);

        $checker = new PlanLimitChecker($tenant);
        $this->assertFalse($checker->canAddCustomer(), 'Should reject 151st customer');
    }

    public function test_growth_plan_blocks_whatsapp_feature(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $growthPlan = SubscriptionPlan::where('code', 'growth')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $growthPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $checker = new PlanLimitChecker($tenant);

        $this->assertFalse($checker->hasFeature('whatsapp_broadcast'), 'Growth plan should not have whatsapp_broadcast');
    }

    public function test_launch_plan_allows_whatsapp_feature(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $checker = new PlanLimitChecker($tenant);

        $this->assertTrue($checker->hasFeature('whatsapp_broadcast'), 'Launch plan should have whatsapp_broadcast');
    }

    public function test_expired_subscription_blocks_all_operations(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'expired',
            'started_at' => Carbon::now()->subYear(),
            'current_period_start' => Carbon::now()->subYear(),
            'current_period_end' => Carbon::now()->subDay(),
        ]);

        $checker = new PlanLimitChecker($tenant);

        $this->assertFalse($checker->hasActiveSubscription(), 'Expired subscription should not be active');
        $this->assertFalse($checker->canAddCustomer(), 'Should block customer creation');
        $this->assertTrue($checker->canAddStaff(), 'Staff is not limited by subscription');
        $this->assertFalse($checker->canAddRouter(), 'Should block router creation');
        $this->assertFalse($checker->hasFeature('whatsapp_broadcast'), 'Should block feature access');
    }

    public function test_subscription_status_transitions(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $this->assertTrue($subscription->isActive());
        $this->assertTrue($subscription->canAccess());

        $subscription->markAsCancelled();
        $this->assertTrue($subscription->isCancelled());
        $this->assertFalse($subscription->canAccess());

        // Second tenant for trial test
        $tenant2 = Tenant::factory()->create(['status' => 'active']);
        $trialSub = TenantSubscription::create([
            'tenant_id' => $tenant2->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'trial',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $this->assertTrue($trialSub->isTrial());
        $this->assertTrue($trialSub->canAccess());
    }

    public function test_tenant_can_have_subscription_assigned(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        $subscription = TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $this->assertEquals($launchPlan->id, $tenant->activeSubscription->subscription_plan_id);
        $this->assertEquals('active', $tenant->activeSubscription->status);
    }
}