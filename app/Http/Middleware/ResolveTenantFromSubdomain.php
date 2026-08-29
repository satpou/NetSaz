<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ResolveTenantFromSubdomain
{
    public function handle(Request $request, Closure $next)
    {
        // For local development, allow tenant override via query parameter
        if (app()->isLocal() && $request->has('tenant')) {
            $tenant = Tenant::where('slug', $request->query('tenant'))->first();
            if ($tenant) {
                session(['tenant_id' => $tenant->id]);
                URL::defaults(['tenant_slug' => $tenant->slug]);
                return $next($request);
            }
        }

        $host = $request->getHost();
        $tenantDomain = config('app.tenant_domain');

        if ($host !== $tenantDomain && Str::endsWith($host, '.'.$tenantDomain)) {
            $slug = str_replace('.'.$tenantDomain, '', $host);
            $tenant = Tenant::where('slug', $slug)->first();

            if (!$tenant) {
                abort(404, 'ISP tidak ditemukan.');
            }

            session(['tenant_id' => $tenant->id]);
            URL::defaults(['tenant_slug' => $tenant->slug]);
        }

        return $next($request);
    }
}
