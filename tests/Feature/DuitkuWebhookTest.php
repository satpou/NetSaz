<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\PlatformSetting;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuitkuWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Package $package;
    protected Customer $customer;
    protected Invoice $invoice;
    protected Payment $payment;
    protected string $apiKey = 'test_duitku_api_key_12345';
    protected string $merchantCode = 'D1234';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'ISP Duitku Test',
            'slug' => 'isp-duitku',
            'email' => 'duitku@test.com',
            'status' => 'active',
            'settings' => [
                'payment_gateway' => 'duitku',
                'duitku_merchant_code' => $this->merchantCode,
                'duitku_api_key' => $this->apiKey,
                'duitku_is_production' => false,
            ],
        ]);

        $this->package = Package::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Paket 20 Mbps',
            'price' => 150000,
            'speed' => 20,
        ]);

        $this->customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pelanggan Duitku',
            'email' => 'customer@duitku.test',
            'address' => 'Jl. Merdeka No. 45',
            'package_id' => $this->package->id,
            'status' => 'active',
            'join_date' => now()->subMonth(),
        ]);

        $this->invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-DUITKU-001',
            'total_amount' => 150000,
            'due_date' => now()->addDays(7),
            'status' => 'unpaid',
        ]);

        $this->payment = Payment::create([
            'tenant_id' => $this->tenant->id,
            'invoice_id' => $this->invoice->id,
            'customer_id' => $this->customer->id,
            'payment_number' => 'DUITKU-PAY-123',
            'amount' => 150000,
            'payment_method' => 'gateway',
            'gateway_provider' => 'duitku',
            'gateway_transaction_id' => 'REF-DUITKU-123',
            'status' => 'pending',
        ]);
    }

    public function test_duitku_invoice_webhook_with_invalid_signature(): void
    {
        $payload = [
            'merchantCode' => $this->merchantCode,
            'amount' => '150000',
            'merchantOrderId' => $this->payment->payment_number,
            'resultCode' => '00',
            'signature' => 'invalid_signature_here',
        ];

        $response = $this->post('/webhook/duitku', $payload);
        $response->assertStatus(400);
        $this->assertEquals('pending', $this->payment->fresh()->status);
    }

    public function test_duitku_invoice_webhook_with_valid_signature_settles_payment(): void
    {
        $amount = '150000';
        $signature = md5($this->merchantCode . $amount . $this->payment->payment_number . $this->apiKey);

        $payload = [
            'merchantCode' => $this->merchantCode,
            'amount' => $amount,
            'merchantOrderId' => $this->payment->payment_number,
            'resultCode' => '00',
            'signature' => $signature,
            'reference' => 'REF-DUITKU-123',
        ];

        $response = $this->post('/webhook/duitku', $payload);
        $response->assertStatus(200);
        $this->assertEquals('SUCCESS', $response->getContent());

        $this->assertEquals('success', $this->payment->fresh()->status);
        $this->assertEquals('paid', $this->invoice->fresh()->status);
    }

    public function test_duitku_subscription_webhook_activates_pending_tenant(): void
    {
        PlatformSetting::set('duitku_merchant_code', 'PLATFORM_MERCHANT');
        PlatformSetting::set('duitku_api_key', 'platform_secret_key_abc');

        $pendingTenant = Tenant::create([
            'name' => 'ISP Baru Duitku',
            'slug' => 'isp-baru-duitku',
            'email' => 'admin@ispbaru.com',
            'status' => 'pending_payment',
        ]);

        $user = User::create([
            'tenant_id' => $pendingTenant->id,
            'name' => 'Admin ISP Baru',
            'email' => 'admin@ispbaru.com',
            'password' => bcrypt('secret123'),
            'role' => 'admin',
            'is_active' => false,
        ]);

        $plan = SubscriptionPlan::create([
            'name' => 'Grow',
            'code' => 'grow',
            'price' => 799000,
            'max_active_customers' => 1000,
        ]);

        $sub = TenantSubscription::create([
            'tenant_id' => $pendingTenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'pending_payment',
        ]);

        $merchantOrderId = 'SUB-' . $pendingTenant->id . '-20261119220000';
        $amount = '799000';
        $signature = md5('PLATFORM_MERCHANT' . $amount . $merchantOrderId . 'platform_secret_key_abc');

        $payload = [
            'merchantCode' => 'PLATFORM_MERCHANT',
            'amount' => $amount,
            'merchantOrderId' => $merchantOrderId,
            'resultCode' => '00',
            'signature' => $signature,
            'reference' => 'REF-SUB-999',
            'paymentCode' => 'VA-BCA',
        ];

        $response = $this->post('/webhook/duitku-subscription', $payload);
        $response->assertStatus(200);
        $this->assertEquals('SUCCESS', $response->getContent());

        $freshTenant = $pendingTenant->fresh();
        $this->assertEquals('active', $freshTenant->status);
        $this->assertEquals('duitku', $freshTenant->settings['payment_method']);
        $this->assertEquals('paid', $freshTenant->settings['payment_status']);

        $this->assertTrue((bool) $user->fresh()->is_active);

        $freshSub = $sub->fresh();
        $this->assertEquals('active', $freshSub->status);
        $this->assertNotNull($freshSub->current_period_end);
        $this->assertTrue($freshSub->current_period_end->isFuture());
    }

    public function test_superadmin_can_save_duitku_platform_settings(): void
    {
        $superAdmin = User::create([
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@netsaz.local',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superAdmin)->post('/admin/settings', [
            'platform_payment_gateway' => 'duitku',
            'duitku_merchant_code' => 'D9999',
            'duitku_api_key' => 'secret_key_super_admin',
            'duitku_is_production' => '1',
            'sales_whatsapp' => '083162238930',
            'sales_name' => 'Ahmad Zidnan',
            'banks' => [
                [
                    'bank' => 'BCA',
                    'account_number' => '12345678',
                    'account_name' => 'PT NetSaz Solusi Digital',
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('D9999', PlatformSetting::get('duitku_merchant_code'));
        $this->assertEquals('secret_key_super_admin', PlatformSetting::get('duitku_api_key'));
        $this->assertTrue((bool) PlatformSetting::get('duitku_is_production'));
        $this->assertEquals('duitku', PlatformSetting::get('platform_payment_gateway'));
    }
}
