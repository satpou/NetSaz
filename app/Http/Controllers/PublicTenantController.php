<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PublicTenantController extends Controller
{
    public function landing(Request $request)
    {
        $tenantId = session('tenant_id');

        if (! $tenantId) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        $tenant = Tenant::findOrFail($tenantId);
        $packages = Package::where('tenant_id', $tenantId)->get();

        return view('tenant.landing', compact('tenant', 'packages'));
    }

    public function packages(Request $request)
    {
        $tenantId = session('tenant_id');

        if (! $tenantId) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        $tenant = Tenant::findOrFail($tenantId);
        $packages = Package::where('tenant_id', $tenantId)->get();

        return view('tenant.packages', compact('tenant', 'packages'));
    }

    public function register(Request $request)
    {
        $tenantId = session('tenant_id');
        if (! $tenantId) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        $tenant = Tenant::findOrFail($tenantId);
        $packages = Package::where('tenant_id', $tenantId)->orderBy('name')->get();

        $selectedPackage = null;
        if ($request->has('package_id')) {
            $selectedPackage = $packages->firstWhere('id', $request->package_id);
        }

        return view('tenant.register', compact('tenant', 'packages', 'selectedPackage'));
    }

    public function processRegistration(Request $request)
    {
        $tenantId = session('tenant_id');
        if (! $tenantId) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        $tenant = Tenant::findOrFail($tenantId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:500',
            'package_id' => 'required|integer',
        ]);

        $package = Package::where('tenant_id', $tenantId)
            ->where('id', $validated['package_id'])
            ->first();

        if (! $package) {
            return back()->withErrors([
                'package_id' => 'Paket tidak valid untuk ISP ini.',
            ])->withInput();
        }

        $existingCustomer = Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('phone', $validated['phone'])
            ->first();

        if ($existingCustomer) {
            return back()->withErrors([
                'phone' => 'Nomor HP sudah terdaftar. Silakan hubungi admin untuk akses portal.',
            ])->withInput();
        }

        $pin = (string) random_int(100000, 999999);
        $customer = Customer::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'],
            'package_id' => $package->id,
            'status' => 'isolated',
            'join_date' => now()->format('Y-m-d'),
            'billing_cycle_day' => now()->day,
            'portal_pin' => Hash::make($pin),
        ]);

        return view('tenant.register-success', compact('tenant', 'customer', 'package'));
    }
}
