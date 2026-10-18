<?php

namespace Database\Factories;

use App\Models\Nas;
use App\Models\RouterAgent;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouterAgentFactory extends Factory
{
    protected $model = RouterAgent::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'nas_id' => Nas::factory(),
            'agent_token' => RouterAgent::hashToken(RouterAgent::generateToken()),
            'status' => 'offline',
        ];
    }
}
