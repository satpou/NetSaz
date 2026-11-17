<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function isPlatformSuperAdmin($user): bool
    {
        return $user->isTenantSuperAdmin() && is_null($user->tenant_id);
    }

    public function registerTenant(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:100|regex:/^[a-z0-9-]+$/',
            'pic_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'email' => 'required|email|max:255|unique:users,email|unique:tenants,email',
            'password' => 'required|string|min:8|confirmed',
            'customers' => 'nullable|string|max:50',
            'mitra' => 'nullable|string|max:50',
            'package' => 'nullable|string|max:50',
        ]);

        $baseSlug = Str::slug($validated['slug'] ?? $validated['company_name']);
        if ($baseSlug === '') {
            $baseSlug = 'isp-' . Str::lower(Str::random(6));
        }

        $slug = $baseSlug;
        $counter = 1;
        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        [$tenant, $user] = DB::transaction(function () use ($validated, $slug) {
            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'email' => $validated['email'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? null,
                'status' => 'active',
                'settings' => array_filter([
                    'pic_name' => $validated['pic_name'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'estimated_customers' => $validated['customers'] ?? null,
                    'estimated_mitra' => $validated['mitra'] ?? null,
                    'selected_package' => $validated['package'] ?? 'launch',
                ], fn ($v) => $v !== null && $v !== ''),
            ]);

            $user = User::create([
                'name' => $validated['pic_name'] ?? $validated['company_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'tenant_id' => $tenant->id,
                'role' => 'super_admin',
                'is_active' => true,
            ]);

            $planKey = ($validated['package'] ?? 'launch') === 'professional'
                ? 'pro'
                : ($validated['package'] ?? 'launch');

            $plan = SubscriptionPlan::where('code', $planKey)->first()
                ?? SubscriptionPlan::orderBy('sort_order')->first();

            if ($plan) {
                TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'subscription_plan_id' => $plan->id,
                    'status' => 'trial',
                    'started_at' => now(),
                    'current_period_start' => now(),
                    'current_period_end' => now()->addDays(14),
                ]);
            }

            return [$tenant, $user];
        });

        Auth::login($user);
        $request->session()->regenerate();
        session(['tenant_id' => $tenant->id, 'active_tenant_id' => $tenant->id]);

        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portSuffix = in_array($port, [80, 443, null], true) ? '' : ":{$port}";
        $tenantDomain = config('app.tenant_domain');

        return redirect("{$scheme}://{$tenant->slug}.{$tenantDomain}{$portSuffix}/dashboard")
            ->with('success', 'Akun ISP Anda berhasil dibuat!');
    }

    public function showLogin(Request $request)
    {
        $host = $request->getHost();
        $mainDomain = config('app.main_domain');
        $tenantDomain = config('app.tenant_domain');
        $isMainDomain = ($host === $mainDomain || !str_contains($host, '.'));

        if (Auth::check()) {
            $user = Auth::user();

            if ($this->isPlatformSuperAdmin($user)) {
                if ($isMainDomain) {
                    return redirect()->intended('/dashboard');
                }
            } elseif (
                !$isMainDomain
                && $user->tenant
                && str_ends_with($host, '.' . $tenantDomain)
                && $user->tenant->slug === Str::before($host, '.' . $tenantDomain)
            ) {
                return redirect()->intended('/dashboard');
            }
        }

        return view('auth.login', compact('isMainDomain'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $host = $request->getHost();
        $mainDomain = config('app.main_domain');
        $isMainDomain = ($host === $mainDomain || !str_contains($host, '.'));

        if ($isMainDomain) {
            $user = User::where('email', $request->email)->first();

            if ($user && $user->tenant) {
                $scheme = $request->getScheme();
                $port = $request->getPort();
                $portSuffix = in_array($port, [80, 443]) ? '' : ":{$port}";
                $tenantDomain = config('app.tenant_domain');

                return redirect("{$scheme}://{$user->tenant->slug}.{$tenantDomain}{$portSuffix}/login?email=" . urlencode($request->email));
            }

            if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                $request->session()->regenerate();
                $user = Auth::user();

                if (!$user->is_active) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Akun Anda tidak aktif.'])->onlyInput('email');
                }

                return redirect()->intended('/dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda tidak aktif.'])->onlyInput('email');
            }

            $tenantId = session('tenant_id');

            if (!$this->isPlatformSuperAdmin($user) && $user->tenant_id !== $tenantId) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda tidak terdaftar di ISP ini.'
                ])->onlyInput('email');
            }

            return redirect()->intended('/dashboard')
                ->with('success', 'Selamat datang kembali!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $mainDomain = config('app.main_domain');
        $scheme = $request->getScheme();
        $port = $request->getPort();
        $portSuffix = in_array($port, [80, 443]) ? '' : ":{$port}";

        return redirect("{$scheme}://{$mainDomain}{$portSuffix}");
    }
}
