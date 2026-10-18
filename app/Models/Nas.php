<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nas extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'nas';

    protected $fillable = [
        'tenant_id',
        'name',
        'shortname',
        'host',
        'status',
        'connection_type',
    ];
}
