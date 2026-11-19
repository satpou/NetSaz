<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_force_delete_tenant_with_complex_relationships(): void
    {
        $superAdmin = User::create([
            'name' => 'Platform Super Admin',
            'email' => 'superadmin@netsaz.local',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $tenant = Tenant::create([
            'name' => 'ISP To Purge',
            'slug' => 'isp-to-purge',
            'email' => 'isp@purge.test',
            'status' => 'active',
        ]);

        $tenantUser = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Staff ISP',
            'email' => 'staff@purge.test',
            'password' => bcrypt('secret'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $area = Area::create([
            'tenant_id' => $tenant->id,
            'name' => 'Area Timur',
        ]);

        $package = Package::create([
            'tenant_id' => $tenant->id,
            'name' => 'Paket Cepat',
            'price' => 200000,
            'speed' => 50,
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name' => 'Customer Purge',
            'email' => 'cust@purge.test',
            'address' => 'Jl. Uji No. 9',
            'package_id' => $package->id,
            'area_id' => $area->id,
            'status' => 'active',
            'join_date' => now(),
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-PURGE-01',
            'total_amount' => 200000,
            'due_date' => now()->addDays(5),
            'status' => 'unpaid',
        ]);

        $payment = Payment::create([
            'tenant_id' => $tenant->id,
            'invoice_id' => $invoice->id,
            'customer_id' => $customer->id,
            'payment_number' => 'PAY-PURGE-01',
            'amount' => 200000,
            'payment_method' => 'manual',
            'status' => 'pending',
        ]);

        // Soft delete first
        $tenant->delete();
        $this->assertSoftDeleted('tenants', ['id' => $tenant->id]);

        // Force delete
        $response = $this->actingAs($superAdmin)->delete("/admin/tenants/{$tenant->id}/force-delete");
        $response->assertRedirect('/admin/tenants?status=trashed');
        $response->assertSessionHas('success');

        // Confirm tenant and records are permanently wiped
        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
        $this->assertDatabaseMissing('users', ['id' => $tenantUser->id]);
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('payments', ['id' => $payment->id]);
    }
}
