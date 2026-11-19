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

@section('title', 'IP Management')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Network</span> &gt; <b>IP Management</b>
</div>

<x-page-header title="IP Management &amp; Pool Subnet" subtitle="Alokasi IP Address pelanggan PPPoE, pool isolir walled garden, dan monitoring subnet jaringan">
    <form action="{{ route('network.ip', $ts ? ['tenant_slug' => $ts] : []) }}" method="GET" style="display:flex;gap:8px">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari IP / Nama / Username..." class="form-input" style="padding:8px 12px;font-size:13px;width:240px">
        <button type="submit" class="btn btn-outline" style="padding:8px 14px">Cari</button>
    </form>
</x-page-header>

<!-- IP Pools Cards -->
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;margin-bottom:24px">
    @foreach($pools as $pool)
        <div class="card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
            <div style="display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:14px;font-weight:700;color:var(--ink)">{{ $pool['name'] }}</span>
                <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>{{ $pool['type'] }}</span>
            </div>
            <div style="font-family:'JetBrains Mono';font-size:13px;color:var(--primary);margin-top:8px">
                {{ $pool['subnet'] }}
            </div>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">
                Range: {{ $pool['range'] }}
            </div>
            <div style="margin-top:14px">
                <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--ink-soft);margin-bottom:4px">
                    <span>Terpakai: {{ $pool['used'] }} IP</span>
                    <span>Kapasitas: {{ $pool['total'] }} IP</span>
                </div>
                <div style="height:6px;background:var(--bg-alt);border-radius:100px;overflow:hidden">
                    @php $pct = min(100, round(($pool['used'] / max(1, $pool['total'])) * 100)); @endphp
                    <div style="width:{{ $pct }}%;height:100%;background:var(--primary);border-radius:100px"></div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Customer IP Allocation Table -->
<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="padding:18px 24px;border-bottom:1px solid var(--line)">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0">Alokasi IP Pelanggan</h3>
        <p style="font-size:13px;color:var(--ink-soft);margin:3px 0 0">Daftar IP address statis dan remote address PPPoE yang dialokasikan ke pelanggan</p>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Pelanggan</th>
                    <th style="padding:14px 16px">Username PPPoE</th>
                    <th style="padding:14px 16px">IP Address Remote</th>
                    <th style="padding:14px 16px">Status Layanan</th>
                    <th style="padding:14px 20px;text-align:right">Tautan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customersWithIp as $cust)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px">
                            <div style="font-weight:600;color:var(--ink)">{{ $cust->name }}</div>
                            <div style="font-size:12px;color:var(--ink-faint)">{{ $cust->customer_code }}</div>
                        </td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:13px;color:var(--ink)">
                            {{ $cust->pppoe_username ?? '-' }}
                        </td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:13.5px;font-weight:600;color:var(--primary)">
                            {{ $cust->ip_address }}
                        </td>
                        <td style="padding:14px 16px">
                            @if($cust->status === 'active')
                                <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Aktif</span>
                            @elseif($cust->status === 'isolated')
                                <span class="status-pill danger" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Isolir</span>
                            @else
                                <span class="status-pill" style="padding:2px 8px;font-size:11px">{{ $cust->status }}</span>
                            @endif
                        </td>
                        <td style="padding:14px 20px;text-align:right">
                            <a href="{{ route('customers.show', array_merge($ts ? ['tenant_slug' => $ts] : [], ['customer' => $cust->id])) }}" class="btn-outline" style="padding:4px 10px;font-size:12px;text-decoration:none">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada pelanggan dengan alokasi IP Address statis.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customersWithIp->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--line)">
            {{ $customersWithIp->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
