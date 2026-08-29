<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Carbon\Carbon;

class MarkOverdueInvoices extends Command
{
    protected $signature = 'invoices:mark-overdue'
        . ' {--tenant= : Tenant ID spesifik}';
    protected $description = 'Tandai invoice unpaid/partial yang lewat jatuh tempo sebagai overdue';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');
        $tenants = $tenantId
            ? Tenant::where('id', $tenantId)->get()
            : Tenant::where('status', 'active')->get();

        $totalUpdated = 0;

        foreach ($tenants as $tenant) {
            $updated = Invoice::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->where('due_date', '<', Carbon::now()->format('Y-m-d'))
                ->update(['status' => 'overdue']);

            $totalUpdated += $updated;

            $this->line("{$tenant->name}: updated {$updated} invoices");
        }

        $this->newLine();
        $this->info("Total overdue invoices updated: {$totalUpdated}");

        return self::SUCCESS;
    }
}
