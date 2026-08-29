<?php

namespace Tests\Feature;

use App\Models\RouterAgent;
use App\Models\RouterAgentCommand;
use App\Models\Nas;
use App\Models\Tenant;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class RouterAgentTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function agentWithToken(): array
    {
        $rawToken = RouterAgent::generateToken();
        $tenant = Tenant::factory()->create();
        $nas = Nas::factory()->create(['tenant_id' => $tenant->id]);
        $agent = RouterAgent::factory()->create([
            'tenant_id' => $tenant->id,
            'nas_id' => $nas->id,
            'agent_token' => RouterAgent::hashToken($rawToken),
        ]);
        return [$agent, $rawToken, $tenant, $nas];
    }

    public function test_agent_with_invalid_token_is_rejected()
    {
        $response = $this->postJson('/api/agent/connect', [], [
            'Authorization' => 'Bearer invalid-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['error' => 'Token tidak valid']);
    }

    public function test_agent_can_register_connection()
    {
        [$agent, $rawToken] = $this->agentWithToken();

        $response = $this->postJson('/api/agent/connect', [], [
            'Authorization' => 'Bearer ' . $rawToken,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('online', $agent->fresh()->status);
    }

    public function test_agent_can_request_pending_commands()
    {
        [$agent, $rawToken] = $this->agentWithToken();

        $command = RouterAgentCommand::factory()->create([
            'tenant_id' => $agent->tenant_id,
            'router_agent_id' => $agent->id,
            'command_type' => 'suspend_customer',
            'payload' => ['username' => 'user1'],
        ]);

        $response = $this->getJson('/api/agent/commands/pending', [
            'Authorization' => 'Bearer ' . $rawToken,
        ]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'commands');
    }

    public function test_command_for_tenant_a_cannot_be_executed_by_agent_from_tenant_b()
    {
        [$agentA, $tokenA] = $this->agentWithToken();
        [$agentB, $tokenB] = $this->agentWithToken();

        $command = RouterAgentCommand::factory()->create([
            'tenant_id' => $agentA->tenant_id,
            'router_agent_id' => $agentA->id,
            'command_type' => 'suspend_customer',
        ]);

        $response = $this->postJson("/api/agent/commands/{$command->id}/ack", [], [
            'Authorization' => 'Bearer ' . $tokenB,
        ]);

        $response->assertStatus(404);
    }
}