<?php

namespace Tests\Feature;

use App\Models\Customer;
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

class CustomerPortalLinkTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

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
            'current_period_end' => Carbon::now()->addMonth()->subDay(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $this->user->assignRole('super_admin');

        $this->withSession(['active_tenant_id' => $this->tenant->id]);
    }

    protected function makeCustomer(array $overrides = []): Customer
    {
        $package = Package::factory()->create(['tenant_id' => $this->tenant->id]);

        return Customer::factory()->create(array_merge([
            'tenant_id' => $this->tenant->id,
            'package_id' => $package->id,
            'phone' => '081234567890',
        ], $overrides));
    }

    public function test_customer_show_page_displays_portal_url_and_send_buttons(): void
    {
        $customer = $this->makeCustomer();

        $response = $this->actingAs($this->user)
            ->get(route('customers.show', ['tenant_slug' => $this->tenant->slug, 'customer' => $customer->id]));

        $response->assertOk();
        $response->assertSee($customer->portalUrl());
        $response->assertSee('Kirim Link Portal');
        $response->assertSee('Kirim Selamat Datang');
    }

    public function test_portal_url_uses_tenant_subdomain(): void
    {
        $customer = $this->makeCustomer();

        $this->assertStringContainsString("{$this->tenant->slug}.", $customer->portalUrl());
        $this->assertStringContainsString('/portal/login', $customer->portalUrl());
    }

    public function test_send_portal_link_posts_to_whatsapp_bridge(): void
    {
        Http::fake(['*' => Http::response(['status' => 'sent'])]);

        $customer = $this->makeCustomer();
        $portalUrl = $customer->portalUrl();

        $response = $this->actingAs($this->user)
            ->post(route('customers.send-portal-link', ['tenant_slug' => $this->tenant->slug, 'customer' => $customer->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $customer->refresh();
        $this->assertTrue(strlen((string) $customer->portal_pin) > 20);

        Http::assertSent(function ($request) use ($customer, $portalUrl) {
            $apiUrl = config('services.wa_notification.api_url');
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);
            $text = $query['text'] ?? '';

            return $request->method() === 'GET'
                && str_starts_with($request->url(), $apiUrl)
                && ($query['phone'] ?? null) === $customer->phone
                && str_contains($text, $portalUrl)
                && str_contains($text, '/portal/auth/')
                && preg_match('/PIN: \*\d{6}\*/', $text) === 1;
        });
    }

    public function test_send_welcome_message_posts_to_whatsapp_bridge(): void
    {
        Http::fake(['*' => Http::response(['status' => 'sent'])]);

        $customer = $this->makeCustomer();
        $portalUrl = $customer->portalUrl();

        $response = $this->actingAs($this->user)
            ->post(route('customers.send-welcome', ['tenant_slug' => $this->tenant->slug, 'customer' => $customer->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) use ($customer, $portalUrl) {
            $apiUrl = config('services.wa_notification.api_url');
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);
            $text = $query['text'] ?? '';

            return $request->method() === 'GET'
                && str_starts_with($request->url(), $apiUrl)
                && ($query['phone'] ?? null) === $customer->phone
                && str_contains($text, 'Selamat datang')
                && str_contains($text, $portalUrl)
                && str_contains($text, '/portal/auth/')
                && preg_match('/PIN: \*\d{6}\*/', $text) === 1;
        });
    }

    public function test_send_portal_link_fails_when_customer_has_no_phone(): void
    {
        Http::fake();

        $customer = $this->makeCustomer(['phone' => null]);

        $response = $this->actingAs($this->user)
            ->post(route('customers.send-portal-link', ['tenant_slug' => $this->tenant->slug, 'customer' => $customer->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_customer_show_page_renders_when_package_is_missing(): void
    {
        $otherTenant = Tenant::factory()->create(['status' => 'active']);
        $otherPackage = Package::factory()->create(['tenant_id' => $otherTenant->id]);

        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'package_id' => $otherPackage->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('customers.show', ['tenant_slug' => $this->tenant->slug, 'customer' => $customer->id]));

        $response->assertOk();
        $response->assertSee('-');
    }
}
