@extends('layouts.tenant-public')

@php($brandColor = $tenant->brand_color ?: '#2451FF')

@section('title', 'Paket Layanan')

@section('content')
<section style="background:linear-gradient(180deg, {{ $brandColor }}12 0%, {{ $brandColor }}04 100%);padding:64px 24px;position:relative;overflow:hidden">
    <div style="position:absolute;top:-100px;right:-60px;width:320px;height:320px;border-radius:50%;background:{{ $brandColor }};opacity:.04;pointer-events:none"></div>
    <div style="max-width:1080px;margin:0 auto;position:relative;z-index:1">
        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(28px,4vw,40px);margin:0 0 10px;color:var(--ink)">Paket Layanan {{ $tenant->name }}</h1>
        <p style="font-size:15.5px;color:var(--ink-soft);margin:0">Pilih paket internet yang sesuai dengan kebutuhan Anda</p>
    </div>
</section>

<section style="padding:56px 24px 88px">
    <div style="max-width:1080px;margin:0 auto">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;align-items:stretch">
            @forelse($packages as $package)
            <div class="panel" style="padding:28px;display:flex;flex-direction:column">
                <h3 style="font-size:18px;font-weight:700;margin:0 0 6px">{{ $package->name }}</h3>
                <p style="font-size:13px;color:var(--ink-faint);margin:0 0 20px;min-height:34px">{{ Str::limit($package->description ?? 'Paket internet', 70) }}</p>

                <div>
                    <span style="font-size:32px;font-weight:700;font-family:'JetBrains Mono',monospace">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                    <span style="font-size:12.5px;color:var(--ink-faint)">/bulan</span>
                </div>

                <div style="margin:18px 0;padding-top:18px;border-top:1px dashed var(--line)">
                    <div style="font-family:'JetBrains Mono',monospace;font-size:24px;font-weight:700;color:{{ $brandColor }}">{{ $package->speed }} <span style="font-size:12px;color:var(--ink-faint)">Mbps</span></div>
                </div>

                <ul style="list-style:none;margin:0 0 22px;padding:0;display:flex;flex-direction:column;gap:10px;font-size:13.5px;color:var(--ink-soft)">
                    <li style="display:flex;align-items:center;gap:9px">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="3" style="flex-shrink:0"><path d="M5 13l4 4 10-10"/></svg>
                        Unlimited data
                    </li>
                    <li style="display:flex;align-items:center;gap:9px">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="3" style="flex-shrink:0"><path d="M5 13l4 4 10-10"/></svg>
                        Dukungan teknis 24/7
                    </li>
                    @if($package->is_taxable)
                    <li style="font-size:12px;color:var(--ink-faint)">*Harga belum termasuk PPN</li>
                    @endif
                </ul>

                <a href="{{ route('tenant.register', $tenant->slug) }}?package_id={{ $package->id }}" class="btn btn-primary" style="font-size:13.5px;justify-content:center;margin-top:auto;text-decoration:none">Berlangganan Sekarang</a>
            </div>
            @empty
            <div class="panel" style="padding:48px;text-align:center;grid-column:1/-1">
                <p style="font-size:14px;color:var(--ink-soft);margin:0">Belum ada paket layanan yang tersedia. Hubungi support untuk informasi lebih lanjut.</p>
            </div>
            @endforelse
        </div>

        <div class="panel" style="margin-top:36px;background:var(--ink);border-color:var(--ink);padding:40px 36px;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap">
            <div>
                <h2 style="color:#fff;font-family:'Space Grotesk',sans-serif;font-size:21px;font-weight:700;margin:0 0 8px">Sudah memiliki akun?</h2>
                <p style="color:#9AA7C7;font-size:14px;margin:0">Login ke portal pelanggan untuk mengelola langganan Anda</p>
            </div>
            <a href="{{ route('customer.auth.login', $tenant->slug) }}" class="btn btn-primary" style="font-size:14px;padding:13px 26px;text-decoration:none;white-space:nowrap">Login Sekarang</a>
        </div>
    </div>
</section>
@endsection
