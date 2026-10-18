<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MikrotikAccount extends Model
{
    protected $table = 'mikrotik_accounts';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'nas_id',
        'pppoe_secret',
        'pppoe_password',
        'pppoe_service',
        'status',
        'provisioned_at',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function nasServer(): BelongsTo
    {
        return $this->belongsTo(NasServer::class, 'nas_id');
    }
}
