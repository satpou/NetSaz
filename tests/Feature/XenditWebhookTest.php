<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XenditWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create([
            'status' => 'active',
            'settings' => [
                'xendit_api_key' => 'XND_API_KEY',
                'xendit_webhook_token' => 'TOKEN-RAHASIA',
            ],
        ]);

        $package = Package::factory()->create(['tenant_id' => $this->tenant->id]);
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'invoice_number' => 'INV/XD/2026/01/0001',
            'total_amount' => 100000,
            'due_date' => now()->addDays(5),
            'status' => 'unpaid',
        ]);

        $this->payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_number' => 'XDT/20260731/1/1234',
            'amount' => 100000,
            'payment_method' => 'gateway',
            'gateway_provider' => 'xendit',
            'status' => 'pending',
        ]);
    }

    public function test_xendit_webhook_rejects_invalid_token(): void
    {
        $payload = [
            'external_id' => $this->payment->payment_number,
            'status' => 'PAID',
        ];

        $response = $this->postJson('/api/webhook/xendit', $payload, [
            'x-callback-token' => 'WRONG_TOKEN',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Invalid token']);

        $this->payment->refresh();
        $this->assertEquals('pending', $this->payment->status);

        $this->assertDatabaseHas('payment_gateway_logs', [
            'provider' => 'xendit',
            'signature_valid' => false,
            'processed' => false,
        ]);
    }

    public function test_xendit_webhook_accepts_valid_token(): void
    {
        $payload = [
            'external_id' => $this->payment->payment_number,
            'status' => 'PAID',
        ];

        $response = $this->postJson('/api/webhook/xendit', $payload, [
            'x-callback-token' => 'TOKEN-RAHASIA',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'ok']);

        $this->payment->refresh();
        $this->assertEquals('success', $this->payment->status);
        $this->assertNotNull($this->payment->paid_at);

        $this->assertDatabaseHas('payment_gateway_logs', [
            'provider' => 'xendit',
            'signature_valid' => true,
            'processed' => true,
        ]);
    }

    public function test_xendit_webhook_does_not_accept_api_key_as_token(): void
    {
        $payload = [
            'external_id' => $this->payment->payment_number,
            'status' => 'PAID',
        ];

        $response = $this->postJson('/api/webhook/xendit', $payload, [
            'x-callback-token' => 'XND_API_KEY',
        ]);

        $response->assertStatus(401);
        $this->payment->refresh();
        $this->assertEquals('pending', $this->payment->status);
    }
}
