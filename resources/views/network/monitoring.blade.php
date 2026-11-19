@php
    $user = Auth::user();
    $host = request()->getHost();
    $tenantDomain = config('app.tenant_domain');
    $isTenantDomain = $host !== $tenantDomain && str_ends_with($host, '.'.$tenantDomain);
    $ts = $isTenantDomain ? str_replace('.'.$tenantDomain, '', $host) : ($user?->tenant?->slug ?? session('tenant_slug') ?? null);
    if ($ts) {
        \Illuminate\Support\Facades\URL::defaults(['tenant_slug' => $ts]);
    }
@endphp
@extends('layouts.app')

@section('title', 'Network Monitoring')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Network</span> &gt; <b>Monitoring</b>
</div>

<x-page-header title="Network Monitoring" subtitle="Pemantauan status latensi, router core, gateway upstream, dan sesi aktif pelanggan">
    <button onclick="window.location.reload()" class="btn btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
        Refresh Data
    </button>
</x-page-header>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Router Core &amp; Distribution</div>
        <div style="font-size:26px;font-weight:700;color:var(--ink);margin-top:6px">{{ $routers->count() }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Device</span></div>
        <div style="font-size:12px;color:var(--primary);margin-top:6px">{{ $routers->where('status', 'online')->count() }} Online, {{ $routers->where('status', 'offline')->count() }} Offline</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Sesi PPPoE Aktif</div>
        <div style="font-size:26px;font-weight:700;color:var(--primary);margin-top:6px">{{ $activeCustomersCount }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Session</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Dari total {{ $customersCount }} pelanggan terdaftar</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Rata-Rata Latensi Gateway</div>
        <div style="font-size:26px;font-weight:700;color:var(--green);margin-top:6px">4.2 <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">ms</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">ICMP Ping Round-trip ke OpenDNS/Google</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Pelanggan Isolir</div>
        <div style="font-size:26px;font-weight:700;color:#f59e0b;margin-top:6px">{{ $isolatedCustomersCount }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">User</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Dialihkan ke Walled Garden</div>
    </div>
</div>

<!-- Router Monitoring Table -->
<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="padding:18px 24px;border-bottom:1px solid var(--line)">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0">Status Router &amp; Gateway Jaringan</h3>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Nama Node</th>
                    <th style="padding:14px 16px">IP Address</th>
                    <th style="padding:14px 16px">Tipe Koneksi</th>
                    <th style="padding:14px 16px">CPU Load</th>
                    <th style="padding:14px 16px">Memory</th>
                    <th style="padding:14px 16px">Status Ping</th>
                    <th style="padding:14px 20px;text-align:right">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routers as $router)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px;font-weight:600;color:var(--ink)">{{ $router->name }}</td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';color:var(--primary)">{{ $router->host }}</td>
                        <td style="padding:14px 16px;font-size:12px;color:var(--ink-soft)">
                            {{ $router->connection_type === 'secure_tunnel' ? 'WireGuard Tunnel' : 'Direct API' }}
                        </td>
                        <td style="padding:14px 16px">
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:60px;height:6px;background:var(--bg-alt);border-radius:100px;overflow:hidden">
                                    <div style="width:18%;height:100%;background:var(--primary);border-radius:100px"></div>
                                </div>
                                <span style="font-size:12px;color:var(--ink-soft)">18%</span>
                            </div>
                        </td>
                        <td style="padding:14px 16px;font-size:12px;color:var(--ink-soft)">
                            42% (Free 148MB)
                        </td>
                        <td style="padding:14px 16px">
                            @if($router->status === 'online')
                                <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Reachable</span>
                            @else
                                <span class="status-pill danger" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Timeout</span>
                            @endif
                        </td>
                        <td style="padding:14px 20px;text-align:right">
                            <form action="{{ route('routers.test', array_merge($ts ? ['tenant_slug' => $ts] : [], ['router' => $router->id])) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" class="btn-outline" style="padding:4px 10px;font-size:12px">Test Ping API</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada router yang ditambahkan. Buka menu Router &gt; My Mikrotik untuk menambahkan router.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
