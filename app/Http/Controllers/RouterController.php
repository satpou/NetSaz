<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MikrotikAccount;
use App\Models\NasServer;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RouterController extends Controller
{
    public function index()
    {
        $tid = tenantId();
        $routers = NasServer::where('tenant_id', $tid)->latest()->get();

        $activeSecretsCount = MikrotikAccount::where('tenant_id', $tid)->where('status', 'active')->count();

        return view('routers.index', compact('routers', 'activeSecretsCount'));
    }

    public function create()
    {
        return view('routers.create');
    }

    public function store(Request $request)
    {
        $tid = tenantId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'api_port' => 'required|integer|min:1|max:65535',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'connection_type' => 'required|in:direct,secure_tunnel',
        ]);

        $validated['tenant_id'] = $tid;
        $validated['status'] = 'offline';

        $router = NasServer::create($validated);

        return redirect()->route('routers.index', request()->route('tenant_slug') ? ['tenant_slug' => request()->route('tenant_slug')] : [])
            ->with('success', "Router MikroTik '{$router->name}' berhasil ditambahkan.");
    }

    public function edit(NasServer $router)
    {
        abort_unless($router->tenant_id === tenantId(), 403);
        return view('routers.edit', compact('router'));
    }

    public function update(Request $request, NasServer $router)
    {
        abort_unless($router->tenant_id === tenantId(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'api_port' => 'required|integer|min:1|max:65535',
            'api_username' => 'nullable|string|max:255',
            'api_password' => 'nullable|string|max:255',
            'connection_type' => 'required|in:direct,secure_tunnel',
        ]);

        if (empty($validated['api_password'])) {
            unset($validated['api_password']);
        }

        $router->update($validated);

        return redirect()->route('routers.index', request()->route('tenant_slug') ? ['tenant_slug' => request()->route('tenant_slug')] : [])
            ->with('success', "Router MikroTik '{$router->name}' berhasil diperbarui.");
    }

    public function destroy(NasServer $router)
    {
        abort_unless($router->tenant_id === tenantId(), 403);
        $name = $router->name;
        $router->delete();

        return redirect()->route('routers.index', request()->route('tenant_slug') ? ['tenant_slug' => request()->route('tenant_slug')] : [])
            ->with('success', "Router MikroTik '{$name}' berhasil dihapus.");
    }

    public function testConnection(NasServer $router)
    {
        abort_unless($router->tenant_id === tenantId(), 403);

        $startTime = microtime(true);
        $isOnline = false;
        $statusMsg = '';

        try {
            // Attempt socket connect or HTTP REST call
            $port = $router->api_port ?: 8728;
            $connection = @fsockopen($router->host, $port, $errno, $errstr, 3);

            if (is_resource($connection)) {
                fclose($connection);
                $latency = round((microtime(true) - $startTime) * 1000);
                $isOnline = true;
                $statusMsg = "Berhasil terhubung ke MikroTik {$router->host}:{$port} (Latency: {$latency}ms).";
            } else {
                $statusMsg = "Gagal terhubung ke {$router->host}:{$port} ({$errstr}). Pastikan IP dan port dapat diakses.";
            }
        } catch (\Exception $e) {
            $statusMsg = "Koneksi gagal: " . $e->getMessage();
        }

        $router->update([
            'status' => $isOnline ? 'online' : 'offline',
        ]);

        if ($isOnline) {
            return back()->with('success', $statusMsg);
        }

        return back()->with('warning', $statusMsg);
    }

    public function syncSecrets(NasServer $router)
    {
        abort_unless($router->tenant_id === tenantId(), 403);

        $tid = tenantId();
        $accounts = MikrotikAccount::where('tenant_id', $tid)
            ->whereNotNull('pppoe_secret')
            ->get();

        $service = new MikrotikService($router);
        $synced = 0;
        $errors = 0;

        foreach ($accounts as $acc) {
            try {
                $ok = $service->createPppoeSecret(
                    $acc->pppoe_secret,
                    $acc->pppoe_password ?: '123456',
                    $acc->pppoe_service ?: 'pppoe'
                );
                if ($ok) {
                    $synced++;
                } else {
                    $errors++;
                }
            } catch (\Exception $e) {
                $errors++;
            }
        }

        return back()->with('success', "Sinkronisasi selesai! {$synced} PPPoE Secret berhasil disinkronkan ke router {$router->name}.");
    }
}
