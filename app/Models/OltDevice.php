<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OltDevice extends Model
{
    use BelongsToTenant;

    protected $table = 'olt_devices';

    protected $fillable = [
        'tenant_id',
        'name',
        'brand',
        'management_host',
        'username',
        'password',
        'ssh_port',
        'status',
        'onu_count',
    ];

    public function onus(): HasMany
    {
        return $this->hasMany(OltOnu::class, 'olt_id');
    }
}
