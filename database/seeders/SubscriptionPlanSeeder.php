<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'launch',
                'name' => 'Launch',
                'price' => 99000,
                'billing_period' => 'monthly',
                'max_active_customers' => 150,
                'max_routers' => 1,
                'max_staff' => 2,
                'features' => ['whatsapp_broadcast', 'export_data'],
                'sort_order' => 1,
            ],
            [
                'code' => 'growth',
                'name' => 'Growth',
                'price' => 199000,
                'billing_period' => 'monthly',
                'max_active_customers' => 300,
                'max_routers' => 3,
                'max_staff' => 5,
                'features' => ['whatsapp_broadcast', 'export_data', 'advanced_reporting'],
                'sort_order' => 2,
            ],
            [
                'code' => 'pro',
                'name' => 'Pro',
                'price' => 299000,
                'billing_period' => 'monthly',
                'max_active_customers' => 500,
                'max_routers' => 10,
                'max_staff' => 10,
                'features' => ['whatsapp_broadcast', 'export_data', 'advanced_reporting', 'radius_hub', 'olt_management'],
                'sort_order' => 3,
            ],
            [
                'code' => 'biz',
                'name' => 'Biz',
                'price' => 499000,
                'billing_period' => 'monthly',
                'max_active_customers' => 1000,
                'max_routers' => 20,
                'max_staff' => 20,
                'features' => ['whatsapp_broadcast', 'export_data', 'advanced_reporting', 'radius_hub', 'olt_management', 'api_access', 'secure_tunnel'],
                'sort_order' => 4,
            ],
            [
                'code' => 'ultra',
                'name' => 'Ultra',
                'price' => 799000,
                'billing_period' => 'monthly',
                'max_active_customers' => 2000,
                'max_routers' => 50,
                'max_staff' => 50,
                'features' => ['whatsapp_broadcast', 'export_data', 'advanced_reporting', 'radius_hub', 'olt_management', 'api_access', 'secure_tunnel', 'genieacs_integration'],
                'sort_order' => 5,
            ],
            [
                'code' => 'enterprise',
                'name' => 'Enterprise',
                'price' => 0,
                'billing_period' => 'monthly',
                'max_active_customers' => 0,
                'max_routers' => 0,
                'max_staff' => 0,
                'features' => ['whatsapp_broadcast', 'export_data', 'advanced_reporting', 'radius_hub', 'olt_management', 'api_access', 'secure_tunnel', 'genieacs_integration', 'custom_domain', 'white_label'],
                'sort_order' => 6,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['code' => $plan['code']],
                $plan
            );
        }
    }
}
