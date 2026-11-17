<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::withCount(['tenantSubscriptions' => function ($q) {
            $q->whereIn('status', ['active', 'trial']);
        }])->orderBy('sort_order')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $availableFeatures = $this->getAvailableFeatures();
        return view('admin.plans.create', compact('availableFeatures'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|alpha_dash|unique:subscription_plans,code',
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'max_active_customers' => 'required|integer|min:0',
            'max_routers' => 'required|integer|min:0',
            'max_staff' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['features'] = $request->input('features', []);

        SubscriptionPlan::create($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Paket langganan '{$validated['name']}' berhasil dibuat.");
    }

    public function edit(SubscriptionPlan $plan)
    {
        $availableFeatures = $this->getAvailableFeatures();
        return view('admin.plans.edit', compact('plan', 'availableFeatures'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('subscription_plans', 'code')->ignore($plan->id)],
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly',
            'max_active_customers' => 'required|integer|min:0',
            'max_routers' => 'required|integer|min:0',
            'max_staff' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['features'] = $request->input('features', []);

        $plan->update($validated);

        return redirect()->route('admin.plans.index')
            ->with('success', "Paket langganan '{$plan->name}' berhasil diperbarui.");
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $count = $plan->tenantSubscriptions()->whereIn('status', ['active', 'trial'])->count();
        if ($count > 0) {
            return back()->with('error', "Paket tidak dapat dihapus karena masih digunakan oleh {$count} tenant aktif.");
        }

        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', "Paket '{$plan->name}' berhasil dihapus.");
    }

    private function getAvailableFeatures(): array
    {
        return [
            'whatsapp_broadcast' => 'WhatsApp Gateway & Broadcast Tagihan',
            'export_data' => 'Export Data (Excel, PDF, CSV)',
            'advanced_reporting' => 'Laporan Analitik Keuangan & Grafis',
            'radius_hub' => 'Radius Hub & Autentikasi Terpusat',
            'olt_management' => 'Manajemen OLT & Fiber Optik (EPON/GPON)',
            'genieacs_integration' => 'Integrasi GenieACS (TR-069 CPE)',
            'api_access' => 'Akses API Billing & Router Agent',
            'secure_tunnel' => 'Tunnel Aman Jaringan Remote',
            'custom_domain' => 'Dukungan Custom Domain ISP',
            'white_label' => 'White Label (Logo & Brand Sendiri)',
        ];
    }
}
