@extends('layouts.auth')

@section('title', 'Daftar di NetSaz')

@section('content')
<style>
    .register-split {
        display: flex;
        min-height: 100vh;
    }

    .register-left {
        display: none;
        width: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 40px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--ink) 100%);
    }

    @media (min-width: 768px) {
        .register-left {
            display: flex;
            width: 50%;
        }
    }

    .register-left-logo {
        position: absolute;
        top: 32px;
        left: 32px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
        font-size: 20px;
        color: #fff;
        text-decoration: none;
    }

    .register-left-inner {
        max-width: 480px;
        width: 100%;
        text-align: center;
    }

    .register-left h1 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: clamp(28px, 5vw, 42px);
        font-weight: 700;
        line-height: 1.1;
        color: #fff;
        margin-bottom: 20px;
        letter-spacing: -0.02em;
    }

    .register-left p {
        font-size: 17px;
        color: rgba(255,255,255,.75);
        line-height: 1.6;
        margin-bottom: 40px;
    }

    .register-feature-list {
        text-align: left;
        max-width: 400px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .register-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .register-feature-icon {
        flex-shrink: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,.2);
        border: 1px solid rgba(255,255,255,.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
    }

    .register-feature-icon svg {
        width: 14px;
        height: 14px;
        color: #fff;
    }

    .register-feature-title {
        font-weight: 600;
        color: #fff;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .register-feature-desc {
        font-size: 13.5px;
        color: rgba(255,255,255,.65);
        line-height: 1.5;
    }

    .register-left-footer {
        position: absolute;
        bottom: 24px;
        right: 32px;
        font-size: 13px;
        color: rgba(255,255,255,.25);
        font-family: 'JetBrains Mono', monospace;
    }

    .register-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 24px;
        background: var(--bg);
    }

    .register-form-wrap {
        width: 100%;
        max-width: 420px;
    }

    .register-form-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .register-form-logo img {
        height: 48px;
    }

    .register-form-header h2 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--ink);
        text-align: center;
        margin-bottom: 8px;
        letter-spacing: -0.02em;
    }

    .register-form-header p {
        font-size: 14px;
        color: var(--ink-soft);
        text-align: center;
        margin-bottom: 28px;
        line-height: 1.5;
    }

    .register-submit {
        width: 100%;
        padding: 14px 24px;
        background: var(--primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(16,24,53,.08), 0 8px 16px -8px rgba(36,81,255,.45);
        transition: background .15s, transform .15s, box-shadow .15s;
    }

    .register-submit:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .register-submit:active {
        transform: translateY(0);
    }

    .register-link {
        text-align: center;
        margin-top: 24px;
    }

    .register-link a {
        font-size: 14px;
        font-weight: 500;
        color: var(--primary);
        text-decoration: none;
        transition: color .15s;
    }

    .register-link a:hover {
        color: var(--primary-dark);
    }

    .register-back {
        text-align: center;
        margin-top: 20px;
    }

    .register-back a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--ink-faint);
        text-decoration: none;
        transition: color .15s;
    }

    .register-back a:hover {
        color: var(--ink-soft);
    }
</style>

<div class="register-split">
    {{-- LEFT: Branding --}}
    <div class="register-left">
        <a href="{{ route('landing') }}" class="register-left-logo">NetSaz</a>

        <div class="register-left-inner">
            <h1>Mulai Kelola ISP Anda</h1>
            <p>Buat akun gratis dan mulai mengelola pelanggan, tagihan, dan jaringan dalam hitungan menit.</p>

            <div class="register-feature-list">
                <div class="register-feature-item">
                    <div class="register-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="register-feature-title">Setup 5 Menit</div>
                        <div class="register-feature-desc">Daftar, masukkan data ISP, dan langsung mulai beroperasi.</div>
                    </div>
                </div>
                <div class="register-feature-item">
                    <div class="register-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="register-feature-title">Gratis Selama Trial</div>
                        <div class="register-feature-desc">Coba selama 14 hari tanpa kartu kredit.</div>
                    </div>
                </div>
                <div class="register-feature-item">
                    <div class="register-feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="register-feature-title">Support Langsung</div>
                        <div class="register-feature-desc">Tim kami siap membantu via WhatsApp dan email.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="register-left-footer">NetSaz</div>
    </div>

    {{-- RIGHT: Form --}}
    <div class="register-right">
        <div class="register-form-wrap">
            <div class="register-form-logo">
                <img src="{{ asset('images/netsaz/NetSaz_logo_transparent.png') }}" alt="NetSaz Logo">
            </div>

            <div class="register-form-header">
                <h2>Daftar Sekarang</h2>
                <p>Buat akun gratis untuk mulai menggunakan layanan kami</p>
            </div>

            @if($errors->any())
                <div class="form-error" role="alert">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <ul style="list-style:disc;padding-left:16px;flex:1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('tenant.register.store') }}" method="POST">
                @csrf

                <div style="display:flex;flex-direction:column;gap:18px">
                    <div>
                        <label for="company_name" class="form-label">Nama Perusahaan / ISP</label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required class="form-input" placeholder="Contoh: NetCity Indonesia">
                    </div>

                    <div>
                        <label for="email" class="form-label">Email Admin</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="admin@netcity.com">
                    </div>

                    <div>
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" required class="form-input" placeholder="Min. 8 karakter">
                        <p style="font-size:12px;color:var(--ink-faint);margin-top:6px">Minimal 8 karakter</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="form-input" placeholder="Ulangi password">
                    </div>

                    <div>
                        <label for="whatsapp_number" class="form-label">No. WhatsApp <span style="color:var(--ink-faint)">(opsional)</span></label>
                        <input type="text" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number') }}" class="form-input" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <button type="submit" class="register-submit" style="margin-top:24px">
                    Daftar Sekarang
                </button>
            </form>

            <div class="register-link">
                <p style="font-size:14px;color:var(--ink-soft)">
                    Sudah punya akun?
                    <a href="{{ route('login') }}">Masuk</a>
                </p>
            </div>

            <div class="register-back">
                <a href="{{ route('landing') }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
