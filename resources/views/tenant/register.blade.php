@extends('layouts.tenant-public')

@php($brandColor = $tenant->brand_color ?: '#00C67E')

@section('title', 'Daftar Berlangganan')

@section('content')
<section style="background:linear-gradient(180deg, {{ $brandColor }}12 0%, {{ $brandColor }}04 100%);padding:64px 24px;position:relative;overflow:hidden">
    <div style="position:absolute;top:-100px;right:-60px;width:320px;height:320px;border-radius:50%;background:{{ $brandColor }};opacity:.04;pointer-events:none"></div>
    <div style="max-width:1080px;margin:0 auto;position:relative;z-index:1">
        <h1 style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:clamp(28px,4vw,40px);margin:0 0 10px;color:var(--ink)">Daftar Berlangganan</h1>
        <p style="font-size:15.5px;color:var(--ink-soft);margin:0">Isi data diri Anda untuk memulai langganan</p>
    </div>
</section>

<section style="padding:48px 24px 88px">
    <div style="max-width:680px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">

        {{-- Registration Form --}}
        <div class="panel" style="padding:28px;grid-column:1">
            <h2 style="font-size:16px;font-weight:600;margin:0 0 20px;color:var(--ink)">Data Diri</h2>

            @if ($errors->any())
            <div style="background:var(--red-tint);border:1px solid var(--red);border-radius:10px;padding:12px 16px;margin-bottom:20px">
                @foreach ($errors->all() as $error)
                    <div style="font-size:13px;color:var(--red);line-height:1.5">{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form action="{{ route('tenant.register.process', $tenant->slug) }}" method="POST" style="display:flex;flex-direction:column;gap:16px">
                @csrf

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">Nama Lengkap <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" style="width:100%" placeholder="Nama sesuai KTP">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">No. WhatsApp <span style="color:var(--red)">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required class="form-input" style="width:100%" placeholder="08xxxxxxxxxx">
                    <p style="font-size:11.5px;color:var(--ink-faint);margin-top:4px">Untuk konfirmasi dan notifikasi langganan</p>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">Email <span style="color:var(--ink-faint);font-weight:400">(opsional)</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" style="width:100%" placeholder="email@contoh.com">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">Alamat Pemasangan <span style="color:var(--red)">*</span></label>
                    <textarea name="address" rows="3" required class="form-input" style="width:100%;resize:vertical" placeholder="Alamat lengkap lokasi pemasangan">{{ old('address') }}</textarea>
                </div>

                <input type="hidden" name="package_id" value="{{ $selectedPackage?->id }}">

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;font-size:14px;margin-top:4px">
                    Kirim Pendaftaran
                </button>
            </form>
        </div>

        {{-- Package Summary --}}
        <div style="grid-column:2">
            @if($selectedPackage)
            <div class="panel" style="padding:24px;position:sticky;top:90px">
                <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px">Paket Dipilih</div>

                <h3 style="font-size:18px;font-weight:700;margin:0 0 4px;color:var(--ink)">{{ $selectedPackage->name }}</h3>
                <p style="font-size:13px;color:var(--ink-faint);margin:0 0 16px">{{ Str::limit($selectedPackage->description ?? 'Paket internet', 60) }}</p>

                <div style="font-family:'JetBrains Mono',monospace;font-size:28px;font-weight:700;color:{{ $brandColor }};margin-bottom:4px">
                    {{ $selectedPackage->speed }} <span style="font-size:13px;color:var(--ink-faint)">Mbps</span>
                </div>

                <div style="padding-top:14px;border-top:1px dashed var(--line);margin-top:14px">
                    <span style="font-size:22px;font-weight:700;color:var(--ink)">Rp {{ number_format($selectedPackage->price, 0, ',', '.') }}</span>
                    <span style="font-size:12.5px;color:var(--ink-faint)">/bulan</span>
                </div>

                @if($selectedPackage->is_taxable)
                <p style="font-size:12px;color:var(--ink-faint);margin-top:8px">*Harga belum termasuk PPN 11%</p>
                @endif

                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--line)">
                    <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">Yang Anda dapatkan</div>
                    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;font-size:13px;color:var(--ink-soft)">
                        <li style="display:flex;align-items:center;gap:8px">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="3"><path d="M5 13l4 4 10-10"/></svg>
                            Unlimited data
                        </li>
                        <li style="display:flex;align-items:center;gap:8px">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="3"><path d="M5 13l4 4 10-10"/></svg>
                            Dukungan teknis 24/7
                        </li>
                        <li style="display:flex;align-items:center;gap:8px">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="3"><path d="M5 13l4 4 10-10"/></svg>
                            Portal pelanggan online
                        </li>
                    </ul>
                </div>
            </div>
            @else
            <div class="panel" style="padding:24px">
                <p style="font-size:14px;color:var(--ink-soft);margin:0">Pilih paket terlebih dahulu dari <a href="{{ route('tenant.packages', $tenant->slug) }}" style="color:var(--primary)">halaman paket</a>.</p>
            </div>
            @endif
        </div>

    </div>
</section>
@endsection
