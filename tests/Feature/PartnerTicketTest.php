<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerTicketTest extends TestCase
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

    public function test_partner_can_be_created(): void
    {
        $this->actingAs($this->user)
            ->withSession(['active_tenant_id' => $this->tenant->id])
            ->post(route('partners.store', ['tenant_slug' => $this->tenant->slug]), [
                'name' => 'Toko Elektronik Jaya',
                'contact_person' => 'Budi',
                'phone' => '081234567890',
                'email' => 'toko@example.com',
                'commission_rate' => 5,
                'status' => 'active',
                'notes' => 'Mitra wilayah barat',
            ])
            ->assertRedirect(route('partners.index'));

        $this->assertDatabaseHas('partners', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Toko Elektronik Jaya',
            'commission_rate' => 5.00,
            'status' => 'active',
        ]);
    }

    public function test_partner_index_shows_partners_only_from_active_tenant(): void
    {
        $otherTenant = Tenant::factory()->create(['status' => 'active']);
        Partner::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Mitra Saya',
            'commission_rate' => 0,
            'status' => 'active',
        ]);
        Partner::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Mitra Lain',
            'commission_rate' => 0,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_tenant_id' => $this->tenant->id])
            ->get(route('partners.index', ['tenant_slug' => $this->tenant->slug]));

        $response->assertOk();
        $response->assertSee('Mitra Saya');
        $response->assertDontSee('Mitra Lain');
    }

    public function test_ticket_can_be_created_and_status_updated(): void
    {
        $package = Package::factory()->create(['tenant_id' => $this->tenant->id]);
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->withSession(['active_tenant_id' => $this->tenant->id])
            ->post(route('tickets.store', ['tenant_slug' => $this->tenant->slug]), [
                'customer_id' => $customer->id,
                'subject' => 'Koneksi terputus',
                'message' => 'Internet sering putus sejak kemarin.',
                'priority' => 'high',
                'assigned_to' => $this->user->id,
            ])
            ->assertRedirect(route('tickets.index'));

        $ticket = Ticket::where('tenant_id', $this->tenant->id)->first();

        $this->assertNotNull($ticket);
        $this->assertEquals('open', $ticket->status);
        $this->assertEquals('high', $ticket->priority);
        $this->assertMatchesRegularExpression('/^TCK-\d{8}-[A-Z0-9]{4}$/', $ticket->ticket_number);

        $this->actingAs($this->user)
            ->withSession(['active_tenant_id' => $this->tenant->id])
            ->put(route('tickets.status', ['tenant_slug' => $this->tenant->slug, 'ticket' => $ticket->id]), [
                'status' => 'resolved',
                'resolution_notes' => 'Reset port OLT.',
            ])
            ->assertRedirect();

        $ticket->refresh();
        $this->assertEquals('resolved', $ticket->status);
        $this->assertEquals('Reset port OLT.', $ticket->resolution_notes);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_ticket_index_is_scoped_to_tenant(): void
    {
        $otherTenant = Tenant::factory()->create(['status' => 'active']);

        Ticket::create([
            'tenant_id' => $this->tenant->id,
            'ticket_number' => 'TCK-20260731-TEST',
            'subject' => 'Tiket Saya',
            'message' => 'Test',
            'priority' => 'normal',
            'status' => 'open',
            'source' => 'staff',
        ]);
        Ticket::create([
            'tenant_id' => $otherTenant->id,
            'ticket_number' => 'TCK-20260731-OTHR',
            'subject' => 'Tiket Lain',
            'message' => 'Test',
            'priority' => 'normal',
            'status' => 'open',
            'source' => 'staff',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_tenant_id' => $this->tenant->id])
            ->get(route('tickets.index', ['tenant_slug' => $this->tenant->slug]));

        $response->assertOk();
        $response->assertSee('Tiket Saya');
        $response->assertDontSee('Tiket Lain');
    }
}
