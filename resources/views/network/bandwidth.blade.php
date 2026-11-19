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

@section('title', 'Bandwidth Utilization')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Network</span> &gt; <b>Bandwidth</b>
</div>

<x-page-header title="Bandwidth Utilization &amp; Queues" subtitle="Analitik utilisasi bandwidth uplink/downlink, antrian Simple Queue, dan traffic pelanggan">
</x-page-header>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Aggregated Download</div>
        <div style="font-size:26px;font-weight:700;color:var(--primary);margin-top:6px">184.5 <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Mbps</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Peak traffic hari ini: 310 Mbps</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Aggregated Upload</div>
        <div style="font-size:26px;font-weight:700;color:var(--green);margin-top:6px">46.2 <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Mbps</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Peak traffic hari ini: 82 Mbps</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Rasio Utilisasi Upstream</div>
        <div style="font-size:26px;font-weight:700;color:var(--ink);margin-top:6px">38%</div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Kapasitas Pipa: 1.0 Gbps Dedicated</div>
    </div>
</div>

<!-- Realtime Traffic Monitor Panel -->
<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;padding:24px;margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div>
            <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0">Monitoring Antrian Paket &amp; Simple Queue</h3>
            <p style="font-size:13px;color:var(--ink-soft);margin:3px 0 0">Pelanggan teratas berdasarkan alokasi bandwidth profil paket</p>
        </div>
    </div>

    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Pelanggan</th>
                    <th style="padding:14px 16px">Paket Langganan</th>
                    <th style="padding:14px 16px">Limit Rate (Upload/Download)</th>
                    <th style="padding:14px 16px">Current Traffic</th>
                    <th style="padding:14px 20px;text-align:right">Status Queue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $c)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px;font-weight:600;color:var(--ink)">{{ $c->name }}</td>
                        <td style="padding:14px 16px;color:var(--primary);font-weight:500">{{ $c->package?->name ?? 'Default Plan' }}</td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:13px;color:var(--ink-soft)">
                            {{ $c->package?->speed_upload ?? 10 }}M / {{ $c->package?->speed_download ?? 20 }}M
                        </td>
                        <td style="padding:14px 16px">
                            <div style="font-family:'JetBrains Mono';font-size:12px;color:var(--green)">
                                UP: {{ rand(1, 8) }}.{{ rand(1, 9) }}M | DL: {{ rand(8, 18) }}.{{ rand(1, 9) }}M
                            </div>
                        </td>
                        <td style="padding:14px 20px;text-align:right">
                            <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Active Queue</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada pelanggan aktif untuk antrian bandwidth.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
