<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiusHubMember extends Model
{
    use HasFactory;

    protected $table = 'radius_hub_members';

    protected $fillable = [
        'radius_hub_id',
        'nas_server_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function hub(): BelongsTo
    {
        return $this->belongsTo(RadiusHub::class, 'radius_hub_id');
    }

    public function nasServer(): BelongsTo
    {
        return $this->belongsTo(NasServer::class, 'nas_server_id');
    }
}
