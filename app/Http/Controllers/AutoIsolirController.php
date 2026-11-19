<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class AutoIsolirController extends Controller
{
    public function index(Request $request)
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $settings = $tenant->settings ?? [];

        // Isolated customers
        $isolatedCustomers = Customer::where('tenant_id', $tid)
            ->where('status', 'isolated')
            ->with(['package', 'area'])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%")
                        ->orWhere('pppoe_username', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15);

        // Overdue count eligible for isolir
        $graceDays = $settings['grace_period_days'] ?? 3;
        $overdueInvoices = Invoice::where('tenant_id', $tid)
            ->where('status', 'unpaid')
            ->where('due_date', '<', now()->subDays($graceDays))
            ->with('customer')
            ->count();

        return view('billing.auto-isolir', compact('tenant', 'settings', 'isolatedCustomers', 'overdueInvoices'));
    }

    public function updateSettings(Request $request)
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);

        $validated = $request->validate([
            'auto_isolate_enabled' => 'nullable|boolean',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'isolir_profile_name' => 'required|string|max:100',
            'isolir_address_list' => 'nullable|string|max:100',
            'send_wa_notice' => 'nullable|boolean',
        ]);

        $settings = $tenant->settings ?? [];
        $settings['auto_isolate_enabled'] = $request->boolean('auto_isolate_enabled');
        $settings['grace_period_days'] = $validated['grace_period_days'];
        $settings['isolir_profile_name'] = $validated['isolir_profile_name'];
        $settings['isolir_address_list'] = $validated['isolir_address_list'] ?? 'ISOLIR_LIST';
        $settings['isolir_send_wa_notice'] = $request->boolean('send_wa_notice');

        $tenant->settings = $settings;
        $tenant->save();

        return back()->with('success', 'Pengaturan sistem Auto-Isolir berhasil disimpan.');
    }

    public function toggle(Customer $customer, MikrotikService $mikrotikService)
    {
        abort_unless($customer->tenant_id === tenantId(), 403);

        if ($customer->status === 'isolated') {
            $customer->status = 'active';
            $customer->save();

            // Enable on MikroTik if configured
            try {
                if ($customer->router) {
                    $mikrotikService->enableSecret($customer->router, $customer->pppoe_username);
                }
            } catch (\Throwable $e) {
                // Non-fatal if router offline
            }

            return back()->with('success', "Isolir pelanggan '{$customer->name}' berhasil dibuka (Status: Aktif).");
        } else {
            $customer->status = 'isolated';
            $customer->save();

            // Disable / profile switch on MikroTik if configured
            try {
                if ($customer->router) {
                    $mikrotikService->disableSecret($customer->router, $customer->pppoe_username);
                }
            } catch (\Throwable $e) {
                // Non-fatal if router offline
            }

            return back()->with('warning', "Pelanggan '{$customer->name}' berhasil di-isolir.");
        }
    }
}
