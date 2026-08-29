<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Tenant;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_invoice_populates_full_fields(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = Package::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 150000,
            'is_taxable' => false,
        ]);
        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => now()->subMonths(2)->startOfMonth(),
        ]);

        config()->set('services.tax.ppn_rate', 0.11);

        $billingDate = now()->startOfMonth();
        $invoice = (new BillingService())->generateInvoice($customer, $billingDate);

        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'amount' => 150000.00,
            'discount' => 0,
            'tax' => 0,
            'total_amount' => 150000.00,
            'status' => 'unpaid',
        ]);
    }

    public function test_generate_invoice_calculates_tax_for_taxable_package(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = Package::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 100000,
            'is_taxable' => true,
        ]);
        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => now()->subMonths(2)->startOfMonth(),
        ]);

        config()->set('services.tax.ppn_rate', 0.11);

        $billingDate = now()->startOfMonth();
        $invoice = (new BillingService())->generateInvoice($customer, $billingDate);

        $this->assertNotNull($invoice);
        $this->assertEquals(100000, (float) $invoice->amount);
        $this->assertEquals(11000, (float) $invoice->tax);
        $this->assertEquals(111000, (float) $invoice->total_amount);
    }

    public function test_generate_invoice_is_idempotent_per_month(): void
    {
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $package = Package::factory()->create([
            'tenant_id' => $tenant->id,
            'price' => 150000,
            'is_taxable' => false,
        ]);
        $customer = Customer::factory()->create([
            'tenant_id' => $tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => now()->subMonths(2)->startOfMonth(),
        ]);

        $service = new BillingService();
        $billingDate = now()->startOfMonth();

        $first = $service->generateInvoice($customer, $billingDate);
        $second = $service->generateInvoice($customer, $billingDate);

        $this->assertNotNull($first);
        $this->assertNull($second);
        $this->assertEquals(
            1,
            Invoice::withoutGlobalScopes()->where('customer_id', $customer->id)
                ->whereDate('period_start', $billingDate->startOfMonth()->format('Y-m-d'))
                ->count()
        );
    }
}
