<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->tenant = Tenant::factory()->create(['status' => 'active']);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->user->assignRole('super_admin');

        $this->withSession(['active_tenant_id' => $this->tenant->id]);
    }

    protected function makePayment(): Payment
    {
        $package = Package::factory()->create(['tenant_id' => $this->tenant->id]);
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
        ]);
        $invoice = Invoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
        ]);

        return Payment::create([
            'tenant_id' => $this->tenant->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-' . now()->format('Ymd') . '-TEST001',
            'amount' => 150000,
            'payment_method' => 'transfer',
            'reference_number' => 'REF-12345',
            'status' => 'success',
            'paid_at' => now(),
            'verified_by' => $this->user->id,
        ]);
    }

    public function test_payment_show_page_renders_with_receipt_button(): void
    {
        $payment = $this->makePayment();

        $response = $this->actingAs($this->user)
            ->get(route('payments.show', ['tenant_slug' => $this->tenant->slug, 'payment' => $payment->id]));

        $response->assertOk();
        $response->assertSee('Cetak Kuitansi');
        $response->assertSee('transfer');
    }

    public function test_payment_receipt_pdf_downloads(): void
    {
        $payment = $this->makePayment();

        $response = $this->actingAs($this->user)
            ->get(route('payments.receipt', ['tenant_slug' => $this->tenant->slug, 'payment' => $payment->id]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_payment_list_shows_receipt_link(): void
    {
        $payment = $this->makePayment();

        $response = $this->actingAs($this->user)
            ->get(route('payments.index', ['tenant_slug' => $this->tenant->slug]));

        $response->assertOk();
        $response->assertSee('Kuitansi');
    }
}
