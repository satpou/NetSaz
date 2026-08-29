<?php

namespace App\Models\Traits;

use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Apply TenantScope to all queries
        static::addGlobalScope(new TenantScope());

        // Auto-fill tenant_id on creating
        static::creating(function (Model $model) {
            if (!$model->tenant_id) {
                $tenantId = TenantScope::getCurrentTenantId();
                if ($tenantId) {
                    $model->tenant_id = $tenantId;
                }
            }
        });
    }

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}