<?php

namespace App\Http\Middleware;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Customer portal users bypass tenant identification
        if (Auth::guard('customer')->check()) {
            return $next($request);
        }

        // Get tenant_id from user's own tenant_id
        $tenantId = $user->tenant_id ?? $request->session()->get('tenant_id');

        if (!$tenantId) {
            abort(403, 'Tidak ada tenant yang aktif.');
        }

        // Super admin can manage all tenants
        if ($user->hasRole('super_admin')) {
            $request->session()->put('tenant_id', $tenantId);
            return $next($request);
        }

        // Ensure user belongs to this tenant
        if ($user->tenant_id !== $tenantId) {
            abort(403, 'Akun Anda tidak terdaftar di ISP ini.');
        }

        // Check tenant status
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            abort(403, 'Tenant tidak ditemukan.');
        }

        if ($tenant->isSuspended()) {
            abort(403, 'Akun tenant Anda sedang ditangguhkan. Hubungi support.');
        }

        // Store tenant_id in session for later use
        $request->session()->put('active_tenant_id', $tenantId);

        // Set default route parameter so route() calls work without passing tenant_slug
        URL::defaults(['tenant_slug' => $user->tenant?->slug]);

        return $next($request);
    }
}