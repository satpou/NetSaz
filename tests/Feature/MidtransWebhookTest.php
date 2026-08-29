<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\MikrotikAccount;
use App\Models\NasServer;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected Package $package;

    protected Customer $customer;

    protected Invoice $invoice;

    protected Payment $payment;

    protected MidtransService $midtransService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'ISP Midtrans',
            'slug' => 'midtrans-test',
            'email' => 'midtrans@test.com',
            'status' => 'active',
        ]);

        $this->package = Package::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Package',
            'description' => 'Test',
            'price' => 100000,
            'speed' => 10,
        ]);

        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'address' => 'Test Address',
            'package_id' => $this->package->id,
            'status' => 'active',
            'join_date' => now()->subMonth(),
        ]);

        $this->invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV/MT/2024/01/0001',
            'total_amount' => 100000,
            'due_date' => now()->addDays(5),
            'status' => 'unpaid',
        ]);

        $this->payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'invoice_id' => $this->invoice->id,
            'payment_number' => 'PAY/MT/ORDER123',
            'amount' => 100000,
            'payment_method' => 'gateway',
            'gateway_provider' => 'midtrans',
            'gateway_transaction_id' => 'MIDTRANS-TRANS-ABC',
            'status' => 'pending',
        ]);

        $this->midtransService = new MidtransService;

        // Mock Midtrans config for testing
        config()->set('services.midtrans.server_key', 'YOUR_SERVER_KEY');
        config()->set('services.midtrans.client_key', 'YOUR_CLIENT_KEY');
        config()->set('services.midtrans.is_production', false);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        Event::fake(); // Prevent actual email sending etc.

        $payload = [
            'order_id' => $this->payment->payment_number,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_id' => $this->payment->gateway_transaction_id,
        ];

        $response = $this->postJson(route('webhook.midtrans', ['tenant_code' => $this->tenant->slug]), $payload, [
            'X-Callback-Token' => 'INVALID_SIGNATURE',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid signature']);

        $this->assertDatabaseHas('payment_gateway_logs', [
            'provider' => 'midtrans',
            'signature_valid' => false,
            'processed' => false,
        ]);

        $this->payment->refresh();
        $this->assertEquals('pending', $this->payment->status);
    }

    public function test_webhook_successful_payment_updates_status(): void
    {
        Event::fake(); // Prevent actual email sending etc.

        $payload = [
            'order_id' => $this->payment->payment_number,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_id' => $this->payment->gateway_transaction_id,
            'payment_type' => 'credit_card',
            'settlement_time' => now()->toDateTimeString(),
        ];

        $signature = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key')
        );

        $response = $this->postJson(route('webhook.midtrans', ['tenant_code' => $this->tenant->slug]), $payload, [
            'X-Callback-Token' => $signature,
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('payment_gateway_logs', [
            'provider' => 'midtrans',
            'signature_valid' => true,
            'processed' => true,
        ]);

        $this->payment->refresh();
        $this->assertEquals('success', $this->payment->status);
        $this->assertNotNull($this->payment->paid_at);

        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
    }

    public function test_webhook_duplicate_call_does_not_double_process(): void
    {
        Event::fake(); // Prevent actual email sending etc.

        $payload = [
            'order_id' => $this->payment->payment_number,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_id' => $this->payment->gateway_transaction_id,
            'payment_type' => 'credit_card',
            'settlement_time' => now()->toDateTimeString(),
        ];

        $signature = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key')
        );

        // First call
        $this->postJson(route('webhook.midtrans', ['tenant_code' => $this->tenant->slug]), $payload, [
            'X-Callback-Token' => $signature,
        ])->assertOk();

        // Second call - should be idempotent, not update again
        $this->postJson(route('webhook.midtrans', ['tenant_code' => $this->tenant->slug]), $payload, [
            'X-Callback-Token' => $signature,
        ])->assertOk();

        $this->payment->refresh();
        $this->assertEquals('success', $this->payment->status);
        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);

        // Verify idempotency: payment was not double-counted
        $remainingAmount = $this->invoice->remaining_amount;
        $this->assertEquals(0, $remainingAmount, 'Invoice should not be double-paid');
    }

    public function test_webhook_success_activates_pppoe_account(): void
    {
        Event::fake();

        $this->payment->update(['customer_id' => $this->customer->id]);

        NasServer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router Utama',
            'host' => '192.168.1.11',
            'username' => 'admin',
            'password' => 'secret',
            'api_port' => 8728,
            'status' => 'online',
        ]);

        $account = MikrotikAccount::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'pppoe_secret' => 'custpaused',
            'pppoe_password' => 'rahasia123',
            'pppoe_service' => 'pppoe',
            'status' => 'disabled',
        ]);

        Http::fake([
            'http://192.168.1.11:8728/*' => function ($request) {
                if ($request->method() === 'PATCH') {
                    return Http::response(['id' => 'x'], 200);
                }

                return Http::response([], 200);
            },
        ]);

        $payload = [
            'order_id' => $this->payment->payment_number,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_id' => $this->payment->gateway_transaction_id,
            'payment_type' => 'credit_card',
            'settlement_time' => now()->toDateTimeString(),
        ];

        $signature = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key')
        );

        $this->postJson(route('webhook.midtrans', ['tenant_code' => $this->tenant->slug]), $payload, [
            'X-Callback-Token' => $signature,
        ])->assertOk();

        $this->assertEquals('success', $this->payment->refresh()->status);
        $this->assertEquals('active', $account->refresh()->status);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_ends_with($request->url(), '/rest/ppp/secret/custpaused')
                && $request['disabled'] === 'no';
        });
    }

    public function test_webhook_partial_payment_updates_invoice_status_to_partial(): void
    {
        Event::fake();

        $this->invoice->update(['total_amount' => 200000]); // Make invoice larger than payment

        $payload = [
            'order_id' => $this->payment->payment_number,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'transaction_id' => $this->payment->gateway_transaction_id,
            'payment_type' => 'credit_card',
            'settlement_time' => now()->toDateTimeString(),
        ];

        $signature = hash('sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key')
        );

        $this->postJson(route('webhook.midtrans', ['tenant_code' => $this->tenant->slug]), $payload, [
            'X-Callback-Token' => $signature,
        ])->assertOk();

        $this->invoice->refresh();
        $this->assertEquals('partial', $this->invoice->status);
    }
}
