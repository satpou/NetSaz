<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'customer_id',
        'payment_number',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'gateway_provider',
        'gateway_transaction_id',
        'gateway_reference',
        'qr_string',
        'status',
        'paid_at',
        'proof_of_payment',
        'verified_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_number)) {
                $prefix = $payment->status === 'pending' ? 'MAN' : 'PAY';
                $tenantPart = $payment->tenant_id ?? tenantId() ?? '0';
                $payment->payment_number = "{$prefix}/" . date('YmdHis') . "/{$tenantPart}/" . random_int(1000, 9999);
            }
        });
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
