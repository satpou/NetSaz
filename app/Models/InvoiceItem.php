<?php
namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'invoice_id',
        'description',
        'days',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'days' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public $timestamps = true;

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
