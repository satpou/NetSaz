<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Customer extends CustomerAuthenticatable
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'address',
        'area',
        'latitude',
        'longitude',
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
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    protected $hidden = [
        'portal_pin',
        'portal_login_token',
    ];

    protected string $guard_name = 'customer';

    public $timestamps = true;

    public function otps(): HasMany
    {
        return $this->hasMany(CustomerOtp::class);
    }

    public function getPortalIdentifierAttribute(): string
    {
        return $this->phone ?? $this->email;
    }

    public function portalUrl(): string
    {
        $base = $this->portalBaseUrl();

        return $base ? $base.'/portal/login' : '';
    }

    public function magicLoginUrl(): string
    {
        $base = $this->portalBaseUrl();
        if (! $base) {
            return '';
        }

        $token = $this->generateMagicLoginToken();

        return $base.'/portal/auth/'.$token;
    }

    public function generateMagicLoginToken(int $days = 7): string
    {
        $token = Str::random(64);
        $this->forceFill([
            'portal_login_token' => hash('sha256', $token),
            'portal_login_token_expires_at' => now()->addDays($days),
        ])->save();

        return $token;
    }

    public function verifyPortalPin(string $pin): bool
    {
        if (! $this->portal_pin) {
            return false;
        }

        // Legacy plaintext 6-digit PIN (pre-hash migration)
        if (strlen($this->portal_pin) === 6 && ctype_digit($this->portal_pin)) {
            if (! hash_equals($this->portal_pin, $pin)) {
                return false;
            }
            $this->forceFill(['portal_pin' => Hash::make($pin)])->save();

            return true;
        }

        return Hash::check($pin, $this->portal_pin);
    }

    /**
     * Issue a new portal PIN. Returns plaintext once; stores only the hash.
     */
    public function issuePortalPin(): string
    {
        $pin = (string) random_int(100000, 999999);
        $this->forceFill(['portal_pin' => Hash::make($pin)])->save();

        return $pin;
    }

    public function ensurePortalPin(): string
    {
        return $this->issuePortalPin();
    }

    protected function portalBaseUrl(): string
    {
        $tenant = $this->tenant;
        if (! $tenant || ! $tenant->slug) {
            return '';
        }

        $appUrl = config('app.url', 'http://localhost');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'http';
        $port = parse_url($appUrl, PHP_URL_PORT);
        $portSuffix = $port && ! in_array($port, [80, 443]) ? ":{$port}" : '';

        $tenantDomain = config('app.tenant_domain', config('app.main_domain', 'netsaz.id'));

        return "{$scheme}://{$tenant->slug}.{$tenantDomain}{$portSuffix}";
    }
}
