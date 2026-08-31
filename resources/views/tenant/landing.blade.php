@extends('layouts.tenant-public')

@php($websiteContent = $tenant->settings['website_content'] ?? [])
@php($brandColor = $tenant->brand_color ?: '#2451FF')

@section('title', $websiteContent['hero_title'] ?? $tenant->name)

@section('content')
<section style="background:linear-gradient(180deg, {{ $brandColor }}12 0%, {{ $brandColor }}04 100%);padding:88px 24px 92px;position:relative;overflow:hidden">
    <div style="position:absolute;top:-120px;right:-80px;width:400px;height:400px;border-radius:50%;background:{{ $brandColor }};opacity:.04;pointer-events:none"></div>
    <div style="position:absolute;bottom:-160px;left:-120px;width:500px;height:500px;border-radius:50%;background:{{ $brandColor }};opacity:.03;pointer-events:none"></div>
    <div style="max-width:1080px;margin:0 auto;position:relative;z-index:1">
        <span style="display:inline-flex;align-items:center;gap:8px;background:{{ $brandColor }}10;border:1px solid {{ $brandColor }}20;border-radius:100px;padding:6px 14px;font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:{{ $brandColor }}">
            <span style="width:7px;height:7px;border-radius:50%;background:#4ADE80;display:inline-block"></span>
            {{ $websiteContent['tagline'] ?? 'Internet Service Provider Lokal' }}
        </span>

        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(34px,5vw,54px);line-height:1.08;letter-spacing:-.02em;margin:22px 0 16px;max-width:720px;color:var(--ink)">
            {{ $websiteContent['hero_title'] ?? $tenant->name }}
        </h1>
        <p style="font-size:17px;line-height:1.65;color:var(--ink-soft);max-width:560px;margin:0 0 32px">
            {{ $websiteContent['hero_subtitle'] ?? 'Layanan internet cepat, stabil, dan terjangkau untuk rumah dan bisnis di wilayah Anda.' }}
        </p>

        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a href="{{ route('tenant.packages', $tenant->slug) }}" class="btn btn-primary" style="font-size:14px;padding:13px 26px;text-decoration:none">Lihat Paket</a>
            <a href="{{ route('customer.auth.login', $tenant->slug) }}" class="btn btn-outline" style="font-size:14px;padding:13px 26px;text-decoration:none">Portal Pelanggan</a>
        </div>
    </div>
</section>

<section style="padding:64px 24px">
    <div style="max-width:1080px;margin:0 auto">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px">
            <div class="panel" style="padding:28px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--primary-tint-solid);color:var(--primary);display:flex;align-items:center;justify-content:center;margin-bottom:16px">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
                <h3 style="font-size:15.5px;font-weight:600;margin-bottom:6px">Cepat &amp; Stabil</h3>
                <p style="font-size:13.5px;color:var(--ink-soft);line-height:1.55;margin:0">Koneksi internet berkecepatan tinggi dengan stabilitas yang terjamin setiap saat.</p>
            </div>
            <div class="panel" style="padding:28px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--green-tint);color:var(--green);display:flex;align-items:center;justify-content:center;margin-bottom:16px">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <h3 style="font-size:15.5px;font-weight:600;margin-bottom:6px">Harga Terjangkau</h3>
                <p style="font-size:13.5px;color:var(--ink-soft);line-height:1.55;margin:0">Paket berlangganan dengan harga kompetitif, tanpa biaya tersembunyi.</p>
            </div>
            <div class="panel" style="padding:28px">
                <div style="width:40px;height:40px;border-radius:10px;background:var(--amber-tint);color:var(--amber);display:flex;align-items:center;justify-content:center;margin-bottom:16px">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                </div>
                <h3 style="font-size:15.5px;font-weight:600;margin-bottom:6px">Dukungan 24/7</h3>
                <p style="font-size:13.5px;color:var(--ink-soft);line-height:1.55;margin:0">Tim support siap membantu Anda kapan saja, termasuk via WhatsApp.</p>
            </div>
        </div>
    </div>
</section>

<section style="padding:8px 24px 72px">
    <div style="max-width:1080px;margin:0 auto">
        <div style="display:flex;align-items:end;justify-content:space-between;gap:16px;margin-bottom:28px">
            <div>
                <h2 style="font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:700;margin:0 0 6px">Paket Layanan Kami</h2>
                <p style="font-size:14px;color:var(--ink-soft);margin:0">Pilih paket internet yang sesuai dengan kebutuhan Anda</p>
            </div>
            @if(count($packages) > 3)
            <a href="{{ route('tenant.packages', $tenant->slug) }}" class="btn btn-outline" style="font-size:13px;padding:9px 18px;text-decoration:none;white-space:nowrap">Lihat Semua</a>
            @endif
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px">
            @forelse(array_slice($packages->all(), 0, 3) as $package)
            <div class="panel" style="padding:26px;display:flex;flex-direction:column">
                <div style="margin-bottom:18px">
                    <h3 style="font-size:17px;font-weight:700;margin:0 0 4px">{{ $package->name }}</h3>
                    <p style="font-size:12.5px;color:var(--ink-faint);margin:0">{{ Str::limit($package->description ?? 'Paket internet rumahan', 46) }}</p>
                </div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:30px;font-weight:700;color:{{ $brandColor }}">{{ $package->speed }} <span style="font-size:13px;font-weight:600;color:var(--ink-faint)">Mbps</span></div>
                <div style="margin:14px 0 20px;padding-top:14px;border-top:1px dashed var(--line)">
                    <span style="font-size:22px;font-weight:700">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                    <span style="font-size:12.5px;color:var(--ink-faint)">/bulan</span>
                </div>
                <a href="{{ route('tenant.register', $tenant->slug) }}?package_id={{ $package->id }}" class="btn btn-outline" style="font-size:13px;justify-content:center;margin-top:auto;text-decoration:none">Berlangganan</a>
            </div>
            @empty
            <div class="panel" style="padding:40px;text-align:center;grid-column:1/-1">
                <p style="font-size:14px;color:var(--ink-soft);margin:0">Belum ada paket layanan yang tersedia. Hubungi kami untuk informasi lebih lanjut.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<section style="padding:0 24px 88px">
    <div style="max-width:1080px;margin:0 auto">
        <div class="panel" style="background:var(--ink);border-color:var(--ink);padding:44px 36px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap">
            <div>
                <h2 style="color:#fff;font-family:'Space Grotesk',sans-serif;font-size:23px;font-weight:700;margin:0 0 8px">Sudah jadi pelanggan?</h2>
                <p style="color:#9AA7C7;font-size:14px;margin:0">Masuk ke portal pelanggan untuk cek tagihan dan riwayat pembayaran.</p>
            </div>
            <a href="{{ route('customer.auth.login', $tenant->slug) }}" class="btn btn-primary" style="font-size:14px;padding:13px 26px;text-decoration:none;white-space:nowrap">Buka Portal Pelanggan</a>
        </div>
    </div>
</section>
@endsection
