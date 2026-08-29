<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceNumberGenerator
{
    public static function generate(?int $tenantId = null): string
    {
        if (!$tenantId) {
            $tenantId = session('active_tenant_id') ?? auth()->user()?->tenant_id;
        }

        if (!$tenantId) {
            throw new \Exception('Tenant ID tidak ditemukan untuk generate invoice number.');
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            throw new \Exception('Tenant tidak ditemukan.');
        }

        $tenantCode = strtoupper(substr($tenant->slug, 0, 3));
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        
        return DB::transaction(function () use ($tenantId, $tenantCode, $year, $month) {
            $prefix = "INV/{$tenantCode}/{$year}/{$month}/";
            
            $query = Invoice::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('invoice_number', 'like', "{$prefix}%")
                ->orderByDesc('invoice_number');

            $lastInvoice = $query->first();

            if ($lastInvoice) {
                $lastSequence = (int) substr($lastInvoice->invoice_number, -4);
                $newSequence = $lastSequence + 1;
            } else {
                $newSequence = 1;
            }

            $sequence = str_pad($newSequence, 4, '0', STR_PAD_LEFT);

            return "{$prefix}{$sequence}";
        });
    }
}
