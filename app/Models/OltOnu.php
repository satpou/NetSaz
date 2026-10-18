<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OltOnu extends Model
{
    use BelongsToTenant;

    protected $table = 'olt_onus';

    protected $fillable = [
        'olt_id',
        'tenant_id',
        'name',
        'serial',
        'pon_id',
        'onu_id',
        'vlan',
        'status',
        'provisioned_at',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
    ];

    public function olt(): BelongsTo
    {
        return $this->belongsTo(OltDevice::class, 'olt_id');
    }
}
