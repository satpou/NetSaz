<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Package extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'price',
        'speed',
        'is_taxable',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public $timestamps = true;

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasManyThrough(Invoice::class, Customer::class);
    }

    public function parseSpeed(): ?array
    {
        if (!$this->speed) {
            return null;
        }

        $speed = strtolower($this->speed);
        preg_match_all('/(\d+(?:\.\d+)?)\s*(mbps|gbps|kbps)?/i', $speed, $matches);

        if (count($matches[0]) === 0) {
            return null;
        }

        $values = [];
        foreach ($matches[0] as $i => $raw) {
            $num = (float) $matches[1][$i];
            $unit = $matches[2][$i] ?? 'mbps';
            $mbs = match ($unit) {
                'gbps', 'g' => $num * 1000,
                'kbps' => $num / 1000,
                default => $num,
            };
            $values[] = $mbs;
        }

        if (str_contains($speed, '/')) {
            $down = $values[0] ?? null;
            $up = $values[1] ?? $down;
        } else {
            $down = $values[0] ?? null;
            $up = count($values) > 1 ? $values[1] : $down;
        }

        if ($down === null) {
            return null;
        }

        return ['down' => $down, 'up' => $up ?: $down];
    }
}