<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'customer_id',
        'package_id',
        'period_start',
        'period_end',
        'amount',
        'discount',
        'tax',
        'total_amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public $timestamps = true;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function auditLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AuditLog::class, 'model');
    }

    public function getRemainingAmountAttribute(): float
    {
        $paid = $this->payments()->where('status', 'success')->sum('amount');
        return max(0, $this->total_amount - $paid);
    }

    public function markAsOverdue(): bool
    {
        return $this->update(['status' => 'overdue']);
    }

    public function markAsPaid(): bool
    {
        return $this->update(['status' => 'paid']);
    }

    public function recalculateStatus(): void
    {
        $totalPaid = $this->payments()->where('status', 'success')->sum('amount');
        
        if ($totalPaid >= $this->total_amount) {
            $this->status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date && Carbon::parse($this->due_date)->isPast()) {
            $this->status = 'overdue';
        } else {
            $this->status = 'unpaid';
        }
        
        $this->save();
    }
}