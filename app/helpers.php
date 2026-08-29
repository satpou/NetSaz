<?php

use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

if (!function_exists('tenantId')) {
    function tenantId(): ?int
    {
        if (session()->has('tenant_id')) {
            return session('tenant_id');
        }

        return Auth::user()?->tenant_id;
    }
}
