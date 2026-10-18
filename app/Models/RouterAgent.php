<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RouterAgent extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'nas_id',
        'agent_token',
        'status',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function nas(): BelongsTo
    {
        return $this->belongsTo(Nas::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(RouterAgentCommand::class);
    }

    /**
     * Generate a random raw token.
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Hash a raw token for storage.
     */
    public static function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    /**
     * Find an agent by raw token.
     */
    public static function findByRawToken(string $rawToken): ?self
    {
        return static::where('agent_token', static::hashToken($rawToken))->first();
    }
}
