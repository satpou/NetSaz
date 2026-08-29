<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected Customer $customerA;
    protected Customer $customerB;
    protected Invoice $invoiceA;
    protected Invoice $invoiceB;
    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = Tenant::create([
            'name' => 'ISP A',
            'slug' => 'isp-a',
            'email' => 'isp-a@test.com',
            'status' => 'active',
        ]);

        $this->tenantB = Tenant::create([
            'name' => 'ISP B',
            'slug' => 'isp-b',
            'email' => 'isp-b@test.com',
            'status' => 'active',
        ]);

        $packageA = Package::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Paket A',
            'description' => 'Paket internet A',
            'price' => 100000,
            'speed' => 10,
        ]);

        $packageB = Package::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Paket B',
            'description' => 'Paket internet B',
            'price' => 150000,
            'speed' => 20,
        ]);

        $this->customerA = Customer::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Customer A',
            'email' => 'customer-a@test.com',
            'address' => 'Address A',
            'package_id' => $packageA->id,
            'status' => 'active',
            'join_date' => now()->subMonth(),
        ]);

        $this->customerB = Customer::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'Customer B',
            'email' => 'customer-b@test.com',
            'address' => 'Address B',
            'package_id' => $packageB->id,
            'status' => 'active',
            'join_date' => now()->subMonth(),
        ]);

        $this->invoiceA = Invoice::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->create([
            'tenant_id' => $this->tenantA->id,
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV/A/2025/01/0001',
            'total_amount' => 100000,
            'due_date' => now()->addDays(5),
            'status' => 'unpaid',
        ]);

        $this->invoiceB = Invoice::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)->create([
            'tenant_id' => $this->tenantB->id,
            'customer_id' => $this->customerB->id,
            'invoice_number' => 'INV/B/2025/01/0001',
            'total_amount' => 150000,
            'due_date' => now()->addDays(5),
            'status' => 'unpaid',
        ]);

        $this->userA = User::create([
            'name' => 'User A',
            'email' => 'usera@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenantA->id,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);

        $this->userB = User::create([
            'name' => 'User B',
            'email' => 'userb@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $this->tenantB->id,
            'role' => 'tenant_admin',
            'is_active' => true,
        ]);
    }

    public function test_user_from_tenant_a_cannot_see_invoices_from_tenant_b(): void
    {
        $this->actingAs($this->userA);
        session(['active_tenant_id' => $this->tenantA->id]);

        $invoicesFromA = Invoice::all();

        $this->assertTrue($invoicesFromA->contains($this->invoiceA));
        $this->assertFalse($invoicesFromA->contains($this->invoiceB));
    }

    public function test_user_from_tenant_b_cannot_see_invoices_from_tenant_a(): void
    {
        $this->actingAs($this->userB);
        session(['active_tenant_id' => $this->tenantB->id]);

        $invoicesFromB = Invoice::all();

        $this->assertTrue($invoicesFromB->contains($this->invoiceB));
        $this->assertFalse($invoicesFromB->contains($this->invoiceA));
    }

    public function test_invoice_created_automatically_has_correct_tenant_id(): void
    {
        $this->actingAs($this->userA);
        session(['active_tenant_id' => $this->tenantA->id]);

        $newInvoice = Invoice::create([
            'customer_id' => $this->customerA->id,
            'invoice_number' => 'INV/A/2025/02/0099',
            'total_amount' => 200000,
            'due_date' => now()->addDays(5),
            'status' => 'unpaid',
        ]);

        $this->assertEquals($this->tenantA->id, $newInvoice->tenant_id);
        $this->assertDatabaseHas('invoices', [
            'id' => $newInvoice->id,
            'tenant_id' => $this->tenantA->id,
        ]);
    }

    public function test_invoices_query_isolation_cannot_be_bypassed(): void
    {
        session(['active_tenant_id' => $this->tenantA->id]);

        $allInvoicesA = Invoice::withoutGlobalScopes()->get();

        $tenantAIds = $allInvoicesA->where('tenant_id', $this->tenantA->id)->pluck('id');
        $tenantBIds = $allInvoicesA->where('tenant_id', $this->tenantB->id)->pluck('id');

        $this->assertTrue($tenantAIds->contains($this->invoiceA->id));
        $this->assertFalse($tenantAIds->contains($this->invoiceB->id));

        $this->assertFalse($tenantBIds->contains($this->invoiceA->id));
        $this->assertTrue($tenantBIds->contains($this->invoiceB->id));
    }
}