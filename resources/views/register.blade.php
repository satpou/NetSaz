@php $hideFooter = true; @endphp
@extends('layouts.auth')

@section('title', 'Daftar - NetSaz')

@push('styles')
@vite(['resources/css/register.css'])
@endpush

@section('content')
<div class="login-split">
    {{-- KIRI --}}
    <div class="login-left">
        <a href="{{ route('dashboard') }}" class="login-left-logo">
            <div class="login-left-logo-mark"></div>
            NetSaz
        </a>
        <div class="login-left-inner">
            <h1>Billing Terpusat untuk ISP Anda</h1>
            <p>Kelola pelanggan, tagihan, dan jaringan dalam satu platform. Automasi penuh untuk ISP kecil hingga menengah.</p>
            <div class="login-feature-list">
                <div class="login-feature-item">
                    <div class="login-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="login-feature-title">Billing Otomatis & Prorata</div>
                        <div class="login-feature-desc">Invoice otomatis per siklus bulanan dengan prorata untuk pelanggan baru.</div>
                    </div>
                </div>
                <div class="login-feature-item">
                    <div class="login-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="login-feature-title">Multi-Tenant ISP Siap Pakai</div>
                        <div class="login-feature-desc">Satu instalasi untuk banyak ISP. Data setiap tenant terisolasi penuh.</div>
                    </div>
                </div>
                <div class="login-feature-item">
                    <div class="login-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="login-feature-title">Payment Gateway Terintegrasi</div>
                        <div class="login-feature-desc">Pembayaran online Midtrans & manual. Rekonsiliasi tercatat otomatis.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="login-left-footer">NetSaz</div>
    </div>

    {{-- KANAN --}}
    <div class="login-right">
        <div class="login-form-wrap" style="background:transparent;border:none;box-shadow:none;">
<div class="login-form-logo">
    <img src="{{ asset('images/netsaz/NetSaz_logo_transparent.png') }}" alt="NetSaz" style="height: 48px;">
