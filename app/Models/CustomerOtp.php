<?php
namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOtp extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'otp_code',
        'purpose',
        'expires_at',
        'used_at',
    ];

    protected $hidden = ['otp_code'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    // tenant() inherited from BelongsToTenant trait

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isValid(): bool
    {
        return !$this->used_at && $this->expires_at && $this->expires_at->isFuture();
    }

    public function markAsUsed(): bool
    {
        return $this->update(['used_at' => now()]);
    }
}
