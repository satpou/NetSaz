<?php

namespace Tests\Feature;

use App\Models\SubscriptionPlan;
use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private array $validFeatureKeys = [
        'whatsapp_broadcast',
        'export_data',
        'secure_tunnel',
        'advanced_reporting',
        'api_access',
        'olt_management',
        'radius_hub',
        'genieacs_integration',
        'custom_domain',
        'white_label',
    ];

    public function test_create_default_plans_command_uses_valid_feature_keys(): void
    {
        $this->seed(SubscriptionPlanSeeder::class);

        $this->artisan('plans:create-default')->assertSuccessful();

        $plans = SubscriptionPlan::all();

        $this->assertGreaterThan(0, $plans->count());

        $codes = ['launch', 'growth', 'pro', 'biz', 'ultra', 'enterprise'];
        foreach ($codes as $code) {
            $this->assertDatabaseHas('subscription_plans', ['code' => $code]);
        }

        foreach ($plans as $plan) {
            foreach ($plan->features as $feature) {
                $this->assertContains(
                    $feature,
                    $this->validFeatureKeys,
                    "Plan {$plan->code} memakai feature key tidak dikenal: {$feature}"
                );
            }
        }
    }
}
