<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $tenantAdmin;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubscriptionPlanSeeder::class);

        $this->tenant = Tenant::create([
            'name' => 'Test ISP',
            'slug' => 'test-isp',
            'email' => 'admin@test-isp.com',
            'status' => 'active',
        ]);

        $this->tenantAdmin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'tenant_id' => $this->tenant->id,
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@netsaz.local',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'tenant_id' => null,
            'is_active' => true,
        ]);
    }

    public function test_tenant_admin_can_login_via_main_domain(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($this->tenantAdmin);
        $this->assertEquals($this->tenant->id, session('tenant_id'));
    }

    public function test_tenant_admin_can_login_via_cloudflare_tunnel(): void
    {
        $tunnelHost = 'records-mud-giving-participate.trycloudflare.com';

        $response = $this->withServerVariables([
            'HTTP_HOST' => $tunnelHost,
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => $tunnelHost,
        ])->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->tenantAdmin);
        $this->assertEquals($this->tenant->id, session('tenant_id'));
    }

    public function test_tenant_admin_can_access_dashboard_on_subdomain(): void
    {
        $response = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_id' => $this->tenant->id, 'active_tenant_id' => $this->tenant->id])
            ->get('http://' . $this->tenant->slug . '.localhost/dashboard');

        $response->assertStatus(200);

        $custResponse = $this->actingAs($this->tenantAdmin)
            ->withSession(['tenant_id' => $this->tenant->id, 'active_tenant_id' => $this->tenant->id])
            ->get('http://' . $this->tenant->slug . '.localhost/customers');

        $custResponse->assertStatus(200);
    }

    public function test_wrong_password_fails_login(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
