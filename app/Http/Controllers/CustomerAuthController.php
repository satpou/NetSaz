<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::guard('customer')->check()) {
            if ($request->has('package_id')) {
                session(['subscribe_package_id' => $request->package_id]);
            }

            return redirect()->route('customer.portal.profile');
        }

        if ($request->has('package_id')) {
            session(['subscribe_package_id' => $request->package_id]);
        }

        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone_or_email' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        $tenantId = session('tenant_id');
        $tenant = $tenantId ? Tenant::find($tenantId) : null;

        if (! $tenant) {
            return back()->withErrors(['tenant' => 'ISP tidak ditemukan.'])->onlyInput('phone_or_email');
        }

        $customer = Customer::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where(function ($q) use ($validated) {
                $q->where('phone', $validated['phone_or_email'])
                    ->orWhere('email', $validated['phone_or_email']);
            })
            ->first();

        if (! $customer) {
            return back()->withErrors(['phone_or_email' => 'Nomor/email tidak terdaftar.'])->onlyInput('phone_or_email');
        }

        if (! $customer->verifyPortalPin($validated['pin'])) {
            return back()->withErrors(['pin' => 'PIN salah.'])->onlyInput('phone_or_email');
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $customer->update(['portal_last_login_at' => now()]);

        return redirect()->route('customer.portal.profile');
    }

    public function magicLogin(Request $request, string $token)
    {
        $tenantId = session('tenant_id');

        $customer = Customer::withoutGlobalScopes()
            ->where('portal_login_token', hash('sha256', $token))
            ->where('portal_login_token_expires_at', '>', now())
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->first();

        if (! $customer) {
            return redirect()->route('customer.auth.login')
                ->withErrors(['pin' => 'Link login tidak valid atau sudah kedaluwarsa.']);
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();
        $customer->forceFill([
            'portal_login_token' => null,
            'portal_login_token_expires_at' => null,
            'portal_last_login_at' => now(),
        ])->save();

        return redirect()->route('customer.portal.profile');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.auth.login');
    }
}
