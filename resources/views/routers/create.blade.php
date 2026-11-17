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

@section('title', 'Tambah Router MikroTik')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <a href="{{ route('routers.index', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Router MikroTik</a> &gt;
    <b>Tambah Baru</b>
</div>

<x-page-header title="Tambah Router MikroTik" subtitle="Hubungkan router untuk aktivasi PPPoE dan manajemen bandwidth otomatis">
</x-page-header>

<div class="panel" style="max-width:680px">
    <form method="POST" action="{{ route('routers.store', $ts ? ['tenant_slug' => $ts] : []) }}">
        @csrf

        <div style="margin-bottom:18px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Nama Router <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Router Core Kantor, CCR1009 Tower Barat" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            @error('name')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Host / IP Address / Domain <span style="color:var(--red)">*</span></label>
                <input type="text" name="host" value="{{ old('host') }}" placeholder="Contoh: 192.168.88.1 atau vpn.myisp.id" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('host')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Port API <span style="color:var(--red)">*</span></label>
                <input type="number" name="api_port" value="{{ old('api_port', 8728) }}" min="1" max="65535" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('api_port')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Username API RouterOS</label>
                <input type="text" name="api_username" value="{{ old('api_username', 'admin') }}" placeholder="admin" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('api_username')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Password API RouterOS</label>
                <input type="password" name="api_password" placeholder="Password router..." class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('api_password')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="margin-bottom:24px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Tipe Koneksi</label>
            <select name="connection_type" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <option value="direct" {{ old('connection_type') == 'direct' ? 'selected' : '' }}>Direct Connection (IP Publik Statis / VPN)</option>
                <option value="secure_tunnel" {{ old('connection_type') == 'secure_tunnel' ? 'selected' : '' }}>Secure Tunnel / ZeroTier / WireGuard</option>
            </select>
            @error('connection_type')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="{{ route('routers.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="btn btn-outline" style="padding:10px 20px">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Simpan Router</button>
        </div>
    </form>
</div>
@endsection