</div>

            <div class="login-form-header">
                <h2>Daftar Akun Baru</h2>
                <p>Buat akun ISP Anda dalam 1 menit dan langsung masuk ke dashboard.</p>
            </div>

            @if($errors->any())
                <div class="form-error" role="alert">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <ul style="list-style:disc;padding-left:14px;flex:1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('tenant.register.store') }}" method="POST" class="reg-form" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='Memproses...';">
                @csrf

                <div class="reg-section-title">Informasi Usaha</div>

                <div>
                    <label for="company_name" class="form-label">Nama Usaha / Organisasi <span style="color:var(--primary)">*</span></label>
                    <input type="text" name="company_name" id="company_name" value="{{ old('company_name') }}" required
                           autocomplete="organization" class="form-input" placeholder="Contoh: NetCity Indonesia"
                           oninput="var slug=document.getElementById('slug'); if(slug.value==='' || slug.dataset.auto==='1'){slug.value=this.value.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-+|-+$/g,'');slug.dataset.auto='1';}">
                </div>

                <input type="hidden" name="slug" id="slug" value="{{ old('slug') }}">

                <div class="reg-row">
                    <div>
                        <label for="pic_name" class="form-label">Nama PIC <span style="color:var(--primary)">*</span></label>
                        <input type="text" name="pic_name" id="pic_name" value="{{ old('pic_name') }}" required
                               autocomplete="name" class="form-input" placeholder="Nama kontak resmi">
                    </div>
                    <div>
                        <label for="city" class="form-label">Kota / Kabupaten <span style="color:var(--primary)">*</span></label>
                        <input type="text" name="city" id="city" value="{{ old('city') }}" required
                               autocomplete="address-level2" class="form-input" placeholder="Contoh: Jakarta Selatan">
                    </div>
                </div>

                <div class="reg-section-title">Kontak</div>

                <div class="reg-row">
                    <div>
                        <label for="whatsapp_number" class="form-label">Nomor WhatsApp <span style="color:var(--primary)">*</span></label>
                        <input type="tel" name="whatsapp_number" id="whatsapp_number" value="{{ old('whatsapp_number') }}" required
                               autocomplete="tel" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                    <div>
                        <label for="email" class="form-label">Email <span style="color:var(--primary)">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               autocomplete="email" class="form-input" placeholder="admin@netcity.com">
                    </div>
                </div>

                <div class="reg-section-title">Keamanan Akun</div>

                <div class="reg-row">
                    <div>
                        <label for="password" class="form-label">Password <span style="color:var(--primary)">*</span></label>
                        <input type="password" name="password" id="password" required
                               autocomplete="new-password" class="form-input" placeholder="Minimal 8 karakter">
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Ulangi Password <span style="color:var(--primary)">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               autocomplete="new-password" class="form-input" placeholder="Ketik ulang password">
                    </div>
                </div>

                <div class="reg-section-title">Detail Layanan</div>

                <div class="reg-row">
                    <div>
                        <label for="customers" class="form-label">Jumlah Pelanggan <span style="color:var(--primary)">*</span></label>
                        <select name="customers" id="customers" required class="form-input">
                            <option value="" disabled selected>Pilih jumlah</option>
                            <option value="<50">Kurang dari 50</option>
                            <option value="50-100">50 – 100 pelanggan</option>
                            <option value="100-300">100 – 300 pelanggan</option>
                            <option value="300-500">300 – 500 pelanggan</option>
                            <option value="500-1000">500 – 1.000 pelanggan</option>
                            <option value="1000+">Lebih dari 1.000</option>
                        </select>
                    </div>
                    <div>
                        <label for="mitra" class="form-label">Jumlah Mitra (Opsional)</label>
                        <select name="mitra" id="mitra" class="form-input">
                            <option value="" selected>Tidak ada / belum tahu</option>
                            <option value="1-3">1 – 3 mitra</option>
                            <option value="4-10">4 – 10 mitra</option>
                            <option value="10+">Lebih dari 10</option>
                        </select>
                    </div>
                </div>

                <div class="reg-section-title">Paket Langganan <span style="color:var(--primary)">*</span></div>

                <div class="reg-pkg-header">
                    <p class="reg-section-desc">Pilih paket sesuai jumlah pelanggan Anda.</p>
                    <a href="{{ route('price') }}" class="reg-pkg-see">Lihat detail paket</a>
                </div>

                <div class="reg-pkgs">
                    @php
                    $icons = [
                        'rocket'   => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c1.26-1.5 2-5 2-5s-3.74.5-5 2c-1.26 1.5-2 5-2 5z"/><path d="M15.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c1.26-1.5 2-5 2-5s-3.74.5-5 2c-1.26 1.5-2 5-2 5z"/></svg>',
                        'chart'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>',
                        'monitor'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>',
                        'trending' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 7l-5 5-5-5"/></svg>',
                        'globe'    => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
                        'building' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
                    ];
                    @endphp
                    @foreach (config('pricing_plans') as $plan)
                    <div class="paket-option">
                        <input type="radio" name="package" value="{{ $plan['key'] }}" id="paket-{{ $plan['key'] }}" required {{ $loop->first ? 'required' : '' }}>
                        <label for="paket-{{ $plan['key'] }}">
                            <div class="paket-radio"><div class="paket-radio-dot"></div></div>
                            <div class="paket-body">
                                <div class="paket-main">
                                    <span class="paket-icon">{!! $icons[$plan['icon_key']] ?? '' !!}</span>
                                    <span class="paket-nama">{{ $plan['name'] }}</span>
                                    @if ($plan['badge'])
                                    <span class="paket-badge">{{ $plan['badge'] }}</span>
                                    @endif
                                </div>
                                <span class="paket-desc">{{ $plan['description'] }}</span>
                            </div>
                            <div class="paket-right">
                                <span class="paket-harga">{{ $plan['price'] }}</span>
                                <span class="paket-kuota">{{ $plan['kuota'] }}</span>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>

                <button type="submit" class="reg-submit">Daftar Sekarang →</button>
            </form>

            <div class="reg-back">
                <a href="{{ route('dashboard') }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>

                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection