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

@section('title', 'FastRADIUS Hub')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Router</span> &gt; <b>FastRADIUS Hub</b>
</div>

<x-page-header title="FastRADIUS Hub" subtitle="Pusat autentikasi RADIUS tersentralisasi untuk multi-router MikroTik ISP Anda">
    <div style="display:flex;gap:10px">
        <button onclick="document.getElementById('modal-config-hub').style.display='flex'" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
            {{ $hub ? 'Edit Konfigurasi Hub' : 'Aktifkan Radius Hub' }}
        </button>
    </div>
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Status FastRADIUS</div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-top:6px">
            <span style="font-size:24px;font-weight:700;color:var(--ink)">{{ $hub ? 'Aktif' : 'Belum Dikonfigurasi' }}</span>
            <span class="status-pill {{ $hub ? 'ok' : 'danger' }}" style="padding:2px 8px;font-size:11px">
                <span class="dot" style="background:{{ $hub ? 'var(--primary)' : '#ef4444' }}"></span>{{ $hub ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Database: <code class="mono">{{ $hub->db_name ?? '-' }}</code></div>
    </div>

    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Server Host & Port</div>
        <div style="font-size:20px;font-weight:700;color:var(--primary);margin-top:6px;font-family:'JetBrains Mono'">
            {{ $hub ? $hub->server_host . ':' . $hub->radius_port : '127.0.0.1:1812' }}
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Port Accounting: <code class="mono">1813</code></div>
    </div>

    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Router NAS Terhubung</div>
        <div style="font-size:24px;font-weight:700;color:var(--ink);margin-top:6px">
            {{ $hub ? $hub->members->count() : 0 }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Router</span>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Tersedia: {{ $nasServers->count() }} router MikroTik</div>
    </div>
</div>

<!-- Connected NAS Table -->
<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="padding:18px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
        <div>
            <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0">Router NAS dalam Hub</h3>
            <p style="font-size:13px;color:var(--ink-soft);margin:3px 0 0">Router MikroTik yang mengautentikasi pelanggan melalui FastRADIUS ini</p>
        </div>
        @if($hub && $nasServers->count() > 0)
            <form action="{{ route('devices.radius.member.add', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST" style="display:flex;gap:10px">
                @csrf
                <select name="nas_server_id" class="form-input" style="padding:8px 12px;font-size:13px" required>
                    <option value="">-- Pilih Router untuk Ditambahkan --</option>
                    @foreach($nasServers as $nas)
                        <option value="{{ $nas->id }}">{{ $nas->name }} ({{ $nas->host }})</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline" style="padding:8px 14px;white-space:nowrap">Hubungkan NAS</button>
            </form>
        @endif
    </div>

    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Nama Router</th>
                    <th style="padding:14px 16px">Host / IP NAS</th>
                    <th style="padding:14px 16px">Status Koneksi</th>
                    <th style="padding:14px 16px">Bergabung</th>
                    <th style="padding:14px 20px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if($hub && $hub->members->count() > 0)
                    @foreach($hub->members as $member)
                        <tr style="border-bottom:1px solid var(--line)">
                            <td style="padding:14px 20px;font-weight:600;color:var(--ink)">
                                {{ $member->nasServer?->name ?? 'Router #' . $member->nas_server_id }}
                            </td>
                            <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:13px;color:var(--primary)">
                                {{ $member->nasServer?->host ?? '-' }}
                            </td>
                            <td style="padding:14px 16px">
                                <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Synced</span>
                            </td>
                            <td style="padding:14px 16px;font-size:12px;color:var(--ink-faint)">
                                {{ $member->joined_at ? $member->joined_at->diffForHumans() : '-' }}
                            </td>
                            <td style="padding:14px 20px;text-align:right">
                                <form action="{{ route('devices.radius.member.remove', array_merge($ts ? ['tenant_slug' => $ts] : [], ['member' => $member->id])) }}" method="POST" onsubmit="return confirm('Lepas router ini dari Radius Hub?');" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline" style="padding:5px 10px;font-size:12px;color:#ef4444;border-color:rgba(239,68,68,0.3)">Lepas</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada Router NAS yang terhubung ke FastRADIUS Hub.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

@if($hub)
<!-- FastRADIUS MikroTik Script -->
<div class="card" style="padding:24px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
    <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0 0 8px">Script Konfigurasi MikroTik RADIUS Client</h3>
    <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px">Salin dan jalankan script ini di Terminal MikroTik Anda untuk mengaktifkan autentikasi RADIUS PPPoE dan Hotspot:</p>
    
    <div style="background:#090d16;padding:16px;border-radius:8px;border:1px solid var(--line);font-family:'JetBrains Mono';font-size:13px;color:#38bdf8;position:relative">
        <pre style="margin:0;white-space:pre-wrap;word-break:break-all">/radius add address={{ $hub->server_host }} secret="{{ $hub->radius_secret }}" service=ppp,login authentication-port={{ $hub->radius_port }} accounting-port=1813 timeout=3000ms comment="NetSaz FastRADIUS"
/radius incoming set accept=yes port=3799
/ppp aaa set use-radius=yes accounting=yes interim-update=5m</pre>
    </div>
</div>
@endif

<!-- Modal Config Hub -->
<div id="modal-config-hub" class="modal-overlay" style="display:none;align-items:center;justify-content:center;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999">
    <div class="modal-card" style="background:var(--bg-card);border:1px solid var(--line);border-radius:14px;width:100%;max-width:480px;padding:24px">
        <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:12px">Konfigurasi FastRADIUS Hub</h3>
        <form action="{{ route('devices.radius.store', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST">
            @csrf
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Nama Database Hub</label>
                <input type="text" name="db_name" class="form-input" value="{{ $hub->db_name ?? 'radius_' . ($ts ?? 'isp') }}" required placeholder="radius_tenant">
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Server Host / IP</label>
                <input type="text" name="server_host" class="form-input" value="{{ $hub->server_host ?? request()->getHost() }}" required placeholder="103.x.x.x atau domain">
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Radius Port</label>
                <input type="number" name="radius_port" class="form-input" value="{{ $hub->radius_port ?? 1812 }}" required>
            </div>
            <div class="form-field" style="margin-bottom:20px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Radius Secret Key</label>
                <input type="text" name="radius_secret" class="form-input mono" value="{{ $hub->radius_secret ?? 'radius_secret_' . bin2hex(random_bytes(4)) }}" required>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="document.getElementById('modal-config-hub').style.display='none'" class="btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
@endsection
