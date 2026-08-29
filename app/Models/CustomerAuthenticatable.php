<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CustomerAuthenticatable extends Model implements AuthenticatableContract
{
    use Authenticatable, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'address',
        'ktp_id',
        'ktp_image',
        'package_id',
        'status',
        'join_date',
        'billing_cycle_day',
        'portal_pin',
        'portal_login_token',
        'portal_login_token_expires_at',
        'portal_last_login_at',
    ];

    protected $casts = [
        'join_date' => 'date',
        'portal_login_token_expires_at' => 'datetime',
        'portal_last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'portal_pin',
        'portal_login_token',
    ];

    public $timestamps = true;

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'model');
    }

    public function otps(): HasMany
    {
        return $this->hasMany(CustomerOtp::class);
    }

    public function getPortalIdentifierAttribute(): string
    {
        return $this->phone ?? $this->email;
    }
}
