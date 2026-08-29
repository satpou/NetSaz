<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Carbon\Carbon;

class BillingService
{
    public function generateInvoice(Customer $customer, ?Carbon $billingDate = null, bool $dryRun = false): Invoice|array|null
    {
        $billingDate = $billingDate ?? Carbon::now();
        $package = $customer->package;

        if (! $package) {
            return null;
        }

        $periodStart = $billingDate->copy()->startOfMonth();
        $periodEnd = $billingDate->copy()->endOfMonth();

        $existingInvoice = Invoice::withoutGlobalScope(TenantScope::class)
            ->where('customer_id', $customer->id)
            ->whereDate('period_start', $periodStart->format('Y-m-d'))
            ->whereDate('period_end', $periodEnd->format('Y-m-d'))
            ->first();

        if ($existingInvoice) {
            return null;
        }

        $prorata = $this->calculateProrata($customer, $billingDate);
        $ppnRate = (float) config('services.tax.ppn_rate', 0.11);
        $tax = $package->is_taxable ? round($prorata['amount'] * $ppnRate, 2) : 0;
        $totalAmount = $prorata['amount'] + $tax;

        if ($dryRun) {
            return [
                'amount' => $prorata['amount'],
                'tax' => $tax,
                'total_amount' => $totalAmount,
                'description' => $prorata['description'],
                'days' => $prorata['days'],
                'unit_price' => $prorata['unit_price'],
            ];
        }

        $invoice = Invoice::withoutGlobalScope(TenantScope::class)->create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'invoice_number' => InvoiceNumberGenerator::generate($customer->tenant_id),
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'amount' => $prorata['amount'],
            'discount' => 0,
            'tax' => $tax,
            'total_amount' => $totalAmount,
            'due_date' => $billingDate->copy()->addDays(5)->format('Y-m-d'),
            'status' => 'unpaid',
        ]);

        InvoiceItem::withoutGlobalScope(TenantScope::class)->create([
            'invoice_id' => $invoice->id,
            'tenant_id' => $customer->tenant_id,
            'description' => $prorata['description'],
            'days' => $prorata['days'],
            'unit_price' => $prorata['unit_price'],
            'subtotal' => $prorata['amount'],
        ]);

        return $invoice;
    }

    protected function calculateProrata(Customer $customer, Carbon $billingDate): array
    {
        $package = $customer->package;
        $daysInMonth = $billingDate->daysInMonth;
        $dailyRate = $package->price / $daysInMonth;

        $joinDate = Carbon::parse($customer->join_date);

        if ($joinDate->isSameMonth($billingDate) && $joinDate->year == $billingDate->year) {
            $days = $daysInMonth - $joinDate->day + 1;
            $description = "Paket {$package->name} (Prorata {$days} hari)";
        } else {
            $days = $daysInMonth;
            $description = "Paket {$package->name}";
        }

        $amount = round($dailyRate * $days, 2);

        return [
            'description' => $description,
            'days' => $days,
            'unit_price' => round($dailyRate, 2),
            'amount' => $amount,
        ];
    }

    public function generateAllPendingInvoices(?Carbon $billingDate = null, bool $dryRun = false): array
    {
        $billingDate = $billingDate ?? Carbon::now();
        $tenants = Tenant::where('status', 'active')->get();
        $results = ['created' => 0, 'skipped' => 0, 'errors' => 0, 'invoices' => []];

        foreach ($tenants as $tenant) {
            $customers = Customer::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->get();

            foreach ($customers as $customer) {
                $billingDay = $customer->billing_cycle_day ?? 1;
                if ($billingDate->day !== $billingDay) {
                    continue;
                }

                try {
                    $invoice = $this->generateInvoice($customer, $billingDate, $dryRun);
                    if ($invoice) {
                        $results['created']++;
                        $results['invoices'][] = $invoice;
                    } else {
                        $results['skipped']++;
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                }
            }
        }

        return $results;
    }

    public function markOverdueInvoices(): void
    {
        Invoice::where('status', 'unpaid')
            ->where('due_date', '<', Carbon::now())
            ->update(['status' => 'overdue']);

        Invoice::where('status', 'partial')
            ->where('due_date', '<', Carbon::now())
            ->update(['status' => 'overdue']);
    }

    public function getUnpaidInvoicesForCustomer(Customer $customer): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->get();
    }

    public function getTotalUnpaidForCustomer(Customer $customer): float
    {
        return (float) Invoice::where('customer_id', $customer->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->sum('total_amount');
    }

    public function isFullyPaid(Customer $customer): bool
    {
        return $this->getTotalUnpaidForCustomer($customer) == 0;
    }
}
