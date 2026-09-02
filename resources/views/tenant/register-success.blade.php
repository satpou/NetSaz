@extends('layouts.tenant-public')

@php($brandColor = $tenant->brand_color ?: '#00C67E')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<section style="background:linear-gradient(180deg, {{ $brandColor }}12 0%, {{ $brandColor }}04 100%);padding:64px 24px;position:relative;overflow:hidden">
    <div style="max-width:1080px;margin:0 auto;position:relative;z-index:1">
        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(28px,4vw,40px);margin:0;color:var(--ink)">Pendaftaran Berhasil</h1>
    </div>
</section>

<section style="padding:48px 24px 88px">
    <div style="max-width:560px;margin:0 auto">
        <div class="panel" style="padding:36px;text-align:center">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--green-tint);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2.5"><path d="M5 13l4 4 10-10"/></svg>
            </div>

            <h2 style="font-size:20px;font-weight:700;color:var(--ink);margin:0 0 12px">Terima kasih, {{ $customer->name }}!</h2>
            <p style="font-size:14px;color:var(--ink-soft);line-height:1.6;margin:0 0 24px">
                Pendaftaran Anda untuk paket <strong>{{ $package->name }}</strong> ({{ $package->speed }} Mbps) telah kami terima.
            </p>

            <div style="background:var(--bg-alt);border-radius:12px;padding:20px;margin-bottom:24px;text-align:left">
                <div style="font-size:13px;color:var(--ink-faint);margin-bottom:10px;font-weight:500">Langkah selanjutnya:</div>
                <ol style="margin:0;padding-left:18px;font-size:13.5px;color:var(--ink-soft);line-height:1.8">
                    <li>Admin <strong>{{ $tenant->name }}</strong> akan menghubungi Anda via WhatsApp</li>
                    <li>Pembayaran deposit/pemasangan (jika ada)</li>
                    <li>Tim teknis melakukan pemasangan di lokasi Anda</li>
                    <li>Akun diaktifkan dan Anda menerima PIN portal pelanggan</li>
                </ol>
            </div>

            <div style="background:{{ $brandColor }}08;border:1px solid {{ $brandColor }}20;border-radius:12px;padding:16px;margin-bottom:24px">
                <div style="font-size:12px;color:var(--ink-faint);margin-bottom:6px">Paket Anda</div>
                <div style="font-size:16px;font-weight:700;color:var(--ink)">{{ $package->name }} — {{ $package->speed }} Mbps</div>
                <div style="font-size:14px;color:var(--ink-soft);margin-top:2px">Rp{{ number_format($package->price, 0, ',', '.') }}/bulan</div>
            </div>

            @if($tenant->whatsapp_number)
            <a href="https://wa.me/{{ ltrim($tenant->whatsapp_number, '0') }}?text={{ urlencode('Halo, saya ' . $customer->name . ' ingin berlangganan paket ' . $package->name) }}" target="_blank" rel="noopener" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;font-size:14px;text-decoration:none;margin-bottom:12px">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Hubungi via WhatsApp
            </a>
            @endif

            <a href="{{ route('tenant.landing', $tenant->slug) }}" class="btn btn-outline" style="width:100%;justify-content:center;padding:13px;font-size:14px;text-decoration:none">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</section>
@endsection
