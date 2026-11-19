<?php

namespace App\Http\Controllers;

use App\Models\NasServer;
use App\Models\Tenant;
use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    public function index()
    {
        $tid = tenantId();
        $tenant = Tenant::findOrFail($tid);
        $routers = NasServer::where('tenant_id', $tid)->get();

        // Generate tailored MikroTik bootstrap script
        $tenantSlug = $tenant->slug ?? 'tenant';
        $appName = config('app.name', 'NetSaz');
        $serverHost = request()->getHost();

        $bootstrapScript = <<<MIKROTIK
# ========================================================
# {$appName} Zero-Touch RouterOS Bootstrap Script
# Tenant: {$tenant->name} ({$tenantSlug})
# Generated: auto
# ========================================================

# 1. Enable API Service
/ip service set api disabled=no port=8728
/ip service set api-ssl disabled=yes

# 2. Setup NetSaz API User
/user group add name=netsaz-group policy=api,read,write,test,password
/user add name=netsaz_agent group=netsaz-group password="NETSAZ_SECURE_PASSWORD" comment="NetSaz Cloud Management Agent"

# 3. Setup Isolir Pool & Profile
/ip pool add name=pool-isolir ranges=10.99.0.10-10.99.0.250
/ppp profile add name=profile-isolir local-address=10.99.0.1 remote-address=pool-isolir rate-limit=128k/128k comment="NetSaz Isolir Tagihan"

# 4. Isolir Firewall NAT (Redirect to Walled Garden / Portal Pembayaran)
/ip firewall nat add chain=dstnat src-address=10.99.0.0/24 protocol=tcp dst-port=80 action=redirect to-ports=8080 comment="NetSaz Isolir Redirect HTTP"
/ip firewall nat add chain=dstnat src-address=10.99.0.0/24 protocol=tcp dst-port=443 action=redirect to-ports=8443 comment="NetSaz Isolir Redirect HTTPS"

# 5. Whitelist Portal NetSaz for Isolated Customers
/ip firewall address-list add list=NETSAZ_WHITELIST address={$serverHost} comment="NetSaz Payment Portal"
/ip firewall filter add chain=forward src-address=10.99.0.0/24 dst-address-list=NETSAZ_WHITELIST action=accept comment="Allow Access to NetSaz Portal"

# 6. Allow DNS for Isolated Customers
/ip firewall filter add chain=forward src-address=10.99.0.0/24 protocol=udp dst-port=53 action=accept comment="Allow DNS Queries"
/ip firewall filter add chain=forward src-address=10.99.0.0/24 protocol=tcp dst-port=53 action=accept comment="Allow DNS Queries TCP"

# Done! Your router is ready to connect with NetSaz.
MIKROTIK;

        return view('deployment.index', compact('tenant', 'routers', 'bootstrapScript'));
    }
}
