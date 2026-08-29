<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\MikrotikAccount;
use App\Models\NasServer;
use App\Models\Package;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PppoeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected User $user;

    protected Package $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->tenant = Tenant::factory()->create(['status' => 'active']);

        $plan = SubscriptionPlan::create([
            'code' => 'pro',
            'name' => 'Pro',
            'price' => 299000,
            'billing_period' => 'monthly',
            'max_active_customers' => 500,
            'max_routers' => 10,
            'max_staff' => 10,
            'features' => ['radius_hub', 'olt_management'],
            'sort_order' => 3,
        ]);

        TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->user->assignRole('super_admin');

        $this->package = Package::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->withSession(['active_tenant_id' => $this->tenant->id]);
    }

    protected function storePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Pelanggan PPPoE',
            'email' => 'pppoe@example.com',
            'phone' => '081234567891',
            'address' => 'Jl. Contoh 1',
            'package_id' => $this->package->id,
            'status' => 'active',
            'join_date' => Carbon::now()->format('Y-m-d'),
            'pppoe_username' => 'cust001',
            'pppoe_password' => 'rahasia123',
            'pppoe_service' => 'pppoe',
        ], $overrides);
    }

    protected function makeAccount(array $overrides = []): MikrotikAccount
    {
        NasServer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router Utama',
            'host' => '192.168.88.1',
            'username' => 'admin',
            'password' => 'secret',
            'api_port' => 8728,
            'status' => 'online',
        ]);

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $this->package->id,
            'phone' => '081234567892',
        ]);

        return MikrotikAccount::create(array_merge([
            'tenant_id' => $this->tenant->id,
            'customer_id' => $customer->id,
            'pppoe_secret' => 'cust001',
            'pppoe_password' => 'rahasia123',
            'pppoe_service' => 'pppoe',
            'status' => 'active',
        ], $overrides));
    }

    public function test_store_creates_and_provisions_pppoe_account(): void
    {
        NasServer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router Utama',
            'host' => '192.168.88.1',
            'username' => 'admin',
            'password' => 'secret',
            'api_port' => 8728,
            'status' => 'online',
        ]);

        Http::fake([
            'http://192.168.88.1:8728/*' => function ($request) {
                if ($request->method() === 'GET') {
                    return Http::response([], 200);
                }

                return Http::response(['id' => 's1', 'name' => 'cust001'], 201);
            },
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.store', ['tenant_slug' => $this->tenant->slug]), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mikrotik_accounts', [
            'tenant_id' => $this->tenant->id,
            'pppoe_secret' => 'cust001',
            'pppoe_password' => 'rahasia123',
            'status' => 'active',
        ]);

        $account = MikrotikAccount::where('pppoe_secret', 'cust001')->first();
        $this->assertNotNull($account->provisioned_at);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_ends_with($request->url(), '/rest/ppp/secret')
                && $request['name'] === 'cust001'
                && $request['password'] === 'rahasia123'
                && $request['service'] === 'pppoe';
        });
    }

    public function test_store_without_nas_saves_customer_but_warns_pppoe_not_provisioned(): void
    {
        Http::fake();

        $response = $this->actingAs($this->user)
            ->post(route('customers.store', ['tenant_slug' => $this->tenant->slug]), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('warning');

        $this->assertDatabaseHas('mikrotik_accounts', [
            'pppoe_secret' => 'cust001',
            'status' => 'active',
        ]);

        $account = MikrotikAccount::where('pppoe_secret', 'cust001')->first();
        $this->assertNull($account->provisioned_at);
    }

    public function test_store_applies_package_bandwidth_as_simple_queue(): void
    {
        NasServer::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Router Utama',
            'host' => '192.168.88.1',
            'username' => 'admin',
            'password' => 'secret',
            'api_port' => 8728,
            'status' => 'online',
        ]);

        $this->package->update(['speed' => '20Mbps']);

        Http::fake([
            'http://192.168.88.1:8728/*' => function ($request) {
                if ($request->method() === 'GET') {
                    return Http::response([], 200);
                }

                if ($request->method() === 'POST' && str_ends_with($request->url(), '/rest/ppp/secret')) {
                    return Http::response(['id' => 's1', 'name' => 'cust001'], 201);
                }

                return Http::response(['id' => 'q1'], 201);
            },
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.store', ['tenant_slug' => $this->tenant->slug]), $this->storePayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_ends_with($request->url(), '/rest/queue/simple')
                && $request['name'] === 'cust001'
                && $request['max-limit'] === '20M/20M';
        });
    }

    public function test_parse_speed_handles_down_up_and_files(): void
    {
        $this->package->update(['speed' => '50Mbps/10Mbps']);

        $this->assertSame(['down' => 50.0, 'up' => 10.0], $this->package->parseSpeed());

        $this->package->update(['speed' => '1Gbps']);

        $this->assertSame(['down' => 1000.0, 'up' => 1000.0], $this->package->parseSpeed());

        $this->package->update(['speed' => '']);

        $this->assertNull($this->package->parseSpeed());
    }

    public function test_store_requires_password_when_username_provided(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('customers.store', ['tenant_slug' => $this->tenant->slug]), $this->storePayload([
                'pppoe_username' => 'cust001',
                'pppoe_password' => null,
            ]));

        $response->assertSessionHasErrors('pppoe_password');
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('mikrotik_accounts', 0);
    }

    public function test_enable_pppoe_updates_router_and_account(): void
    {
        $account = $this->makeAccount(['status' => 'disabled']);

        Http::fake([
            'http://192.168.88.1:8728/*' => function ($request) {
                if ($request->method() === 'PATCH') {
                    return Http::response(['id' => 'x'], 200);
                }

                return Http::response([], 200);
            },
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.pppoe.enable', ['tenant_slug' => $this->tenant->slug, 'customer' => $account->customer_id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mikrotik_accounts', [
            'id' => $account->id,
            'status' => 'active',
        ]);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_ends_with($request->url(), '/rest/ppp/secret/cust001')
                && $request['disabled'] === 'no';
        });
    }

    public function test_reset_password_updates_router_and_account(): void
    {
        $account = $this->makeAccount();

        Http::fake([
            'http://192.168.88.1:8728/*' => function ($request) {
                if ($request->method() === 'GET') {
                    return Http::response([['id' => 's1', 'name' => 'cust001']], 200);
                }

                return Http::response(['id' => 's1'], 200);
            },
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.pppoe.reset-password', ['tenant_slug' => $this->tenant->slug, 'customer' => $account->customer_id]), [
                'pppoe_password' => 'passwordbaru99',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mikrotik_accounts', [
            'id' => $account->id,
            'pppoe_password' => 'passwordbaru99',
        ]);

        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH'
                && str_ends_with($request->url(), '/rest/ppp/secret/cust001')
                && $request['password'] === 'passwordbaru99';
        });
    }

    public function test_delete_pppoe_removes_account_and_router_secret(): void
    {
        $account = $this->makeAccount();

        Http::fake([
            'http://192.168.88.1:8728/*' => function ($request) {
                if ($request->method() === 'DELETE') {
                    return Http::response([], 200);
                }

                return Http::response([['id' => 's1']], 200);
            },
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.pppoe.delete', ['tenant_slug' => $this->tenant->slug, 'customer' => $account->customer_id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('mikrotik_accounts', ['id' => $account->id]);

        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE'
                && str_ends_with($request->url(), '/rest/ppp/secret/cust001');
        });
    }

    public function test_customer_show_page_renders_pppoe_panel_with_actions(): void
    {
        $account = $this->makeAccount();

        $response = $this->actingAs($this->user)
            ->get(route('customers.show', ['tenant_slug' => $this->tenant->slug, 'customer' => $account->customer_id]));

        $response->assertOk();
        $response->assertSee('Akun PPPoE');
        $response->assertSee('cust001');
        $response->assertSee('Nonaktifkan di Router');
        $response->assertSee('Reset Password');
    }
}
