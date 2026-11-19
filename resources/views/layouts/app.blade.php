@php
    $user = Auth::user();
    $tenantDomain = config('app.tenant_domain');
    $host = request()->getHost();
    $isTenantDomain = $host !== $tenantDomain && str_ends_with($host, '.'.$tenantDomain);
    $ts = $isTenantDomain ? str_replace('.'.$tenantDomain, '', $host) : ($user?->tenant?->slug ?? session('tenant_slug') ?? null);
    $isTenantMode = $isTenantDomain || ($user && $user->tenant_id);
    $isPlatformSuperAdmin = $user && $user->isPlatformSuperAdmin();
    if ($ts) {
        \Illuminate\Support\Facades\URL::defaults(['tenant_slug' => $ts]);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarCollapsed: (localStorage.getItem('netsaz_sidebar_collapsed') === 'true'), showBugModal: false, showUserMenu: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'NetSaz') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js', 'resources/js/dashboard.js'])

    @livewireStyles
    @stack('styles')
</head>
<body>
    <aside class="sidebar" :class="{ 'collapsed': sidebarCollapsed }">
<div class="sidebar-top">
    <img class="sidebar-logo" src="{{ asset('images/netsaz/NetSaz_icon.png') }}" alt="" width="34" height="34">
    <span class="sidebar-brand">NetSaz</span>
