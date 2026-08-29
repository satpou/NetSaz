@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="breadcrumb reveal">Home &gt; <b>Dashboard Platform</b></div>

<div class="welcome">
    <h2>Selamat datang, {{ Auth::user()->name ?? 'Super Admin' }}</h2>
    <div class="date" id="today-date"></div>
    <div class="status-pill ok" style="margin-top:8px"><span class="dot"></span>Platform Super Admin</div>
</div>

<div class="stat-grid">
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
        <div class="stat-desc">Pengguna di seluruh ISP</div>
    </div>
    <div class="stat-card reveal d4">
        <div class="stat-top"><span class="stat-label">Total Revenue</span><span class="stat-icon green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span></div>
        <div class="stat-value">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="stat-desc">Total pembayaran sukses semua ISP</div>
    </div>
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
