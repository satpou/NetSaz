<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\NasServer;
use App\Models\OltDevice;
use App\Models\OltOnu;
use Illuminate\Http\Request;

class NetworkModuleController extends Controller
{
    /**
     * Network Monitoring
     */
    public function monitoring()
    {
        $tid = tenantId();
        $routers = NasServer::where('tenant_id', $tid)->get();
        $customersCount = Customer::where('tenant_id', $tid)->count();
        $activeCustomersCount = Customer::where('tenant_id', $tid)->where('status', 'active')->count();
        $isolatedCustomersCount = Customer::where('tenant_id', $tid)->where('status', 'isolated')->count();

        return view('network.monitoring', compact('routers', 'customersCount', 'activeCustomersCount', 'isolatedCustomersCount'));
    }

    /**
     * IP Management & Subnets
     */
    public function ipManagement(Request $request)
    {
        $tid = tenantId();
        $customersWithIp = Customer::where('tenant_id', $tid)
            ->whereNotNull('ip_address')
            ->select('id', 'name', 'customer_code', 'pppoe_username', 'ip_address', 'status', 'created_at')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('pppoe_username', 'like', "%{$search}%");
                });
            })
            ->paginate(15);

        // Predefined or configured IP Pools
        $pools = [
            ['name' => 'pool-pppoe-main', 'subnet' => '10.10.0.0/20', 'range' => '10.10.1.2 - 10.10.15.254', 'total' => 3824, 'used' => max($customersWithIp->total(), 12), 'type' => 'Dynamic PPPoE'],
            ['name' => 'pool-isolir-tagihan', 'subnet' => '10.99.0.0/24', 'range' => '10.99.0.10 - 10.99.0.250', 'total' => 240, 'used' => Customer::where('tenant_id', $tid)->where('status', 'isolated')->count(), 'type' => 'Isolir Walled Garden'],
            ['name' => 'pool-hotspot-voucher', 'subnet' => '192.168.88.0/24', 'range' => '192.168.88.10 - 192.168.88.254', 'total' => 244, 'used' => 18, 'type' => 'Hotspot Captive'],
        ];

        return view('network.ip-management', compact('customersWithIp', 'pools'));
    }

    /**
     * Network Logs & Audit Trail
     */
    public function logs(Request $request)
    {
        $tid = tenantId();
        $logs = AuditLog::where('tenant_id', $tid)
            ->when($request->action, fn($q, $act) => $q->where('action', $act))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('action', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%")
                        ->orWhere('model_type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20);

        return view('network.logs', compact('logs'));
    }

    /**
     * Bandwidth Utilization & Queues
     */
    public function bandwidth()
    {
        $tid = tenantId();
        $routers = NasServer::where('tenant_id', $tid)->get();
        $topCustomers = Customer::where('tenant_id', $tid)
            ->with('package')
            ->where('status', 'active')
            ->take(10)
            ->get();

        return view('network.bandwidth', compact('routers', 'topCustomers'));
    }

    /**
     * Fault Detection & Health Diagnostics
     */
    public function faultDetection()
    {
        $tid = tenantId();
        $routers = NasServer::where('tenant_id', $tid)->get();
        $offlineRouters = NasServer::where('tenant_id', $tid)->where('status', 'offline')->get();
        $onus = OltOnu::where('tenant_id', $tid)->with('olt')->get();

        return view('network.fault-detection', compact('routers', 'offlineRouters', 'onus'));
    }
}
