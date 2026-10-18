<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NasServer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'nas_servers';

    protected $fillable = [
        'tenant_id',
        'name',
        'shortname',
        'host',
        'status',
        'connection_type',
        'api_username',
        'api_password',
        'api_port',
    ];
}
