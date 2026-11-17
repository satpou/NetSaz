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

        // Get tenant_id from subdomain session, active_tenant_id session, or user's own tenant_id
        $tenantId = $request->session()->get('tenant_id')
            ?? $request->session()->get('active_tenant_id')
            ?? $user->tenant_id;

        if (!$tenantId) {
            abort(403, 'Tidak ada tenant yang aktif.');
        }

        // Platform super admin (no tenant_id) can manage all tenants
        if ($user->hasRole('super_admin') && is_null($user->tenant_id)) {
            $tenant = Tenant::find($tenantId);
            $request->session()->put('tenant_id', $tenantId);
            $request->session()->put('active_tenant_id', $tenantId);
            if ($tenant) {
                URL::defaults(['tenant_slug' => $tenant->slug]);
            }
            return $next($request);
        }

        // Ensure tenant-scoped user (including ISP Owner with role=super_admin) belongs to this tenant
        if ((int) $user->tenant_id !== (int) $tenantId) {
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

        // Store tenant_id in both session keys for consistency across controllers and services
        $request->session()->put('tenant_id', $tenantId);
        $request->session()->put('active_tenant_id', $tenantId);

        // Set default route parameter so route() calls work without passing tenant_slug
        URL::defaults(['tenant_slug' => $tenant->slug]);

        return $next($request);
    }
}