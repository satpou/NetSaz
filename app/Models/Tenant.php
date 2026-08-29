<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    private static array $sensitiveSettings = [
        'midtrans_server_key',
        'midtrans_client_key',
        'xendit_api_key',
        'xendit_webhook_token',
    ];

    protected $fillable = [
        'name',
        'slug',
        'whatsapp_number',
        'email',
        'status',
        'settings',
        'logo_path',
        'brand_color',
        'brand_updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'brand_updated_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isAccessible(): bool
    {
        return $this->isActive() || $this->isOnTrial();
    }

    public function getSecretSetting(string $key): ?string
    {
        $value = $this->settings[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        if (str_starts_with((string) $value, 'enc:')) {
            return Crypt::decryptString(substr($value, 4));
        }

        return $value;
    }

    public function setSecretSetting(string $key, ?string $value): void
    {
        $settings = $this->settings ?? [];
        if ($value !== null && $value !== '') {
            $settings[$key] = 'enc:'.Crypt::encryptString($value);
        } else {
            unset($settings[$key]);
        }
        $this->settings = $settings;
    }

    public function getDecryptedSettings(): array
    {
        $settings = $this->settings ?? [];
        $decrypted = [];
        foreach ($settings as $key => $value) {
            if (in_array($key, self::$sensitiveSettings, true) && is_string($value) && str_starts_with($value, 'enc:')) {
                $decrypted[$key] = Crypt::decryptString(substr($value, 4));
            } else {
                $decrypted[$key] = $value;
            }
        }

        return $decrypted;
    }

}
