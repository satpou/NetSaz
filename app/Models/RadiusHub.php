<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadiusHub extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'radius_hubs';

    protected $fillable = [
        'tenant_id',
        'db_name',
        'server_host',
        'radius_port',
        'radius_secret',
        'status',
        'provisioned_at',
    ];

    protected $casts = [
        'provisioned_at' => 'datetime',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(RadiusHubMember::class);
    }
}
