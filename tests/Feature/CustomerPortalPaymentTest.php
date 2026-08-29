<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerPortalPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Customer $customer;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        SubscriptionPlan::create([
            'code' => 'launch',
            'name' => 'Launch',
            'price' => 99000,
            'billing_period' => 'monthly',
            'max_active_customers' => 150,
            'max_routers' => 1,
            'max_staff' => 1,
            'features' => ['whatsapp_broadcast'],
            'sort_order' => 1,
        ]);

        $this->tenant = Tenant::factory()->create(['status' => 'active']);
        $package = Package::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Portal Customer',
            'email' => 'portal@example.com',
            'phone' => '081234567890',
            'address' => '123 Main St',
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => Carbon::now(),
        ]);

        $plan = SubscriptionPlan::where('code', 'launch')->first();
        TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $this->invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'package_id' => $package->id,
            'invoice_number' => 'INV-TEST-0001',
            'period_start' => Carbon::now()->startOfMonth(),
            'period_end' => Carbon::now()->endOfMonth(),
            'amount' => 100000,
            'discount' => 0,
            'tax' => 0,
            'total_amount' => 100000,
            'due_date' => Carbon::now()->addDays(7),
            'status' => 'unpaid',
        ]);

        URL::defaults(['tenant_slug' => $this->tenant->slug]);
    }

    protected function portalServer(): string
    {
        return $this->tenant->slug.'.'.config('app.main_domain');
    }

    protected function actingAsCustomer()
    {
        return $this->withServerVariables(['HTTP_HOST' => $this->portalServer()])
            ->actingAs($this->customer, 'customer');
    }

    public function test_manual_payment_submission_creates_pending_payment_with_proof(): void
    {
        Storage::fake('public');

        $this->tenant->update([
            'settings' => [
                'payment_bank_accounts' => [
                    ['bank' => 'BCA', 'account_name' => 'PT Primanet', 'account_number' => '1234567890'],
                ],
            ],
        ]);

        $response = $this->actingAsCustomer()
            ->post(route('customer.portal.invoices.pay.manual', $this->invoice->id), [
                'payment_method' => 'transfer',
                'reference_number' => 'TRF/2026/0001',
                'notes' => 'Pembayaran bulanan',
                'proof_of_payment' => UploadedFile::fake()->image('bukti.jpg'),
            ]);

        $response->assertRedirect(route('customer.portal.payments', ['invoice_id' => $this->invoice->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'payment_method' => 'transfer',
            'reference_number' => 'TRF/2026/0001',
            'status' => 'pending',
        ]);

        $payment = $this->invoice->payments()->first();
        $this->assertNotNull($payment->proof_of_payment);
        Storage::disk('public')->assertExists($payment->proof_of_payment);
    }

    public function test_manual_payment_requires_proof_of_payment(): void
    {
        $response = $this->actingAsCustomer()
            ->post(route('customer.portal.invoices.pay.manual', $this->invoice->id), [
                'payment_method' => 'transfer',
                'reference_number' => 'TRF/0001',
            ]);

        $response->assertSessionHasErrors('proof_of_payment');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_manual_payment_cannot_be_submitted_for_other_customers_invoice(): void
    {
        $otherTenant = Tenant::factory()->create(['status' => 'active']);
        $otherPackage = Package::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherInvoice = Invoice::create([
            'tenant_id' => $otherTenant->id,
            'customer_id' => $this->customer->id,
            'package_id' => $otherPackage->id,
            'invoice_number' => 'INV-OTHER-1',
            'amount' => 100000,
            'discount' => 0,
            'tax' => 0,
            'total_amount' => 100000,
            'due_date' => Carbon::now()->addDays(7),
            'status' => 'unpaid',
        ]);

        $response = $this->actingAsCustomer()
            ->post(route('customer.portal.invoices.pay.manual', $otherInvoice->id), [
                'payment_method' => 'transfer',
                'proof_of_payment' => UploadedFile::fake()->image('bukti.jpg'),
            ]);

        $response->assertForbidden();
    }

    public function test_qris_payment_flow_creates_payment_and_shows_qr(): void
    {
        Http::fake([
            'https://api.xendit.co/qr_codes' => Http::response([
                'id' => 'qr-123',
                'reference_id' => 'XDT/20260802/1/1234',
                'qr_string' => '00020101021226480014com.gopay.idQRIS-wib',
                'status' => 'ACTIVE',
            ], 200),
        ]);

        $this->tenant->update([
            'settings' => ['xendit_api_key' => 'xnd_development_fake'],
        ]);

        $response = $this->actingAsCustomer()
            ->post(route('customer.portal.invoices.pay.qris', $this->invoice->id));

        $response->assertRedirect(route('customer.portal.invoices.pay.qris.show', $this->invoice->id));

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'payment_method' => 'qris',
            'gateway_provider' => 'xendit',
            'status' => 'pending',
        ]);

        $payment = $this->invoice->payments()->where('payment_method', 'qris')->first();
        $this->assertEquals('00020101021226480014com.gopay.idQRIS-wib', $payment->qr_string);

        $showResponse = $this->actingAsCustomer()
            ->get(route('customer.portal.invoices.pay.qris.show', $this->invoice->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Scan QRIS');
    }

    public function test_qris_check_confirms_payment_when_completed(): void
    {
        Http::fake([
            'https://api.xendit.co/qr_codes/*' => Http::response(['status' => 'COMPLETED'], 200),
        ]);

        $this->tenant->update([
            'settings' => ['xendit_api_key' => 'xnd_development_fake'],
        ]);

        $payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'payment_number' => 'XDT/20260802/1/9999',
            'amount' => 100000,
            'payment_method' => 'qris',
            'gateway_provider' => 'xendit',
            'gateway_transaction_id' => 'qr-999',
            'qr_string' => '0002010102122648',
            'status' => 'pending',
        ]);

        $response = $this->actingAsCustomer()
            ->post(route('customer.portal.invoices.pay.qris.check', $this->invoice->id));

        $response->assertRedirect(route('customer.portal.invoices.show', $this->invoice->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $this->invoice->id,
            'status' => 'paid',
        ]);
    }

    public function test_qris_unavailable_when_xendit_not_configured(): void
    {
        $this->tenant->update(['settings' => []]);

        $response = $this->actingAsCustomer()
            ->post(route('customer.portal.invoices.pay.qris', $this->invoice->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('payments', 0);
    }
}
