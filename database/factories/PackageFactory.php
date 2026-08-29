<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->randomElement(['Basic', 'Premium', 'Ultimate']) . ' ' . $this->faker->numberBetween(1, 100) . ' Mbps',
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomElement([50000, 75000, 100000, 150000, 200000]),
            'speed' => $this->faker->randomElement(['5Mbps', '10Mbps', '20Mbps', '50Mbps', '100Mbps']),
        ];
    }
}