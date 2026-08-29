<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
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
    }

    public function test_admin_can_download_invoice_pdf(): void
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

        $response = $this->actingAs($this->user)
            ->withSession(['active_tenant_id' => $this->tenant->id])
            ->get(route('invoices.pdf', ['tenant_slug' => $this->tenant->slug, 'invoice' => $invoice->id]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_invoice_show_has_print_button(): void
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

        $response = $this->actingAs($this->user)
            ->withSession(['active_tenant_id' => $this->tenant->id])
            ->get(route('invoices.show', ['tenant_slug' => $this->tenant->slug, 'invoice' => $invoice->id]));

        $response->assertOk();
        $response->assertSee('Cetak PDF');
    }
}
