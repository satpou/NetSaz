<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $tenant->name) - {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        .tp-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: saturate(180%) blur(14px);
            background: rgba(255,255,255,.85);
            border-bottom: 1px solid var(--line);
        }
        .tp-nav-inner {
            max-width: 1080px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .tp-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .tp-brand-name {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 17px;
            color: var(--ink);
        }
        .tp-links {
            display: flex;
            align-items: center;
            gap: 22px;
        }
        .tp-link {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--ink-soft);
            text-decoration: none;
            transition: color .15s;
        }
        .tp-link:hover { color: var(--ink); }
        .tp-footer {
            border-top: 1px solid var(--line);
            background: var(--panel);
            padding: 28px 24px;
        }
        .tp-footer-inner {
            max-width: 1080px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 13px;
            color: var(--ink-faint);
        }
        @media (max-width: 640px) {
            .tp-link { display: none; }
        }
    </style>
</head>
<body style="background:var(--bg);color:var(--ink);font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased;margin:0">
@php($brandColor = $tenant->brand_color ?: '#2451FF')

<header class="tp-nav">
    <nav class="tp-nav-inner">
        <a href="{{ route('tenant.landing', $tenant->slug) }}" class="tp-brand">
            @if($tenant->logo_path)
                <img src="{{ Storage::url($tenant->logo_path) }}" alt="{{ $tenant->name }}" style="height:32px;width:auto;object-fit:contain;display:block">
            @else
                <span style="width:32px;height:32px;border-radius:9px;background:{{ $brandColor }};display:inline-flex;align-items:center;justify-content:center;font-family:'Space Grotesk';font-weight:700;font-size:14px;color:#fff">{{ strtoupper(substr($tenant->name, 0, 2)) }}</span>
            @endif
            <span class="tp-brand-name">{{ $tenant->name }}</span>
        </a>
        <div class="tp-links">
            <a href="{{ route('tenant.landing', $tenant->slug) }}" class="tp-link">Beranda</a>
            <a href="{{ route('tenant.packages', $tenant->slug) }}" class="tp-link">Paket Layanan</a>
            <a href="{{ route('customer.auth.login', $tenant->slug) }}" class="btn btn-primary" style="font-size:13px;padding:9px 18px">Portal Pelanggan</a>
        </div>
    </nav>
</header>

<main>
    @yield('content')
</main>

<footer class="tp-footer">
    <div class="tp-footer-inner">
        <span>&copy; {{ date('Y') }} {{ $tenant->name }}. All rights reserved.</span>
        <span>Powered by <strong style="color:var(--ink-soft)">NetSaz</strong></span>
    </div>
</footer>

@stack('scripts')
</body>
</html>
