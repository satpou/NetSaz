<?php

namespace Database\Factories;

use App\Models\Nas;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class NasFactory extends Factory
{
    protected $model = Nas::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->words(2, true),
            'shortname' => $this->faker->word,
            'host' => $this->faker->localIpv4,
            'status' => 'offline',
            'connection_type' => 'direct',
        ];
    }
}
