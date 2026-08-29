<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly'
        . ' {--tenant= : Tenant ID spesifik}'
        . ' {--date= : Tanggal billing (Y-m-d)}'
        . ' {--dry-run : Jalankan tanpa menyimpan}';
    protected $description = 'Generate invoice bulanan untuk customer aktif';

    public function handle(BillingService $billingService): int
    {
        $billingDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::now();

        $dryRun = $this->option('dry-run');

        $this->info("Generating invoices for {$billingDate->format('Y-m-d')}" . ($dryRun ? ' (DRY RUN)' : ''));

        $results = $billingService->generateAllPendingInvoices($billingDate, $dryRun);

        $this->newLine();
        $this->info("Selesai.");
        $this->info("  Created: {$results['created']}");
        $this->info("  Skipped: {$results['skipped']}");
        $this->info("  Errors:  {$results['errors']}");

        if ($dryRun && ! empty($results['invoices'])) {
            $this->newLine();
            $rows = [];
            foreach ($results['invoices'] as $inv) {
                $rows[] = [
                    'Rp' . number_format($inv['amount'], 0, ',', '.'),
                    'Rp' . number_format($inv['tax'], 0, ',', '.'),
                    'Rp' . number_format($inv['total_amount'], 0, ',', '.'),
                ];
            }
            $this->table(['Amount', 'Tax', 'Total'], $rows);
        }

        return self::SUCCESS;
    }
}
