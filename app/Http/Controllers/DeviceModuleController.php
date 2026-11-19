<?php

namespace App\Http\Controllers;

use App\Models\GenieacsConfig;
use App\Models\NasServer;
use App\Models\OltDevice;
use App\Models\OltOnu;
use App\Models\RadiusHub;
use App\Models\RadiusHubMember;
use Illuminate\Http\Request;

class DeviceModuleController extends Controller
{
    /**
     * FastRADIUS Hub Dashboard & NAS Management
     */
    public function radius()
    {
        $tid = tenantId();
        $hub = RadiusHub::where('tenant_id', $tid)->with('members.nasServer')->first();
        $nasServers = NasServer::where('tenant_id', $tid)->get();

        return view('devices.radius', compact('hub', 'nasServers'));
    }

    public function radiusStore(Request $request)
    {
        $tid = tenantId();
        $validated = $request->validate([
            'db_name' => 'required|string|max:100',
            'server_host' => 'required|string|max:255',
            'radius_port' => 'nullable|integer|min:1|max:65535',
            'radius_secret' => 'nullable|string|max:100',
        ]);

        $hub = RadiusHub::updateOrCreate(
            ['tenant_id' => $tid],
            [
                'db_name' => $validated['db_name'],
                'server_host' => $validated['server_host'],
                'radius_port' => $validated['radius_port'] ?: 1812,
                'radius_secret' => $validated['radius_secret'] ?: bin2hex(random_bytes(12)),
                'status' => 'active',
                'provisioned_at' => now(),
            ]
        );

        return back()->with('success', "FastRADIUS Hub '{$hub->db_name}' berhasil dikonfigurasi.");
    }

    public function radiusMemberAdd(Request $request)
    {
        $tid = tenantId();
        $validated = $request->validate([
            'nas_server_id' => 'required|exists:nas_servers,id',
        ]);

        $hub = RadiusHub::where('tenant_id', $tid)->firstOrFail();

        RadiusHubMember::firstOrCreate([
            'radius_hub_id' => $hub->id,
            'nas_server_id' => $validated['nas_server_id'],
        ], [
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Router NAS berhasil dihubungkan ke FastRADIUS Hub.');
    }

    public function radiusMemberRemove(RadiusHubMember $member)
    {
        $tid = tenantId();
        $hub = RadiusHub::where('tenant_id', $tid)->firstOrFail();

        abort_unless($member->radius_hub_id === $hub->id, 403);
        $member->delete();

        return back()->with('success', 'Router NAS dilepas dari FastRADIUS Hub.');
    }

    /**
     * OLT (Optical Line Terminal) & ONU Manager
     */
    public function olt()
    {
        $tid = tenantId();
        $devices = OltDevice::where('tenant_id', $tid)->with('onus')->latest()->get();
        $totalOnus = OltOnu::where('tenant_id', $tid)->count();
        $activeOnus = OltOnu::where('tenant_id', $tid)->where('status', 'active')->count();

        return view('devices.olt', compact('devices', 'totalOnus', 'activeOnus'));
    }

    public function oltStore(Request $request)
    {
        $tid = tenantId();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'brand' => 'required|string|in:fiberhome,vsol,huawei,zte',
            'management_host' => 'required|string|max:255',
            'username' => 'nullable|string|max:100',
            'password' => 'nullable|string|max:100',
            'ssh_port' => 'nullable|integer|min:1|max:65535',
        ]);

        $validated['tenant_id'] = $tid;
        $validated['ssh_port'] = $validated['ssh_port'] ?: 22;
        $validated['status'] = 'online';

        OltDevice::create($validated);

        return back()->with('success', "Perangkat OLT '{$validated['name']}' berhasil ditambahkan.");
    }

    public function oltDestroy(OltDevice $device)
    {
        abort_unless($device->tenant_id === tenantId(), 403);
        $device->delete();

        return back()->with('success', 'Perangkat OLT berhasil dihapus.');
    }

    public function onuStore(Request $request, OltDevice $device)
    {
        abort_unless($device->tenant_id === tenantId(), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'serial' => 'required|string|max:100',
            'pon_id' => 'required|string|max:50',
            'onu_id' => 'required|integer|min:1|max:128',
            'vlan' => 'nullable|integer|min:1|max:4094',
        ]);

        $validated['tenant_id'] = tenantId();
        $validated['olt_id'] = $device->id;
        $validated['status'] = 'active';
        $validated['provisioned_at'] = now();

        OltOnu::create($validated);
        $device->increment('onu_count');

        return back()->with('success', "ONU / ONT '{$validated['name']}' berhasil didaftarkan ke OLT.");
    }

    /**
     * GenieACS (TR-069 Auto Configuration Server)
     */
    public function genieacs()
    {
        $tid = tenantId();
        $config = GenieacsConfig::firstOrCreate(
            ['tenant_id' => $tid],
            [
                'enabled' => false,
                'host' => '127.0.0.1',
                'port' => 7557,
                'nbi_base_url' => 'http://127.0.0.1:7557',
            ]
        );

        return view('devices.genieacs', compact('config'));
    }

    public function genieacsUpdate(Request $request)
    {
        $tid = tenantId();
        $config = GenieacsConfig::where('tenant_id', $tid)->firstOrFail();

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'nbi_base_url' => 'nullable|url|max:255',
        ]);

        $config->update([
            'enabled' => $request->boolean('enabled'),
            'host' => $validated['host'],
            'port' => $validated['port'],
            'nbi_base_url' => $validated['nbi_base_url'] ?: "http://{$validated['host']}:{$validated['port']}",
        ]);

        return back()->with('success', 'Konfigurasi GenieACS TR-069 berhasil disimpan.');
    }
}
