<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal') - {{ config('app.name', 'NetSaz') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body style="background:var(--bg);color:var(--ink);font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased">
    <header style="background:var(--panel);border-bottom:1px solid var(--line);padding:14px 24px;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:12px">
            <div style="width:32px;height:32px;border-radius:8px;background:var(--primary);display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk';font-weight:700;font-size:15px;color:#fff">PB</div>
            <div>
                <div style="font-weight:700;font-size:16px;font-family:'Space Grotesk'">Portal Pelanggan</div>
                @if(Auth::guard('customer')->check())
                <div style="font-size:12px;color:var(--ink-faint)">{{ Auth::guard('customer')->user()->tenant->name ?? '' }}</div>
                @endif
            </div>
        </div>
        @if(Auth::guard('customer')->check())
        <div style="display:flex;align-items:center;gap:16px">
            <a href="{{ route('customer.portal.profile') }}" style="font-size:13px;color:var(--ink-soft);text-decoration:none;font-weight:500">Profil</a>
            <a href="{{ route('customer.portal.invoices') }}" style="font-size:13px;color:var(--ink-soft);text-decoration:none;font-weight:500">Invoice</a>
            <a href="{{ route('customer.portal.payments') }}" style="font-size:13px;color:var(--ink-soft);text-decoration:none;font-weight:500">Pembayaran</a>
            <form method="POST" action="{{ route('customer.auth.logout') }}" style="display:inline">
                @csrf
                <button type="submit" style="font-size:13px;color:var(--red);background:none;border:none;cursor:pointer;font-weight:500">Keluar</button>
            </form>
        </div>
        @endif
    </header>

    <main style="max-width:960px;margin:0 auto;padding:32px 24px">
        @if(session('success'))
        <div style="background:var(--green-tint);border:1px solid var(--green);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--green)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4 10-10"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('info'))
        <div style="background:var(--amber-tint);border:1px solid var(--amber);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:var(--amber)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01"/></svg>
            {{ session('info') }}
        </div>
        @endif
        @if(session('error'))
        <div style="background:#fee2e2;border:1px solid #f87171;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:#b91c1c">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
            {{ session('error') }}
        </div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
