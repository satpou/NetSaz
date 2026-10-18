<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouterAgentCommand extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'router_agent_id',
        'command_type',
        'payload',
        'status',
        'acknowledged_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'acknowledged_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(RouterAgent::class, 'router_agent_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
