<?php
namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Customer $customer;
    protected Tenant $otherTenant;
    protected Customer $otherCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed subscription plans
        \App\Models\SubscriptionPlan::create([
            'code' => 'launch',
            'name' => 'Launch',
            'price' => 99000,
            'billing_period' => 'monthly',
            'max_active_customers' => 150,
            'max_routers' => 1,
            'max_staff' => 1,
            'features' => ['whatsapp_broadcast'],
            'sort_order' => 1,
        ]);

        $this->tenant = Tenant::factory()->create(['status' => 'active']);
        $package = Package::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '1234567890',
            'address' => '123 Main St',
            'package_id' => $package->id,
            'status' => 'active',
            'join_date' => Carbon::now(),
        ]);

        $this->otherTenant = Tenant::factory()->create(['status' => 'active']);
        $otherPackage = Package::factory()->create(['tenant_id' => $this->otherTenant->id]);
        $this->otherCustomer = Customer::factory()->create([
            'tenant_id' => $this->otherTenant->id,
            'name' => 'Other Customer',
            'email' => 'other@example.com',
            'phone' => '0987654321',
            'address' => '456 Main St',
            'package_id' => $otherPackage->id,
            'status' => 'active',
            'join_date' => Carbon::now(),
        ]);

        // Create a subscription for testing
        $launchPlan = \App\Models\SubscriptionPlan::where('code', 'launch')->first();
        \App\Models\TenantSubscription::create([
            'tenant_id' => $this->tenant->id,
            'subscription_plan_id' => $launchPlan->id,
            'status' => 'active',
            'started_at' => Carbon::now(),
            'current_period_start' => Carbon::now(),
            'current_period_end' => Carbon::now()->addMonth(),
        ]);

        URL::defaults(['tenant_slug' => $this->tenant->slug]);
    }

    protected function portalServer(): string
    {
        return $this->tenant->slug . '.' . config('app.main_domain');
    }

    public function test_customer_can_login_with_pin(): void
    {
        $this->customer->forceFill(['portal_pin' => \Illuminate\Support\Facades\Hash::make('123456')])->save();

        $response = $this->withServerVariables(['HTTP_HOST' => $this->portalServer()])
            ->post(route('customer.auth.authenticate'), [
                'phone_or_email' => $this->customer->phone,
                'pin' => '123456',
            ]);

        $response->assertRedirect(route('customer.portal.profile'));
        $this->assertAuthenticatedAs($this->customer, 'customer');
    }

    public function test_customer_cannot_login_with_wrong_pin(): void
    {
        $this->customer->forceFill(['portal_pin' => \Illuminate\Support\Facades\Hash::make('123456')])->save();

        $response = $this->withServerVariables(['HTTP_HOST' => $this->portalServer()])
            ->post(route('customer.auth.authenticate'), [
                'phone_or_email' => $this->customer->phone,
                'pin' => '000000',
            ]);

        $response->assertSessionHasErrors('pin');
        $this->assertGuest('customer');
    }

    public function test_customer_from_other_tenant_cannot_login(): void
    {
        $this->otherCustomer->forceFill(['portal_pin' => \Illuminate\Support\Facades\Hash::make('123456')])->save();

        $response = $this->withServerVariables(['HTTP_HOST' => $this->portalServer()])
            ->post(route('customer.auth.authenticate'), [
                'phone_or_email' => $this->otherCustomer->phone,
                'pin' => '123456',
            ]);

        $response->assertSessionHasErrors('phone_or_email');
        $this->assertGuest('customer');
    }

    public function test_magic_link_logs_in_customer(): void
    {
        $token = $this->customer->generateMagicLoginToken();

        $response = $this->withServerVariables(['HTTP_HOST' => $this->portalServer()])
            ->get(route('customer.auth.auth', ['token' => $token]));

        $response->assertRedirect(route('customer.portal.profile'));
        $this->assertAuthenticatedAs($this->customer, 'customer');

        // Token is one-time use
        $this->customer->refresh();
        $this->assertNull($this->customer->portal_login_token);
    }

    public function test_magic_link_with_invalid_token_redirects_to_login(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => $this->portalServer()])
            ->get(route('customer.auth.auth', ['token' => 'invalid-token']));

        $response->assertRedirect(route('customer.auth.login'));
        $this->assertGuest('customer');
    }

    public function test_customer_cannot_access_portal_without_login(): void
    {
        $response = $this->withServerVariables(['HTTP_HOST' => $this->portalServer()])
            ->get(route('customer.portal.profile'));

        $response->assertStatus(302);
        $this->assertGuest('customer');
    }
}
