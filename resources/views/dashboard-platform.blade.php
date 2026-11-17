@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="breadcrumb reveal"><a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Dashboard Platform</b></div>

<div class="welcome">
    <h2>Selamat datang, {{ Auth::user()->name ?? 'Super Admin' }}</h2>
    <div class="date" id="today-date"></div>
    <div class="status-pill ok" style="margin-top:8px"><span class="dot"></span>Platform Super Admin</div>
</div>

<div class="stat-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="stat-card reveal d1">
        <div class="stat-top"><span class="stat-label">Total ISP</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></span></div>
        <div class="stat-value" data-count="{{ $totalTenants }}">{{ $totalTenants }}</div>
        <div class="stat-desc">{{ $activeTenants }} aktif, {{ $pendingTenants }} pending, {{ $suspendedTenants }} suspend</div>
    </div>
    <div class="stat-card reveal d2">
        <div class="stat-top"><span class="stat-label">Total Pelanggan</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="2"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span></div>
        <div class="stat-value" data-count="{{ $totalCustomers }}">{{ $totalCustomers }}</div>
        <div class="stat-desc">Pelanggan di seluruh ISP</div>
    </div>
    <div class="stat-card reveal d3">
        <div class="stat-top"><span class="stat-label">Total Pengguna</span><span class="stat-icon green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" stroke="currentColor" stroke-width="2"/></svg></span></div>
        <div class="stat-value" data-count="{{ $totalUsers }}">{{ $totalUsers }}</div>
        <div class="stat-desc">{{ $adminUsers ?? 0 }} admin & {{ $staffUsers ?? 0 }} staff di semua ISP</div>
    </div>
    <div class="stat-card reveal d4">
        <div class="stat-top"><span class="stat-label">Total Revenue</span><span class="stat-icon green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span></div>
        <div class="stat-value">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="stat-desc">Total pembayaran sukses semua ISP</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-top:24px">
    <a href="{{ route('admin.tenants.index') }}" class="panel reveal d1" style="display:flex;align-items:center;gap:18px;text-decoration:none;padding:22px 24px;border-radius:14px;transition:all .2s ease;cursor:pointer">
        <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-tint-solid);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
            </svg>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:4px">Kelola Tenant</div>
            <div style="font-size:13px;color:var(--ink-soft)">Lihat, setujui, dan suspend ISP yang terdaftar</div>
        </div>
    </a>

    <a href="{{ route('admin.plans.index') }}" class="panel reveal d2" style="display:flex;align-items:center;gap:18px;text-decoration:none;padding:22px 24px;border-radius:14px;transition:all .2s ease;cursor:pointer">
        <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-tint-solid);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:4px">Paket Langganan Platform</div>
            <div style="font-size:13px;color:var(--ink-soft)">Atur paket harga yang bisa dipilih ISP</div>
        </div>
    </a>

    <a href="{{ route('admin.subscriptions.index') }}" class="panel reveal d3" style="display:flex;align-items:center;gap:18px;text-decoration:none;padding:22px 24px;border-radius:14px;transition:all .2s ease;cursor:pointer">
        <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-tint-solid);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:4px">Subscription ISP</div>
            <div style="font-size:13px;color:var(--ink-soft)">Lihat status langganan dan tagihan ISP</div>
        </div>
    </a>

    <a href="{{ route('admin.reports.index') }}" class="panel reveal d4" style="display:flex;align-items:center;gap:18px;text-decoration:none;padding:22px 24px;border-radius:14px;transition:all .2s ease;cursor:pointer">
        <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-tint-solid);color:var(--primary);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
            </svg>
        </div>
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:4px">Laporan Platform</div>
            <div style="font-size:13px;color:var(--ink-soft)">Revenue, pelanggan, dan staff seluruh ISP</div>
        </div>
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('today-date');
    if (el) {
        var d = new Date();
        var opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        el.textContent = d.toLocaleDateString('id-ID', opts);
    }
    document.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseInt(el.getAttribute('data-count'));
        if (isNaN(target) || target === 0) return;
        var current = 0;
        var step = Math.max(1, Math.ceil(target / 30));
        var timer = setInterval(function () {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = current;
        }, 30);
    });
});
</script>
@endsection
