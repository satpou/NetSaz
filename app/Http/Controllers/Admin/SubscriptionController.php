<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request)
    {
        $query = TenantSubscription::with(['tenant', 'plan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_id')) {
            $query->where('subscription_plan_id', $request->plan_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('tenant', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $subscriptions = $query->paginate(15)->withQueryString();
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        $counts = [
            'total' => TenantSubscription::count(),
            'active' => TenantSubscription::where('status', 'active')->count(),
            'trial' => TenantSubscription::where('status', 'trial')->count(),
            'expired' => TenantSubscription::where('status', 'expired')->count(),
            'cancelled' => TenantSubscription::where('status', 'cancelled')->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'plans', 'counts'));
    }

    public function create()
    {
        $tenants = Tenant::orderBy('name')->get();
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.subscriptions.create', compact('tenants', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:active,trial,expired,cancelled',
            'duration_months' => 'required|integer|min:1|max:36',
        ]);

        $now = Carbon::now();
        $end = (clone $now)->addMonths((int) $validated['duration_months']);

        TenantSubscription::create([
            'tenant_id' => $validated['tenant_id'],
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'status' => $validated['status'],
            'started_at' => $now,
            'current_period_start' => $now,
            'current_period_end' => $end,
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', 'Subscription baru berhasil ditambahkan.');
    }

    public function edit(TenantSubscription $subscription)
    {
        $subscription->load(['tenant', 'plan']);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.subscriptions.edit', compact('subscription', 'plans'));
    }

    public function update(Request $request, TenantSubscription $subscription)
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'status' => 'required|in:active,trial,expired,cancelled',
            'current_period_end' => 'nullable|date',
        ]);

        $tenant = $subscription->tenant;
        $newPlan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);

        // Check active customer count limit if downgrading
        if ($newPlan->max_active_customers > 0 && $tenant) {
            $customerCount = $tenant->customers()->where('status', 'active')->count();
            if ($customerCount > $newPlan->max_active_customers) {
                return back()->with('error', "Gagal update: Tenant memiliki {$customerCount} pelanggan aktif, melebihi kapasitas paket baru ({$newPlan->max_active_customers}).");
            }
        }

        $subscription->update([
            'subscription_plan_id' => $validated['subscription_plan_id'],
            'status' => $validated['status'],
            'current_period_end' => $validated['current_period_end'] ? Carbon::parse($validated['current_period_end']) : $subscription->current_period_end,
            'cancelled_at' => $validated['status'] === 'cancelled' ? Carbon::now() : null,
        ]);

        return redirect()->route('admin.subscriptions.index')
            ->with('success', "Subscription tenant '{$tenant?->name}' berhasil diperbarui.");
    }

    public function extend(Request $request, TenantSubscription $subscription)
    {
        $months = (int) $request->input('months', 1);
        $baseDate = $subscription->current_period_end && $subscription->current_period_end->isFuture()
            ? $subscription->current_period_end
            : Carbon::now();

        $subscription->update([
            'status' => 'active',
            'current_period_end' => (clone $baseDate)->addMonths($months),
        ]);

        return back()->with('success', "Masa aktif langganan berhasil diperpanjang {$months} bulan.");
    }

    public function destroy(TenantSubscription $subscription)
    {
        $subscription->markAsCancelled();
        return back()->with('success', 'Subscription berhasil dibatalkan.');
    }
}
