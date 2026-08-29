@php
    $user = Auth::user();
    $tenantDomain = config('app.tenant_domain');
    $host = request()->getHost();
    $isTenantDomain = $host !== $tenantDomain && str_ends_with($host, '.'.$tenantDomain);
    $ts = $isTenantDomain ? str_replace('.'.$tenantDomain, '', $host) : null;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ sidebarCollapsed: false, showBugModal: false, showUserMenu: false }">
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
    <div class="logo-mark">N</div>
</div>
        <nav class="sidebar-nav">
            <div class="section-label">MENU</div>
            <a class="nav-item {{ request()->routeIs('tenant.dashboard') ? 'active' : '' }}" href="{{ $ts ? route('tenant.dashboard', ['tenant_slug' => $ts]) : route('dashboard') }}">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 10.5L12 3l9 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M5 9.5V20a1 1 0 0 0 1 1h5v-6h2v6h5a1 1 0 0 0 1-1V9.5" stroke="currentColor" stroke-width="2"/></svg></span>
                <span class="nav-label">Dashboard</span>
            </a>

            @if($ts)
            <div class="section-label">MANAJEMEN</div>

            {{-- Pelanggan & Billing --}}
            <div x-data="{ open: {{ request()->routeIs('packages*') || request()->routeIs('customers*') || request()->routeIs('areas*') || request()->routeIs('invoices*') || request()->routeIs('payments*') ? 'true' : 'false' }} }" class="nav-group">
                <button @click="open = !open" class="nav-group-toggle {{ request()->routeIs('packages*') || request()->routeIs('customers*') || request()->routeIs('areas*') || request()->routeIs('invoices*') || request()->routeIs('payments*') ? 'active' : '' }}">
                    <span class="nav-group-label">
                        <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="2"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity=".5"/></svg></span>
                        <span>Pelanggan & Billing</span>
                    </span>
                    <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" :class="{ 'rotate-180': open }"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div x-show="open" x-collapse class="nav-sub">
                    <a href="{{ route('customers.index', ['tenant_slug' => $ts]) }}" class="nav-sub-item {{ request()->routeIs('customers*') ? 'active' : '' }}">Pelanggan</a>
                    <a href="{{ route('areas.index', ['tenant_slug' => $ts]) }}" class="nav-sub-item {{ request()->routeIs('areas*') ? 'active' : '' }}">Area</a>
                    <a href="{{ route('packages.index', ['tenant_slug' => $ts]) }}" class="nav-sub-item {{ request()->routeIs('packages*') ? 'active' : '' }}">Paket</a>
                    <a href="{{ route('invoices.index', ['tenant_slug' => $ts]) }}" class="nav-sub-item {{ request()->routeIs('invoices*') ? 'active' : '' }}">Invoice</a>
                    <a href="{{ route('payments.index', ['tenant_slug' => $ts]) }}" class="nav-sub-item {{ request()->routeIs('payments*') ? 'active' : '' }}">Pembayaran</a>
                </div>
            </div>

            {{-- Laporan --}}
            <a class="nav-item {{ request()->routeIs('reports*') ? 'active' : '' }}" href="{{ route('reports.index', ['tenant_slug' => $ts]) }}">
                <span class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M3 3v18h18M7 14l4-4 3 3 5-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span class="nav-label">Laporan</span>
            </a>
            @endif
        </nav>
        <div class="sidebar-bottom">
            <button class="sidebar-report-btn" @click="showBugModal = true" title="Laporkan Bug">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="report-label">Laporkan Bug</span>
            </button>
            <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed" title="Ciutkan sidebar" aria-label="Ciutkan sidebar">
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
                <form action="mailto:bugs@netsaz.id" method="post" enctype="text/plain">
                    <input type="hidden" name="subject" value="[BUG] Laporan dari {{ Auth::user()->name ?? 'User' }}">
                    <div class="form-field">
                        <label>Judul Bug</label>
                        <input type="text" name="title" required class="form-input" placeholder="Contoh: Tombol bayar tidak berfungsi">
                    </div>
                    <div class="form-field">
                        <label>Halaman / Lokasi</label>
                        <input type="text" name="page" class="form-input" placeholder="Contoh: Dashboard, Invoice" value="{{ request()->path() }}">
                    </div>
                    <div class="form-field">
                        <label>Deskripsi</label>
                        <textarea name="body" rows="3" required class="form-input" placeholder="Jelaskan langkah-langkah mereproduksi..." style="resize:vertical"></textarea>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:12px">
                        <button type="button" @click="showBugModal = false" class="btn-outline" style="padding:10px 20px">Batal</button>
                        <button type="submit" class="btn-save" style="width:auto;padding:10px 20px;margin-top:0">Kirim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="main" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <div class="topbar">
            <form action="{{ $ts ? route('customers.index', ['tenant_slug' => $ts]) : '#' }}" method="GET" class="search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <input type="text" name="search" placeholder="Cari pelanggan, invoice..." value="{{ request('search') }}" autocomplete="off" style="border:none;background:transparent;flex:1;outline:none;font-size:13.5px;color:var(--ink);font-family:inherit">
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
                            <div class="avatar" style="width:38px;height:38px;border-radius:10px;background:var(--primary-tint);color:var(--primary-dark);display:flex;align-items:center;justify-content:center;font-family:'JetBrains Mono';font-weight:700;font-size:13px">{{ substr(Auth::user()->name ?? 'U', 0, 2) }}</div>
                            <div>
                                <div class="dropdown-name">{{ Auth::user()->name ?? 'User' }}</div>
                                <div class="dropdown-email">{{ Auth::user()->email ?? '' }}</div>
                            </div>
                        </div>
                        <div class="user-dropdown-divider"></div>
                        <a href="{{ $ts ? route('tenant.profile.show', ['tenant_slug' => $ts]) : route('profile.show') }}" class="user-dropdown-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Lihat Profil
                        </a>
                        <a href="{{ $ts ? route('tenant.profile.show', ['tenant_slug' => $ts]) . '#password' : route('profile.show') . '#password' }}" class="user-dropdown-item">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Ganti Password
                        </a>
                        <div class="user-dropdown-divider"></div>
                        <form method="POST" action="{{ $ts ? route('tenant.logout', ['tenant_slug' => $ts]) : route('logout') }}">
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

    @livewireScripts
    @stack('scripts')
</body>
</html>
