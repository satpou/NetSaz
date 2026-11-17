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

@section('title', 'Router MikroTik')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Router MikroTik</b>
</div>

<x-page-header title="Router MikroTik" subtitle="Hubungkan router MikroTik untuk otomasi PPPoE, isolir otomatis, dan manajemen bandwidth">
    <a href="{{ route('routers.create', $ts ? ['tenant_slug' => $ts] : []) }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Router
    </a>
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('warning'))
    <div style="background:var(--amber-tint);border:1px solid var(--amber);color:var(--amber);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('warning') }}</span>
    </div>
@endif

<!-- Router Cards Grid -->
<div class="panel" style="overflow:hidden;padding:0">
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase;letter-spacing:.04em">
                    <th style="padding:16px 20px 14px 24px">Nama Router</th>
                    <th style="padding:16px 16px 14px">Host / IP & Port</th>
                    <th style="padding:16px 16px 14px">Tipe Koneksi</th>
                    <th style="padding:16px 16px 14px">Status</th>
                    <th style="padding:16px 24px 14px;text-align:right">Aksi & Integrasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routers as $router)
                    <tr style="border-bottom:1px solid var(--line);transition:background .15s">
                        <td style="padding:16px 20px 16px 24px">
                            <div style="font-weight:700;color:var(--ink);font-size:15px">{{ $router->name }}</div>
                            <div style="font-size:12px;color:var(--ink-faint);margin-top:2px">
                                User API: <code class="mono">{{ $router->api_username ?? 'admin' }}</code>
                            </div>
                        </td>
                        <td style="padding:16px 16px">
                            <div class="mono" style="font-size:14px;font-weight:600;color:var(--primary)">
                                {{ $router->host }}:{{ $router->api_port ?: 8728 }}
                            </div>
                        </td>
                        <td style="padding:16px 16px">
                            <span style="font-size:12px;padding:3px 8px;border-radius:6px;background:var(--bg-alt);color:var(--ink-soft)">
                                {{ $router->connection_type === 'secure_tunnel' ? 'Secure Tunnel' : 'Direct / VPN' }}
                            </span>
                        </td>
                        <td style="padding:16px 16px">
                            @if($router->status === 'online')
                                <span class="status-pill ok" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Online</span>
                            @else
                                <span class="status-pill danger" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Offline</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px 16px 16px;text-align:right">
                            <div style="display:inline-flex;align-items:center;gap:6px">
                                <!-- Test Connection Button -->
                                <form method="POST" action="{{ route('routers.test', array_merge(['router' => $router->id], $ts ? ['tenant_slug' => $ts] : [])) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px" title="Cek Koneksi ke Router">
                                        Test Ping
                                    </button>
                                </form>

                                <!-- Sync Secrets Button -->
                                <form method="POST" action="{{ route('routers.sync-secrets', array_merge(['router' => $router->id], $ts ? ['tenant_slug' => $ts] : [])) }}" style="display:inline" onsubmit="return confirm('Kirim semua data PPPoE pelanggan ke MikroTik ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px;color:var(--primary)" title="Sinkronkan Secret PPPoE">
                                        Sync PPPoE
                                    </button>
                                </form>

                                <a href="{{ route('routers.edit', array_merge(['router' => $router->id], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px">
                                    Edit
                                </a>

                                <form method="POST" action="{{ route('routers.destroy', array_merge(['router' => $router->id], $ts ? ['tenant_slug' => $ts] : [])) }}" style="display:inline" onsubmit="return confirm('Hapus router {{ $router->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:12.5px;color:var(--red);border-color:var(--red-tint)">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:48px 24px;text-align:center;color:var(--ink-faint)">
                            Belum ada router MikroTik yang terhubung. Klik tombol "Tambah Router" untuk menghubungkan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
