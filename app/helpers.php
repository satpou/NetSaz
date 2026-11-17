<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

if (!function_exists('tenantId')) {
    function tenantId(): ?int
    {
        if (session()->has('active_tenant_id')) {
            return session('active_tenant_id');
        }

        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        return Auth::user()?->tenant_id;
    }
}
