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

        $tenant = DB::transaction(function () use ($validated, $slug) {
            $planKey = ($validated['package'] ?? 'launch') === 'professional'
                ? 'pro'
                : ($validated['package'] ?? 'launch');

            $plan = SubscriptionPlan::where('code', $planKey)->first()
                ?? SubscriptionPlan::orderBy('sort_order')->first();

            $annualPrice = $plan ? $plan->price : 399000;

            $tenant = Tenant::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
                'email' => $validated['email'],
                'whatsapp_number' => $validated['whatsapp_number'] ?? null,
                'status' => 'pending_payment',
                'settings' => array_filter([
                    'pic_name' => $validated['pic_name'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'estimated_customers' => $validated['customers'] ?? null,
                    'estimated_mitra' => $validated['mitra'] ?? null,
                    'selected_package' => $planKey,
                    'selected_package_id' => $plan?->id,
                    'payment_amount' => $annualPrice,
                    'payment_status' => 'pending',
                    'billing_period' => 'yearly',
                ], fn ($v) => $v !== null && $v !== ''),
            ]);

            User::create([
                'name' => $validated['pic_name'] ?? $validated['company_name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'tenant_id' => $tenant->id,
                'role' => 'super_admin',
                'is_active' => false,
            ]);

            if ($plan) {
                TenantSubscription::create([
                    'tenant_id' => $tenant->id,
                    'subscription_plan_id' => $plan->id,
                    'status' => 'pending_payment',
                    'started_at' => null,
                    'current_period_start' => null,
                    'current_period_end' => null,
                ]);
            }

            return $tenant;
        });

        return redirect()->route('register.payment', ['slug' => $tenant->slug])
            ->with('info', 'Pendaftaran akun ISP Anda berhasil dicatat. Silakan lakukan pembayaran untuk aktivasi akun.');
    }

    public function showLogin(Request $request)
    {
        $host = $request->getHost();
        $mainDomain = config('app.main_domain');
        $tenantDomain = config('app.tenant_domain');
        $isTenantDomain = ($host !== $tenantDomain && str_ends_with($host, '.' . $tenantDomain));
        $isMainDomain = !$isTenantDomain;

        return view('auth.login', compact('isMainDomain'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $host = $request->getHost();
        $tenantDomain = config('app.tenant_domain');
        $isTenantDomain = ($host !== $tenantDomain && str_ends_with($host, '.' . $tenantDomain));
        $isMainDomain = !$isTenantDomain;

        $isSubdomainCapable = !str_contains($host, 'trycloudflare.com')
            && !str_contains($host, 'ngrok')
            && !str_contains($host, 'pinggy')
            && !str_contains($host, 'loca.lt')
            && !filter_var($host, FILTER_VALIDATE_IP)
            && ($host === $tenantDomain || str_ends_with($host, '.' . $tenantDomain) || $host === 'localhost');

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->tenant && $user->tenant->status === 'pending_payment') {
                Auth::logout();
                return redirect()->route('register.payment', ['slug' => $user->tenant->slug])
                    ->with('warning', 'Pendaftaran Anda belum selesai. Silakan lakukan pembayaran atau unggah bukti transfer.');
            }

            if ($user->tenant && $user->tenant->status === 'rejected') {
                Auth::logout();
                $reason = $user->tenant->settings['rejection_reason'] ?? 'Pendaftaran tidak disetujui';
                return back()->withErrors(['email' => "Pendaftaran ISP Anda ditolak: {$reason}. Hubungi WhatsApp Sales di 083162238930."])->onlyInput('email');
            }

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda belum aktif. Silakan tunggu verifikasi admin atau hubungi sales.'])->onlyInput('email');
            }

            // Set tenant session context if user belongs to a tenant
            if ($user->tenant_id) {
                session([
                    'tenant_id' => $user->tenant_id,
                    'active_tenant_id' => $user->tenant_id,
                    'tenant_name' => $user->tenant?->name,
                    'tenant_slug' => $user->tenant?->slug,
                ]);
                if ($user->tenant) {
                    \Illuminate\Support\Facades\URL::defaults(['tenant_slug' => $user->tenant->slug]);
                }
            }

            // Subdomain check: if accessed from a tenant subdomain, verify user belongs to this tenant
            if ($isTenantDomain) {
                $subdomainSlug = Str::before($host, '.' . $tenantDomain);
                if (!$this->isPlatformSuperAdmin($user) && (!$user->tenant || $user->tenant->slug !== $subdomainSlug)) {
                    Auth::logout();
                    return back()->withErrors([
                        'email' => 'Akun Anda tidak terdaftar di ISP ini.'
                    ])->onlyInput('email');
                }

                return redirect()->intended('/dashboard')
                    ->with('success', 'Selamat datang kembali!');
            }

            // If accessed from main domain, and subdomains are supported, and in production mode
            if ($user->tenant && $isSubdomainCapable && $host === config('app.main_domain') && !app()->isLocal()) {
                $scheme = $request->getScheme();
                $port = $request->getPort();
                $portSuffix = in_array($port, [80, 443]) ? '' : ":{$port}";

                return redirect("{$scheme}://{$user->tenant->slug}.{$tenantDomain}{$portSuffix}/dashboard")
                    ->with('success', 'Selamat datang kembali!');
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

        if ($mainDomain && !app()->isLocal() && !filter_var($request->getHost(), FILTER_VALIDATE_IP) && !str_contains($request->getHost(), 'trycloudflare.com')) {
            return redirect("{$scheme}://{$mainDomain}{$portSuffix}/");
        }

        return redirect('/');
    }
}
