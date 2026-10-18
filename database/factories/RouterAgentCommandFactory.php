<?php

namespace Database\Factories;

use App\Models\RouterAgent;
use App\Models\RouterAgentCommand;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouterAgentCommandFactory extends Factory
{
    protected $model = RouterAgentCommand::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'router_agent_id' => RouterAgent::factory(),
            'command_type' => $this->faker->randomElement(['suspend_customer', 'resume_customer', 'create_pppoe']),
            'payload' => ['username' => $this->faker->userName],
            'status' => 'pending',
        ];
    }
}
