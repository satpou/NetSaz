<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\RouterAgent;
use App\Models\RouterAgentCommand;
use Illuminate\Http\Request;

class RouterAgentController extends Controller
{
    /**
     * Authenticate an agent by its Bearer token and return agent + tenant.
     */
    protected function authenticateAgent(Request $request): ?RouterAgent
    {
        $header = $request->header('Authorization', '');
        if (! str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $rawToken = substr($header, 7);
        return RouterAgent::findByRawToken($rawToken);
    }

    /**
     * POST /api/agent/connect
     * Register or update agent connection status to 'online'.
     */
    public function connect(Request $request)
    {
        $agent = $this->authenticateAgent($request);

        if (! $agent) {
            return response()->json(['error' => 'Token tidak valid'], 401);
        }

        $agent->update([
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        return response()->json(['status' => 'ok', 'agent_id' => $agent->id]);
    }

    /**
     * GET /api/agent/commands/pending
     * Return pending commands for the authenticated agent.
     */
    public function pendingCommands(Request $request)
    {
        $agent = $this->authenticateAgent($request);

        if (! $agent) {
            return response()->json(['error' => 'Token tidak valid'], 401);
        }

        $commands = RouterAgentCommand::where('router_agent_id', $agent->id)
            ->where('tenant_id', $agent->tenant_id)
            ->where('status', 'pending')
            ->get();

        return response()->json(['commands' => $commands]);
    }

    /**
     * POST /api/agent/commands/{command}/ack
     * Acknowledge a command.
     */
    public function acknowledgeCommand(Request $request, int $commandId)
    {
        $agent = $this->authenticateAgent($request);

        if (! $agent) {
            return response()->json(['error' => 'Token tidak valid'], 401);
        }

        // Only allow acknowledging commands that belong to this agent's tenant
        $command = RouterAgentCommand::where('id', $commandId)
            ->where('tenant_id', $agent->tenant_id)
            ->where('router_agent_id', $agent->id)
            ->first();

        if (! $command) {
            return response()->json(['error' => 'Command not found'], 404);
        }

        $command->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);

        return response()->json(['status' => 'ok']);
    }
}
