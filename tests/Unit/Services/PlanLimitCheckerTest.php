<?php

namespace Tests\Unit\Services;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Services\PlanLimitChecker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanLimitCheckerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();

        SubscriptionPlan::create([
            'code' => 'launch',
            'name' => 'Launch',
            'price' => 99000,
            'billing_period' => 'monthly',
            'max_active_customers' => 150,
            'max_routers' => 1,
            'max_staff' => 1,
            'features' => ['whatsapp_broadcast', 'audit_log'],
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

    public function test_launched_plan_allows_150_customers(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = $tenant->packages()->create(['name' => 'Test Package', 'description' => 'Test Description', 'price' => 10000, 'speed' => '10Mbps']);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        // 149 customers — limit not reached yet
        for ($i = 0; $i < 149; $i++) {
            $tenant->customers()->create([
                'name' => "Customer {$i}",
                'email' => "customer{$i}@example.com",
                'address' => 'Test Address',
                'package_id' => $package->id,
                'status' => 'active',
                'join_date' => Carbon::now(),
            ]);
        }

        // Clear cache to refresh count
        Cache::forget("tenant_customer_count:{$tenant->id}");
        Cache::forget("tenant_active_subscription:{$tenant->id}");

        $checker = new PlanLimitChecker($tenant);
        $this->assertTrue($checker->canAddCustomer(), 'Should allow adding customer when under limit (149 < 150)');

        // 150th customer — limit reached
        $tenant->customers()->create([
            'name' => 'Customer 150',
            'email' => 'customer150@example.com',
            'address' => 'Test Address',
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => Carbon::now(),
        ]);

        // Clear cache again
        Cache::forget("tenant_customer_count:{$tenant->id}");
        Cache::forget("tenant_active_subscription:{$tenant->id}");

        $checker = new PlanLimitChecker($tenant);
        $this->assertFalse($checker->canAddCustomer(), 'Should reject when at limit (150 = 150)');
    }

    public function test_growth_plan_allows_300_customers(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = $tenant->packages()->create(['name' => 'Test Package', 'description' => 'Test Description', 'price' => 10000, 'speed' => '10Mbps']);
        $growthPlan = SubscriptionPlan::where('code', 'growth')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $growthPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        // 299 customers — under limit
        for ($i = 0; $i < 299; $i++) {
            $tenant->customers()->create([
                'name' => "Customer {$i}",
                'email' => "customer{$i}@example.com",
                'address' => 'Test Address',
                'package_id' => $package->id,
                'status' => 'active',
                'join_date' => Carbon::now(),
            ]);
        }

        // Clear cache to refresh count
        Cache::forget("tenant_customer_count:{$tenant->id}");
        Cache::forget("tenant_active_subscription:{$tenant->id}");

        $checker = new PlanLimitChecker($tenant);
        $this->assertTrue($checker->canAddCustomer(), 'Should allow adding customer when under limit (299 < 300)');

        // 300th customer — limit reached
        $tenant->customers()->create([
            'name' => 'Customer 300',
            'email' => 'customer300@example.com',
            'address' => 'Test Address',
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => Carbon::now(),
        ]);

        // Clear cache again
        Cache::forget("tenant_customer_count:{$tenant->id}");
        Cache::forget("tenant_active_subscription:{$tenant->id}");

        $checker = new PlanLimitChecker($tenant);
        $this->assertFalse($checker->canAddCustomer(), 'Should reject when at limit (300 = 300)');
    }

    public function test_has_feature_for_launch_plan(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        $checker = new PlanLimitChecker($tenant);

        $this->assertTrue($checker->hasFeature('whatsapp_broadcast'), 'Launch plan should have whatsapp_broadcast feature');
        $this->assertTrue($checker->hasFeature('audit_log'), 'Launch plan should have audit_log feature');
        $this->assertFalse($checker->hasFeature('api_integration'), 'Launch plan should not have api_integration feature');
    }

    public function test_has_feature_for_growth_plan(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $growthPlan = SubscriptionPlan::where('code', 'growth')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $growthPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        $checker = new PlanLimitChecker($tenant);

        $this->assertFalse($checker->hasFeature('whatsapp_broadcast'), 'Growth plan should not have whatsapp_broadcast feature');
        $this->assertFalse($checker->hasFeature('audit_log'), 'Growth plan should not have audit_log feature');
    }

    public function test_can_add_staff_limits(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $launchPlan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        $checker = new PlanLimitChecker($tenant);
        $this->assertTrue($checker->canAddStaff(), 'Launch plan should allow 1 staff member');

        $tenant->users()->create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);

        // Clear cache to refresh count
        Cache::forget("tenant_staff_count:{$tenant->id}");
        Cache::forget("tenant_active_subscription:{$tenant->id}");

        $checker = new PlanLimitChecker($tenant);
        $this->assertTrue($checker->canAddStaff(), 'Staff limit is unlimited, so should still allow addition');
    }

    public function test_no_active_subscription_disallows_access(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $this->assertFalse($tenant->activeSubscription()->exists(), 'Tenant should have no active subscription');

        $checker = new PlanLimitChecker($tenant);

        $this->assertFalse($checker->canAddCustomer(), 'Should disallow customer addition without active subscription');
        $this->assertFalse($checker->canAddRouter(), 'Should disallow router addition without active subscription');
        $this->assertTrue($checker->canAddStaff(), 'Staff is not limited by subscription');
        $this->assertFalse($checker->hasFeature('whatsapp_broadcast'), 'Should disallow feature access without active subscription');
    }

    public function test_trial_subscription_counts_as_active(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $plan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'trial',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        $checker = new PlanLimitChecker($tenant);

        $this->assertTrue($checker->hasActiveSubscription(), 'Trial should count as active');
        $this->assertTrue($checker->canAddCustomer(), 'Trial should allow customer addition');
        $this->assertTrue($checker->canAddStaff(), 'Trial should allow staff addition');
        $this->assertTrue($checker->hasFeature('whatsapp_broadcast'), 'Trial should allow feature access');
    }

    public function test_expired_subscription_disallows_access(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $plan = SubscriptionPlan::where('code', 'launch')->first();

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'expired',
            'started_at' => Carbon::now()->subYear(),
            'current_period_start' => Carbon::now()->subYear(),
            'current_period_end' => Carbon::now()->subDay(),
        ]);

        $checker = new PlanLimitChecker($tenant);

        $this->assertFalse($checker->hasActiveSubscription(), 'Expired subscription should not count as active');
        $this->assertFalse($checker->canAddCustomer(), 'Should disallow customer addition with expired subscription');
        $this->assertTrue($checker->canAddStaff(), 'Staff is not limited by subscription');
        $this->assertFalse($checker->hasFeature('whatsapp_broadcast'), 'Should disallow feature access with expired subscription');
    }
}
