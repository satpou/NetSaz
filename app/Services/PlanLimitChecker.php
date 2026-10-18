<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Cache;

class PlanLimitChecker
{
    public function __construct(protected Tenant $tenant)
    {
    }

    /**
     * Retrieve the active (or trial) subscription for this tenant, with caching.
     */
    protected function getActiveSubscription(): ?TenantSubscription
    {
        $cacheKey = "tenant_active_subscription:{$this->tenant->id}";

        return Cache::remember($cacheKey, 60, function () {
            return TenantSubscription::where('tenant_id', $this->tenant->id)
                ->whereIn('status', ['active', 'trial'])
                ->latest()
                ->first();
        });
    }

    /**
     * Check whether the tenant has an accessible subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->getActiveSubscription() !== null;
    }

    /**
     * Check if a new customer can be added within the plan limit.
     */
    public function canAddCustomer(): bool
    {
        $subscription = $this->getActiveSubscription();

        if (! $subscription) {
            return false;
        }

        $plan = $subscription->plan;
        if (! $plan) {
            return false;
        }

        $maxCustomers = $plan->max_active_customers;

        // 0 means unlimited
        if ($maxCustomers === 0) {
            return true;
        }

        // Count live (not cached) so limit is enforced immediately after each create.
        $currentCount = $this->tenant->customers()->where('status', 'active')->count();

        return $currentCount < $maxCustomers;
    }

    /**
     * Check if a new router/NAS can be added within the plan limit.
     */
    public function canAddRouter(): bool
    {
        $subscription = $this->getActiveSubscription();

        if (! $subscription) {
            return false;
        }

        return true; // Router limit checking can be extended later
    }

    /**
     * Check if a new staff member can be added. Staff is not subscription-limited.
     */
    public function canAddStaff(): bool
    {
        return true;
    }

    /**
     * Check if the plan includes the given feature key.
     */
    public function hasFeature(string $feature): bool
    {
        $subscription = $this->getActiveSubscription();

        if (! $subscription) {
            return false;
        }

        $plan = $subscription->plan;
        if (! $plan) {
            return false;
        }

        return $plan->hasFeature($feature);
    }
}
