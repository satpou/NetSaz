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

@section('title', 'GenieACS (TR-069)')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Router</span> &gt; <b>GenieACS</b>
</div>

<x-page-header title="GenieACS Auto Configuration Server (TR-069)" subtitle="Manajemen remote otomatis untuk modem ONT / router CPE pelanggan (Auto provisioning, reboot remote, WiFi config)">
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Status Integrasi ACS</div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-top:6px">
            <span style="font-size:24px;font-weight:700;color:var(--ink)">{{ $config->enabled ? 'Terhubung' : 'Nonaktif' }}</span>
            <span class="status-pill {{ $config->enabled ? 'ok' : 'danger' }}" style="padding:2px 8px;font-size:11px">
                <span class="dot" style="background:{{ $config->enabled ? 'var(--primary)' : '#ef4444' }}"></span>{{ $config->enabled ? 'Enabled' : 'Disabled' }}
            </span>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">NBI REST API: <code class="mono">{{ $config->baseUrl() }}</code></div>
    </div>

    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">CWMP Inform URL (Modem)</div>
        <div style="font-size:16px;font-weight:700;color:var(--primary);margin-top:6px;font-family:'JetBrains Mono'">
            http://{{ $config->host }}:7547/
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Alamat server TR-069 pada ONT</div>
    </div>

    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Fitur Otomasi CPE</div>
        <div style="font-size:14px;color:var(--ink);margin-top:6px;line-height:1.5">
            Auto SSIDs, Ganti Password WiFi Remote, Reboot Modem dari Portal Admin, Diagnostic Ping
        </div>
    </div>
</div>

<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;padding:24px;max-width:700px">
    <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin:0 0 8px">Pengaturan Server GenieACS</h3>
    <p style="font-size:13px;color:var(--ink-soft);margin-bottom:20px">Masukkan parameter koneksi instance GenieACS NBI (Northbound Interface) server Anda:</p>

    <form action="{{ route('devices.genieacs.update', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom:18px;display:flex;align-items:center;gap:10px">
            <input type="checkbox" name="enabled" id="enabled" value="1" {{ $config->enabled ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--primary)">
            <label for="enabled" style="font-size:14px;font-weight:600;color:var(--ink);cursor:pointer">Aktifkan sinkronisasi perangkat GenieACS TR-069</label>
        </div>

        <div class="form-field" style="margin-bottom:16px">
            <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Host / IP GenieACS NBI</label>
            <input type="text" name="host" class="form-input mono" value="{{ old('host', $config->host) }}" required placeholder="127.0.0.1 atau domain acs.domain.com">
        </div>

        <div class="form-field" style="margin-bottom:16px">
            <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">NBI Port</label>
            <input type="number" name="port" class="form-input mono" value="{{ old('port', $config->port) }}" required placeholder="7557">
            <small style="font-size:11.5px;color:var(--ink-faint)">Port standar GenieACS NBI API adalah 7557.</small>
        </div>

        <div class="form-field" style="margin-bottom:24px">
            <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">NBI Base URL Lengkap (Opsional)</label>
            <input type="url" name="nbi_base_url" class="form-input mono" value="{{ old('nbi_base_url', $config->nbi_base_url) }}" placeholder="http://acs.netsaz.local:7557">
            <small style="font-size:11.5px;color:var(--ink-faint)">Kosongkan jika menggunakan host dan port di atas.</small>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:10px 24px">
            Simpan Konfigurasi GenieACS
        </button>
    </form>
</div>
@endsection
