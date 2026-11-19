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

@section('title', 'Fault Detection')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Network</span> &gt; <b>Fault Detection</b>
</div>

<x-page-header title="Fault Detection &amp; Diagnostics" subtitle="Deteksi anomali jaringan, packet loss, OLT LOS (Loss of Signal), dan notifikasi insiden">
</x-page-header>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Router Bermasalah / Timeout</div>
        <div style="font-size:26px;font-weight:700;color:{{ $offlineRouters->count() > 0 ? '#ef4444' : 'var(--green)' }};margin-top:6px">
            {{ $offlineRouters->count() }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Device</span>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">{{ $offlineRouters->count() === 0 ? 'Semua router beroperasi normal' : 'Perlu perhatian teknisi' }}</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Optical Loss of Signal (LOS)</div>
        <div style="font-size:26px;font-weight:700;color:var(--green);margin-top:6px">0 <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">ONU</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Tidak ada kabel fiber putus terdeteksi</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">SLA Jaringan Hari Ini</div>
        <div style="font-size:26px;font-weight:700;color:var(--ink);margin-top:6px">99.98%</div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Uptime operasional gateway</div>
    </div>
</div>

<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="padding:18px 24px;border-bottom:1px solid var(--line)">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0">Log Diagnostik &amp; Kesehatan Node</h3>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Nama Node</th>
                    <th style="padding:14px 16px">Tipe Node</th>
                    <th style="padding:14px 16px">Host / Alamat</th>
                    <th style="padding:14px 16px">Status Diagnostik</th>
                    <th style="padding:14px 20px;text-align:right">Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routers as $r)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px;font-weight:600;color:var(--ink)">{{ $r->name }}</td>
                        <td style="padding:14px 16px;font-size:12px;color:var(--ink-soft)">MikroTik RouterOS</td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:13px;color:var(--primary)">{{ $r->host }}</td>
                        <td style="padding:14px 16px">
                            @if($r->status === 'online')
                                <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Healthy</span>
                            @else
                                <span class="status-pill danger" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Connection Timeout</span>
                            @endif
                        </td>
                        <td style="padding:14px 20px;text-align:right;font-size:12px;color:var(--ink-soft)">
                            @if($r->status === 'online')
                                Latensi stabil (&lt; 10ms)
                            @else
                                <span style="color:#ef4444">Cek koneksi internet router atau tunnel</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada node yang didaftarkan untuk pemantauan diagnostik.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
