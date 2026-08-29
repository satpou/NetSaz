<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private function isPlatformSuperAdmin($user): bool
    {
        return $user->isTenantSuperAdmin() && is_null($user->tenant_id);
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
