<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantSubscription;
use App\Services\PlanLimitChecker;
use Illuminate\Http\Request;

class TenantSubscriptionController extends Controller
{
    public function update(Request $request, TenantSubscription $tenantSubscription)
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $tenant = $tenantSubscription->tenant;
        $newPlanId = $validated['subscription_plan_id'];

        // Load new plan
        $newPlan = \App\Models\SubscriptionPlan::findOrFail($newPlanId);

        // Check if downgrade would exceed customer limit
        if ($newPlan->max_active_customers > 0) {
            $currentCustomerCount = $tenant->customers()->where('status', 'active')->count();
            if ($currentCustomerCount > $newPlan->max_active_customers) {
                return redirect()->back()->with(
                    'error',
                    "Tidak dapat downgrade: jumlah pelanggan aktif ({$currentCustomerCount}) melebihi batas plan baru ({$newPlan->max_active_customers})."
                );
            }
        }

        $tenantSubscription->update(['subscription_plan_id' => $newPlanId]);

        return redirect()->back()->with('success', 'Langganan berhasil diperbarui.');
    }
}
