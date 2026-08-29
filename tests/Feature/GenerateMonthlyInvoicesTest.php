<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateMonthlyInvoicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_prorated_invoice_for_new_customer(): void
    {
        config()->set('services.tax.ppn_rate', 0.11);

        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = Package::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 300000,
            'is_taxable' => true,
        ]);

        $billingDate = now()->startOfMonth();
        $joinDay = 10;

        Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
            'billing_cycle_day' => $billingDate->day,
            'join_date' => $billingDate->copy()->setDay($joinDay),
        ]);

        $this->artisan('invoices:generate-monthly', [
            '--tenant' => $tenant->id,
            '--date' => $billingDate->toDateString(),
        ])->assertSuccessful();

        $invoice = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->first();

        $this->assertNotNull($invoice);

        $daysInMonth = $billingDate->daysInMonth;
        $proratedDays = $daysInMonth - $joinDay + 1;
        $expectedAmount = round(300000 / $daysInMonth * $proratedDays, 2);
        $expectedTax = round($expectedAmount * 0.11, 2);

        $this->assertEqualsWithDelta($expectedAmount, (float) $invoice->amount, 0.01);
        $this->assertEqualsWithDelta($expectedTax, (float) $invoice->tax, 0.01);
        $this->assertEqualsWithDelta($expectedAmount + $expectedTax, (float) $invoice->total_amount, 0.01);

        $item = InvoiceItem::withoutGlobalScopes()->where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($item);
        $this->assertStringContainsString('Prorata', $item->description);
        $this->assertEquals($proratedDays, $item->days);
    }

    public function test_generates_full_invoice_for_existing_customer(): void
    {
        config()->set('services.tax.ppn_rate', 0.11);

        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = Package::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 200000,
            'is_taxable' => true,
        ]);

        $billingDate = now()->startOfMonth();

        Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
            'billing_cycle_day' => $billingDate->day,
            'join_date' => $billingDate->copy()->subMonths(3)->startOfMonth(),
        ]);

        $this->artisan('invoices:generate-monthly', [
            '--tenant' => $tenant->id,
            '--date' => $billingDate->toDateString(),
        ])->assertSuccessful();

        $invoice = Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->first();

        $this->assertNotNull($invoice);
        $this->assertEquals(200000, (float) $invoice->amount);
        $this->assertEquals(22000, (float) $invoice->tax);
        $this->assertEquals(222000, (float) $invoice->total_amount);

        $item = InvoiceItem::withoutGlobalScopes()->where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($item);
        $this->assertStringNotContainsString('Prorata', $item->description);
        $this->assertEquals($billingDate->daysInMonth, $item->days);
    }
}
