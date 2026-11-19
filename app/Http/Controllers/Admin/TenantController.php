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
        $status = $request->query('status');

        $query = Tenant::with(['activeSubscription.plan'])
            ->withCount(['customers', 'users', 'packages', 'invoices']);

        if ($status === 'trashed') {
            $query->onlyTrashed();
        } elseif ($request->filled('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        $tenants = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'total' => Tenant::count(),
            'active' => Tenant::where('status', 'active')->count(),
            'trial' => Tenant::where('status', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'trashed' => Tenant::onlyTrashed()->count(),
        ];

        return view('admin.tenants.index', compact('tenants', 'counts', 'status'));
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

        // Deactivate tenant users
        \App\Models\User::where('tenant_id', $tenant->id)->update(['is_active' => false]);

        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant ISP '{$name}' berhasil dihapus ke kotak sampah (Trash). Anda dapat memulihkannya kapan saja.");
    }

    public function restore($id)
    {
        $tenant = Tenant::onlyTrashed()->findOrFail($id);
        $tenant->restore();

        // Reactivate super_admin user
        \App\Models\User::where('tenant_id', $tenant->id)->where('role', 'super_admin')->update(['is_active' => true]);

        return redirect()->route('admin.tenants.index', ['status' => 'trashed'])
            ->with('success', "Tenant ISP '{$tenant->name}' berhasil dipulihkan kembali.");
    }

    public function forceDelete($id)
    {
        $tenant = Tenant::withTrashed()->findOrFail($id);
        $name = $tenant->name;

        self::purgeTenantData($tenant);

        return redirect()->route('admin.tenants.index', ['status' => 'trashed'])
            ->with('success', "Tenant ISP '{$name}' telah dihapus permanen beserta seluruh datanya.");
    }

    public function emptyTrash()
    {
        $trashed = Tenant::onlyTrashed()->get();
        $count = $trashed->count();

        foreach ($trashed as $t) {
            self::purgeTenantData($t);
        }

        return redirect()->route('admin.tenants.index', ['status' => 'trashed'])
            ->with('success', "Kotak sampah berhasil dikosongkan. {$count} tenant telah dihapus permanen.");
    }

    /**
     * Safely and permanently deletes all tenant data in reverse-dependency order.
     */
    public static function purgeTenantData(Tenant $tenant): void
    {
        $tenantId = $tenant->id;
        $driver = \Illuminate\Support\Facades\DB::getDriverName();

        \Illuminate\Support\Facades\DB::transaction(function () use ($tenant, $tenantId, $driver) {
            if ($driver === 'mysql') {
                \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($driver === 'sqlite') {
                \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = OFF;');
            }

            try {
                // Payments & gateway logs
                \Illuminate\Support\Facades\DB::table('payments')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('payment_gateway_logs')->where('tenant_id', $tenantId)->delete();

                // Invoice items & reminders & invoices
                \Illuminate\Support\Facades\DB::table('invoice_items')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('invoice_reminders')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('invoices')->where('tenant_id', $tenantId)->delete();

                // Customer OTPs, accounts, customers
                \Illuminate\Support\Facades\DB::table('customer_otps')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('mikrotik_accounts')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('customers')->where('tenant_id', $tenantId)->delete();

                // Packages & Areas
                \Illuminate\Support\Facades\DB::table('packages')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('areas')->where('tenant_id', $tenantId)->delete();

                // Tickets & Partners
                \Illuminate\Support\Facades\DB::table('tickets')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('partners')->where('tenant_id', $tenantId)->delete();

                // Router agent & commands
                \Illuminate\Support\Facades\DB::table('router_agent_commands')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('router_agents')->where('tenant_id', $tenantId)->delete();

                // OLT & GenieACS
                \Illuminate\Support\Facades\DB::table('olt_onus')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('olt_devices')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('genieacs_configs')->where('tenant_id', $tenantId)->delete();

                // RADIUS
                $hubIds = \Illuminate\Support\Facades\DB::table('radius_hubs')->where('tenant_id', $tenantId)->pluck('id');
                if ($hubIds->isNotEmpty()) {
                    \Illuminate\Support\Facades\DB::table('radius_hub_members')->whereIn('radius_hub_id', $hubIds)->delete();
                }
                \Illuminate\Support\Facades\DB::table('radius_hubs')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('nas_servers')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('nas')->where('tenant_id', $tenantId)->delete();

                // Subscriptions, audits, users
                \Illuminate\Support\Facades\DB::table('tenant_subscriptions')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('audit_logs')->where('tenant_id', $tenantId)->delete();
                \Illuminate\Support\Facades\DB::table('users')->where('tenant_id', $tenantId)->delete();

                // Delete tenant record
                $tenant->forceDelete();
            } finally {
                if ($driver === 'mysql') {
                    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                } elseif ($driver === 'sqlite') {
                    \Illuminate\Support\Facades\DB::statement('PRAGMA foreign_keys = ON;');
                }
            }
        });
    }
}
