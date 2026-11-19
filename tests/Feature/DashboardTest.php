<?php

namespace Tests\Feature;

use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_new_isp_tenant_can_register_from_main_domain(): void
    {
        $this->seed(SubscriptionPlanSeeder::class);

        $response = $this->post(route('tenant.register.store'), [
            'company_name' => 'NetCity Nusantara',
            'slug' => 'netcity-nusantara',
            'pic_name' => 'Budi Santoso',
            'city' => 'Tangerang',
            'whatsapp_number' => '081234567890',
            'email' => 'owner@netcity.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'customers' => '50-100',
            'package' => 'launch',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'name' => 'NetCity Nusantara',
            'slug' => 'netcity-nusantara',
            'email' => 'owner@netcity.id',
            'status' => 'pending_payment',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'owner@netcity.id',
            'role' => 'super_admin',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('tenant_subscriptions', [
            'status' => 'pending_payment',
        ]);
    }

    public function test_enterprise_registration_shows_consultation_panel_instead_of_manual_transfer(): void
    {
        $this->seed(SubscriptionPlanSeeder::class);

        $response = $this->post(route('tenant.register.store'), [
            'company_name' => 'Prima Digital Nusantara',
            'slug' => 'prima-digital-nusantara',
            'pic_name' => 'Primanet',
            'city' => 'Jakarta',
            'whatsapp_number' => '081234567899',
            'email' => 'enterprise@primanet.id',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'package' => 'enterprise',
        ]);

        $response->assertRedirect(route('register.payment', ['slug' => 'prima-digital-nusantara']));

        $payResponse = $this->get(route('register.payment', ['slug' => 'prima-digital-nusantara']));
        $payResponse->assertOk();
        $payResponse->assertSee('Permintaan Layanan Enterprise');
        $payResponse->assertSee('Menunggu Penawaran Kustom');
        $payResponse->assertSee('Kustom / Tak Terbatas');
        $payResponse->assertSee('Sesuai Penawaran Resmi');
        $payResponse->assertSee('Hubungi Enterprise Consultant via WhatsApp');
        $payResponse->assertDontSee('Silakan transfer tepat sejumlah');
        $payResponse->assertDontSee('Unggah Bukti Transfer');
    }
}