</div>
        <nav class="sidebar-nav">
            <div class="section-label">MENU</div>
            <a class="nav-item {{ request()->routeIs('tenant.dashboard') || request()->routeIs('dashboard') || request()->routeIs('dashboard.app') ? 'active' : '' }}" href="{{ $isTenantDomain && $ts ? route('tenant.dashboard', ['tenant_slug' => $ts]) : route('dashboard.app') }}" title="Dashboard">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 10.5L12 3l9 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M5 9.5V20a1 1 0 0 0 1 1h5v-6h2v6h5a1 1 0 0 0 1-1V9.5" stroke="currentColor" stroke-width="2"/></svg></span>
                <span class="nav-label">Dashboard</span>
            </a>

            @if($isPlatformSuperAdmin)
            <div class="section-label">SUPER ADMIN</div>
            <a class="nav-item {{ request()->routeIs('admin.tenants*') ? 'active' : '' }}" href="{{ route('admin.tenants.index') }}" title="Kelola Tenant">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></span>
                <span class="nav-label">Kelola Tenant</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.plans*') ? 'active' : '' }}" href="{{ route('admin.plans.index') }}" title="Paket Langganan">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg></span>
                <span class="nav-label">Paket Langganan</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}" title="Subscription">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg></span>
                <span class="nav-label">Subscription</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.verifications*') ? 'active' : '' }}" href="{{ route('admin.verifications.index') }}" title="Verifikasi Pembayaran">
                <span class="ic" style="position:relative">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    @php
                        $pendingVerifCount = \App\Models\Tenant::where('status', 'pending_payment')->count();
                    @endphp
                    @if($pendingVerifCount > 0)
                        <span class="badge-dot-collapsed" style="position:absolute;top:-3px;right:-3px;width:8px;height:8px;border-radius:50%;background:#f59e0b;display:none;border:2px solid var(--bg)"></span>
                    @endif
                </span>
                <span class="nav-label">
                    <span style="display:flex;align-items:center;justify-content:space-between;width:100%;">
                        <span>Verifikasi Tagihan</span>
                        @if($pendingVerifCount > 0)
                            <span style="background:#f59e0b;color:#0f172a;font-weight:700;font-size:11px;padding:2px 7px;border-radius:9999px;line-height:1;">{{ $pendingVerifCount }}</span>
                        @endif
                    </span>
                </span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.reports*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}" title="Laporan">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></span>
                <span class="nav-label">Laporan</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}" title="Pengaturan Platform">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
                <span class="nav-label">Pengaturan Platform</span>
            </a>
            @endif

            @if($isTenantMode)
            <div class="section-label">MANAJEMEN</div>

            {{-- 1. Router --}}
            <div x-data="{ open: {{ request()->routeIs('routers*') || request()->routeIs('devices.*') ? 'true' : 'false' }} }" class="nav-group">
                <button @click="open = !open" class="nav-group-toggle {{ request()->routeIs('routers*') || request()->routeIs('devices.*') ? 'active' : '' }}" title="Router">
                    <span class="nav-group-label">
                        <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="8" width="20" height="8" rx="2" ry="2"/><rect x="4" y="4" width="2" height="4"/><rect x="18" y="4" width="2" height="4"/><line x1="6" y1="12" x2="6.01" y2="12"/><line x1="10" y1="12" x2="10.01" y2="12"/><line x1="14" y1="12" x2="14.01" y2="12"/></svg></span>
                        <span class="nav-label">Router</span>
                    </span>
                    <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" :class="{ 'rotate-180': open }"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div x-show="open && !sidebarCollapsed" x-collapse class="nav-sub">
                    <a href="{{ route('routers.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('routers*') ? 'active' : '' }}">My Mikrotik</a>
                    <a href="{{ route('devices.radius', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('devices.radius*') ? 'active' : '' }}">FastRADIUS Hub</a>
                    <a href="{{ route('devices.olt', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('devices.olt*') ? 'active' : '' }}">OLT</a>
                    <a href="{{ route('devices.genieacs', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('devices.genieacs*') ? 'active' : '' }}">GenieACS</a>
                </div>
            </div>

            {{-- 2. Network --}}
            <div x-data="{ open: {{ request()->routeIs('network.*') ? 'true' : 'false' }} }" class="nav-group">
                <button @click="open = !open" class="nav-group-toggle {{ request()->routeIs('network.*') ? 'active' : '' }}" title="Network">
                    <span class="nav-group-label">
                        <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg></span>
                        <span class="nav-label">Network</span>
                    </span>
                    <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" :class="{ 'rotate-180': open }"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div x-show="open && !sidebarCollapsed" x-collapse class="nav-sub">
                    <a href="{{ route('network.monitoring', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('network.monitoring') ? 'active' : '' }}">Monitoring</a>
                    <a href="{{ route('network.ip', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('network.ip') ? 'active' : '' }}">IP Management</a>
                    <a href="{{ route('network.logs', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('network.logs') ? 'active' : '' }}">Logs</a>
                    <a href="{{ route('network.bandwidth', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('network.bandwidth') ? 'active' : '' }}">Bandwidth</a>
                    <a href="{{ route('network.fault', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('network.fault') ? 'active' : '' }}">Fault Detection</a>
                </div>
            </div>

            {{-- 3. Pelanggan & Billing --}}
            <div x-data="{ open: {{ request()->routeIs('customers*') || request()->routeIs('areas*') || request()->routeIs('packages*') || request()->routeIs('invoices*') || request()->routeIs('payments*') || request()->routeIs('billing.auto-isolir*') || request()->routeIs('partners*') || request()->routeIs('tickets*') ? 'true' : 'false' }} }" class="nav-group">
                <button @click="open = !open" class="nav-group-toggle {{ request()->routeIs('customers*') || request()->routeIs('areas*') || request()->routeIs('packages*') || request()->routeIs('invoices*') || request()->routeIs('payments*') || request()->routeIs('billing.auto-isolir*') || request()->routeIs('partners*') || request()->routeIs('tickets*') ? 'active' : '' }}" title="Pelanggan & Billing">
                    <span class="nav-group-label">
                        <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                        <span class="nav-label">Pelanggan &amp; Billing</span>
                    </span>
                    <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" :class="{ 'rotate-180': open }"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div x-show="open && !sidebarCollapsed" x-collapse class="nav-sub">
                    <a href="{{ route('customers.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('customers*') ? 'active' : '' }}">Pelanggan</a>
                    <a href="{{ route('areas.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('areas*') ? 'active' : '' }}">Area</a>
                    <a href="{{ route('packages.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('packages*') ? 'active' : '' }}">Paket</a>
                    <a href="{{ route('invoices.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('invoices*') ? 'active' : '' }}">Invoice</a>
                    <a href="{{ route('payments.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('payments*') ? 'active' : '' }}">Pembayaran</a>
                    <a href="{{ route('billing.auto-isolir', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('billing.auto-isolir*') ? 'active' : '' }}">Auto Isolir</a>
                    <a href="{{ route('partners.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('partners*') ? 'active' : '' }}">Mitra</a>
                    <a href="{{ route('tickets.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('tickets*') ? 'active' : '' }}">Tiket</a>
                </div>
            </div>

            {{-- 4. Operasional --}}
            <div x-data="{ open: {{ request()->routeIs('operasional.*') || request()->routeIs('import*') || request()->routeIs('reports*') ? 'true' : 'false' }} }" class="nav-group">
                <button @click="open = !open" class="nav-group-toggle {{ request()->routeIs('operasional.*') || request()->routeIs('import*') || request()->routeIs('reports*') ? 'active' : '' }}" title="Operasional">
                    <span class="nav-group-label">
                        <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg></span>
                        <span class="nav-label">Operasional</span>
                    </span>
                    <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" :class="{ 'rotate-180': open }"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div x-show="open && !sidebarCollapsed" x-collapse class="nav-sub">
                    <a href="{{ route('operasional.staff', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('operasional.staff*') ? 'active' : '' }}">Staff</a>
                    <a href="{{ route('import.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('import*') ? 'active' : '' }}">Import</a>
                    <a href="{{ route('operasional.export', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('operasional.export*') ? 'active' : '' }}">Export</a>
                    <a href="{{ route('reports.index', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('reports*') ? 'active' : '' }}">Laporan</a>
                </div>
            </div>

            {{-- 5. Deployment Center --}}
            <a class="nav-item {{ request()->routeIs('deployment.*') ? 'active' : '' }}" href="{{ route('deployment.index', $ts ? ['tenant_slug' => $ts] : []) }}" title="Deployment Center">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 11 3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></span>
                <span class="nav-label">Deployment Center</span>
            </a>

            {{-- 6. WhatsApp --}}
            <div x-data="{ open: {{ request()->routeIs('whatsapp.*') || request()->routeIs('tenant.settings*') ? 'true' : 'false' }} }" class="nav-group">
                <button @click="open = !open" class="nav-group-toggle {{ request()->routeIs('whatsapp.*') || request()->routeIs('tenant.settings*') ? 'active' : '' }}" title="WhatsApp">
                    <span class="nav-group-label">
                        <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></span>
                        <span class="nav-label">WhatsApp</span>
                    </span>
                    <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" :class="{ 'rotate-180': open }"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div x-show="open && !sidebarCollapsed" x-collapse class="nav-sub">
                    <a href="{{ route('tenant.settings.edit', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('tenant.settings*') ? 'active' : '' }}">Pengaturan</a>
                    <a href="{{ route('whatsapp.templates', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('whatsapp.templates*') ? 'active' : '' }}">Template</a>
                    <a href="{{ route('whatsapp.broadcast', $ts ? ['tenant_slug' => $ts] : []) }}" class="nav-sub-item {{ request()->routeIs('whatsapp.broadcast*') ? 'active' : '' }}">Broadcast</a>
                </div>
            </div>
            @endif
        </nav>
        <div class="sidebar-bottom">
            <button class="sidebar-report-btn" @click="showBugModal = true" title="Laporkan Bug">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="report-label">Laporkan Bug</span>
            </button>
            <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('netsaz_sidebar_collapsed', sidebarCollapsed)" :title="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'" :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </aside>

    <!-- Bug Report Modal -->
    <div x-show="showBugModal" x-cloak class="modal-overlay" @click.self="showBugModal = false" style="display:none">
        <div class="modal" @click.stop>
            <div class="modal-header">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <h3 style="font-family:'Space Grotesk';font-size:18px;font-weight:700;color:var(--ink)">Laporkan Bug</h3>
                    <button @click="showBugModal = false" style="background:none;border:none;cursor:pointer;color:var(--ink-faint);padding:4px">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M18 6 6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <p style="font-size:13px;color:var(--ink-faint);margin-top:4px">Jelaskan bug yang Anda temukan.</p>
            </div>
            <div class="modal-body">
                <form action="{{ route('contact.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="name" value="{{ Auth::user()->name ?? 'User NetSaz' }}">
                    <input type="hidden" name="email" value="{{ Auth::user()->email ?? 'user@netsaz.web.id' }}">
                    <div class="form-field">
                        <label>Judul Bug</label>
                        <input type="text" name="bug_title" required class="form-input" placeholder="Contoh: Tombol bayar tidak berfungsi" id="bug_title">
                    </div>
                    <div class="form-field">
                        <label>Halaman / Lokasi</label>
                        <input type="text" name="bug_page" class="form-input" placeholder="Contoh: Dashboard, Invoice" value="{{ request()->path() }}" id="bug_page">
                    </div>
                    <div class="form-field">
                        <label>Deskripsi Masalah</label>
                        <textarea name="bug_desc" rows="3" required class="form-input" placeholder="Jelaskan langkah-langkah mereproduksi..." style="resize:vertical" id="bug_desc"></textarea>
                    </div>
                    <input type="hidden" name="message" id="bug_combined_message">
                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
                        <button type="button" @click="showBugModal = false" class="btn-outline" style="padding:10px 20px">Batal</button>
                        <button type="submit" class="btn-save" style="width:auto;padding:10px 20px;margin-top:0" onclick="document.getElementById('bug_combined_message').value = '[BUG REPORT] ' + document.getElementById('bug_title').value + ' (Lokasi: ' + document.getElementById('bug_page').value + ')\n\n' + document.getElementById('bug_desc').value;">Kirim Laporan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="main" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <div class="topbar">
            @php
                $searchAction = $isTenantDomain && $ts 
                    ? route('customers.index', ['tenant_slug' => $ts]) 
                    : ($isTenantMode 
                        ? route('customers.index', $ts ? ['tenant_slug' => $ts] : []) 
                        : route('admin.tenants.index'));
                $searchPlaceholder = $isTenantMode ? 'Cari pelanggan, invoice...' : 'Cari ISP, tenant, email...';
            @endphp
            <form action="{{ $searchAction }}" method="GET" class="search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="search" placeholder="{{ $searchPlaceholder }}" value="{{ request('search') }}" autocomplete="off" style="border:none;background:transparent;flex:1;outline:none;font-size:13.5px;color:var(--ink);font-family:inherit">
            </form>
            <div class="topbar-right">
                <div class="user" @click.outside="showUserMenu = false" style="position:relative;cursor:pointer" @click="showUserMenu = !showUserMenu">
                    <div class="avatar">{{ substr(Auth::user()->name ?? 'U', 0, 2) }}</div>
                    <div>
                        <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="user-mail">{{ Auth::user()->email ?? '' }}</div>
                    </div>

                    <div class="user-dropdown" x-show="showUserMenu" x-cloak @click.stop>
                        <div class="user-dropdown-header">
                            <div class="avatar" style="width:38px;height:38px;border-radius:10px;background:var(--primary-tint-solid);color:var(--primary);display:flex;align-items:center;justify-content:center;font-family:'JetBrains Mono';font-weight:700;font-size:13px">{{ substr(Auth::user()->name ?? 'U', 0, 2) }}</div>
                            <div>
                                <div class="dropdown-name">{{ Auth::user()->name ?? 'User' }}</div>
                                <div class="dropdown-email">{{ Auth::user()->email ?? '' }}</div>
                            </div>
                        </div>
                        <div class="user-dropdown-divider"></div>
                        <a href="{{ $isTenantDomain && $ts ? route('tenant.profile.show', ['tenant_slug' => $ts]) : ($isTenantMode ? route('tenant.profile.show', $ts ? ['tenant_slug' => $ts] : []) : route('profile.show')) }}" class="user-dropdown-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Lihat Profil
                        </a>
                        <a href="{{ ($isTenantDomain && $ts ? route('tenant.profile.show', ['tenant_slug' => $ts]) : ($isTenantMode ? route('tenant.profile.show', $ts ? ['tenant_slug' => $ts] : []) : route('profile.show'))) . '#password' }}" class="user-dropdown-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Ganti Password
                        </a>
                        <div class="user-dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="user-dropdown-item danger" style="width:100%;text-align:left;background:transparent;border:none;cursor:pointer">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            @php
                $activeTenant = Auth::user()?->tenant;
                $activeSub = $activeTenant ? $activeTenant->currentSubscription : null;
                $subDaysLeft = ($activeSub && $activeSub->current_period_end) 
                    ? (int) now()->startOfDay()->diffInDays($activeSub->current_period_end->startOfDay(), false) 
                    : null;
            @endphp

            @if($isTenantMode && $subDaysLeft !== null && $subDaysLeft <= 30)
                <div style="background:{{ $subDaysLeft <= 7 ? 'rgba(239, 68, 68, 0.12)' : 'rgba(245, 158, 11, 0.12)' }};border:1px solid {{ $subDaysLeft <= 7 ? '#EF4444' : '#F59E0B' }};border-radius:10px;padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                    <div style="display:flex;align-items:center;gap:10px;color:{{ $subDaysLeft <= 7 ? '#F87171' : '#FBBF24' }};font-size:13.5px">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span>
                            @if($subDaysLeft < 0)
                                <b>Masa langganan tahunan NetSaz telah berakhir.</b> Segera lakukan perpanjangan agar sistem tetap aktif.
                            @elseif($subDaysLeft === 0)
                                <b>Masa langganan tahunan NetSaz berakhir HARI INI.</b> Lakukan perpanjangan segera.
                            @else
                                Masa langganan tahunan ISP Anda tersisa <b>{{ $subDaysLeft }} hari lagi</b> (berakhir pada {{ $activeSub->current_period_end->format('d M Y') }}).
                            @endif
                        </span>
                    </div>
                    @php
                        $salesWa = \App\Models\PlatformSetting::get('sales_whatsapp', '083162238930');
                        $renewText = "Halo Sales NetSaz, saya ingin melakukan perpanjangan langganan tahunan untuk ISP " . ($activeTenant->name ?? 'kami') . " (" . ($activeTenant->slug ?? '') . ".netsaz.web.id).";
                    @endphp
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $salesWa) }}?text={{ urlencode($renewText) }}" target="_blank" rel="noopener" class="btn" style="background:{{ $subDaysLeft <= 7 ? '#EF4444' : '#F59E0B' }};color:#fff;border:none;padding:6px 14px;border-radius:6px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                        Perpanjang Sekarang
                    </a>
                </div>
            @endif

            @if(session('success'))
            <div style="background:var(--green-tint);border:1px solid var(--green);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--green)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4 10-10"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('warning'))
            <div style="background:var(--amber-tint);border:1px solid var(--amber);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--amber)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
                {{ session('warning') }}
            </div>
            @endif
            @if(session('error'))
            <div style="background:var(--red-tint);border:1px solid var(--red);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--red)">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6m0-6l6 6"/></svg>
                {{ session('error') }}
            </div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </div>

    <x-confirm-modal />

    @livewireScripts
    @stack('scripts')
</body>
</html>
