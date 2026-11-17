<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use App\Models\TenantSubscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with(['activeSubscription.plan'])
            ->withCount(['customers', 'users', 'packages', 'invoices']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tenants = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'trial' => Tenant::where('status', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
        ];

        return view('admin.tenants.index', compact('tenants', 'counts'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load([
            'users',
            'packages',
            'subscriptions.plan',
            'activeSubscription.plan',
        ]);

        $tenant->loadCount(['customers', 'invoices', 'payments']);

        $recentInvoices = $tenant->invoices()->latest()->limit(5)->get();
        $recentPayments = $tenant->payments()->latest()->limit(5)->get();
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.tenants.show', compact('tenant', 'recentInvoices', 'recentPayments', 'plans'));
    }

    public function edit(Tenant $tenant)
    {
        return view('admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/', Rule::unique('tenants', 'slug')->ignore($tenant->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('tenants', 'email')->ignore($tenant->id)],
            'whatsapp_number' => 'nullable|string|max:25',
            'status' => 'required|in:active,trial,suspended',
        ]);

        $tenant->update($validated);

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,trial,suspended',
        ]);

        $tenant->update(['status' => $validated['status']]);

        $statusLabels = [
            'active' => 'diaktifkan',
            'trial' => 'diubah ke trial',
            'suspended' => 'ditangguhkan (suspend)',
        ];

        return back()->with('success', "Status tenant '{$tenant->name}' berhasil {$statusLabels[$validated['status']]}.");
    }

    public function destroy(Tenant $tenant)
    {
        $name = $tenant->name;
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant '{$name}' berhasil dihapus (soft delete).");
    }
}
