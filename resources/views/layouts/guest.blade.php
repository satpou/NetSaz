<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'NetSaz') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

    <!-- Vite bundled CSS (includes design-system + all page CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Page-specific styles -->
    @stack('styles')
</head>
<body style="background:var(--bg);color:var(--ink);font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased">
    <!-- Scroll progress bar -->
    <div id="progress"></div>

    <!-- Navbar -->
    <header class="navbar">
<nav class="navbar-nav">
    <a href="{{ route('dashboard') }}" class="logo"><img src="{{ asset('images/netsaz/NetSaz_logo_transparent.png') }}" alt="NetSaz" style="height:36px;width:auto;object-fit:contain;display:block;"></a>
    <div class="nav-links">
                <a href="{{ route('features') }}" class="nav-link {{ request()->routeIs('features') ? 'nav-link-active' : '' }}">Fitur</a>
                <a href="{{ route('price') }}" class="nav-link {{ request()->routeIs('price') ? 'nav-link-active' : '' }}">Harga</a>
                <a href="{{ route('faq') }}" class="nav-link {{ request()->routeIs('faq') ? 'nav-link-active' : '' }}">FAQ</a>
                <a href="{{ route('contact') }}" class="nav-link {{ request()->routeIs('contact') ? 'nav-link-active' : '' }}">Kontak</a>
            </div>
            <a class="btn btn-outline" href="{{ route('login') }}">Masuk</a>
        </nav>
    </header>

    <!-- Main content -->
    <main>
        @yield('content')
    </main>

    @if(!isset($hideFooter) || !$hideFooter)
    <!-- Footer -->
    <footer class="footer">
        <div class="wrap">
            <div class="footer-grid">
                <div class="footer-col footer-col-brand">
                    <div class="footer-logo-text">NetSaz</div>
                    <p class="footer-desc">Billing management terpercaya untuk RT/RW net dan ISP kecil di Indonesia. Tagihan otomatis, pembayaran online, dan portal pelanggan dalam satu platform.</p>
                </div>

                <div class="footer-col">
                    <div class="footer-col-title">Produk</div>
                    <ul class="footer-col-links">
                        <li><a href="{{ route('features') }}">Fitur</a></li>
                        <li><a href="{{ route('price') }}">Harga</a></li>
                        <li><a href="{{ route('faq') }}">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <div class="footer-col-title">Perusahaan</div>
                    <ul class="footer-col-links">
                        <li><a href="{{ route('contact') }}">Kontak</a></li>
                        <li><a href="{{ route('legal') }}">Syarat &amp; Ketentuan</a></li>
                        <li><a href="{{ route('legal') }}">Kebijakan Privasi</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <div class="footer-col-title">Kontak</div>
                    <ul class="footer-col-links">
                        <li><a href="mailto:srahmaddhani@gmail.com">srahmaddhani@gmail.com</a></li>
                        <li><a href="https://wa.me/6281234567890">+62 812-3456-7890</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="foot-note">© {{ date('Y') }} NetSaz. Dibuat untuk ISP Indonesia.</div>
                <div class="footer-bottom-links">
                    <a href="{{ route('legal') }}">Syarat &amp; Ketentuan</a>
                    <span aria-hidden="true">·</span>
                    <a href="{{ route('legal') }}">Kebijakan Privasi</a>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <!-- Page-specific scripts -->
    @stack('scripts')
</body>
</html>