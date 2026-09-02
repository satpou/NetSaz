@php $hideFooter = true; @endphp
@extends('layouts.auth')

@section('title', 'Masuk ke NetSaz')

@push('styles')
<style>
  /* Login page overrides */
  body {
    background: var(--bg);
    color: var(--ink);
    font-family: 'Inter', sans-serif;
  }

  .login-split {
    display: flex;
    min-height: 100vh;
  }

  /* LEFT: Branding column */
  .login-left {
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
    .login-left {
      display: flex;
      width: 50%;
    }
  }

  .login-left-logo {
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
  }

  .login-left-logo-mark {
    position: relative;
    width: 40px;
    height: 40px;
    background: #fff;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .login-left-logo-mark::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 12px;
    border: 2.5px solid var(--primary);
  }

  .login-left-logo-mark::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 12px;
    height: 12px;
    background: var(--primary);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    animation: pulse-dot 2.2s ease-in-out infinite;
  }

  @keyframes pulse-dot {
    0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,.35); }
    50% { box-shadow: 0 0 0 10px rgba(255,255,255,0); }
  }

  .login-left-inner {
    max-width: 480px;
    width: 100%;
    text-align: center;
  }

  .login-left h1 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: clamp(32px, 5vw, 48px);
    font-weight: 700;
    line-height: 1.1;
    color: #fff;
    margin-bottom: 20px;
    letter-spacing: -0.02em;
  }

  .login-left p {
    font-size: 17px;
    color: rgba(255,255,255,.75);
    line-height: 1.6;
    margin-bottom: 40px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
  }

  .login-feature-list {
    text-align: left;
    max-width: 400px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .login-feature-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
  }

  .login-feature-icon {
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

  .login-feature-icon svg {
    width: 14px;
    height: 14px;
    color: #fff;
  }

  .login-feature-title {
    font-weight: 600;
    color: #fff;
    font-size: 15px;
    margin-bottom: 4px;
  }

  .login-feature-desc {
    font-size: 13.5px;
    color: rgba(255,255,255,.65);
    line-height: 1.5;
  }

  .login-left-footer {
    position: absolute;
    bottom: 24px;
    right: 32px;
    font-size: 13px;
    color: rgba(255,255,255,.25);
    font-family: 'JetBrains Mono', monospace;
  }

  /* RIGHT: Form column */
  .login-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px 24px;
    background: var(--bg);
  }

  .login-form-wrap {
    width: 100%;
    max-width: 420px;
  }

  .login-form-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 28px;
  }

  .login-form-logo-mark {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--primary), #5CFFBF);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0,198,126,.3);
  }

  .login-form-logo-mark span {
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    font-size: 20px;
    color: #fff;
  }

  .login-form-logo-name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--ink);
  }

  .login-form-header h2 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 26px;
    font-weight: 700;
    color: var(--ink);
    text-align: center;
    margin-bottom: 8px;
    letter-spacing: -0.02em;
  }

  .login-form-header p {
    font-size: 14px;
    color: var(--ink-soft);
    text-align: center;
    margin-bottom: 28px;
    line-height: 1.5;
  }

  /* Override design-system form styles for login */
  .login-form .form-input {
    padding: 14px 16px;
    border-radius: 10px;
    font-size: 14.5px;
  }

  .login-form .form-label {
    font-size: 13.5px;
    margin-bottom: 8px;
  }

  .login-form .form-error {
    font-size: 13.5px;
  }

  /* Password toggle */
  .password-field {
    position: relative;
  }

  .password-toggle {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--ink-faint);
    cursor: pointer;
    padding: 4px;
    transition: color .15s;
  }

  .password-toggle:hover {
    color: var(--ink-soft);
  }

  /* Checkbox styling */
  .form-checkbox {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1.5px solid var(--line);
    accent-color: var(--primary);
    cursor: pointer;
  }

  /* Remember + Forgot link */
  .login-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .login-options label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13.5px;
    color: var(--ink-soft);
    cursor: pointer;
  }

  .login-options a {
    font-size: 13.5px;
    font-weight: 500;
    color: var(--primary);
  }

  .login-options a:hover {
    color: var(--primary-dark);
  }

  /* Submit button */
  .login-submit {
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
    box-shadow: 0 1px 2px rgba(16,24,53,.08), 0 8px 16px -8px rgba(0,198,126,.45);
    transition: background .15s, transform .15s, box-shadow .15s;
    position: relative;
    overflow: hidden;
  }

  .login-submit:hover {
    background: var(--primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(16,24,53,.08), 0 12px 24px -10px rgba(0,198,126,.55);
  }

  .login-submit:active {
    transform: translateY(0);
  }

  .login-submit:disabled {
    opacity: 0.5;
    cursor: wait;
    transform: none;
  }

  /* Divider */
  .login-divider {
    position: relative;
    margin: 24px 0;
  }

  .login-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: var(--line);
  }

  .login-divider span {
    position: relative;
    display: flex;
    justify-content: center;
    font-size: 12.5px;
    color: var(--ink-faint);
    background: var(--bg);
    padding: 0 12px;
  }

  /* Register link */
  .login-register-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 14px 24px;
    border: 2px solid var(--primary);
    border-radius: 10px;
    font-size: 14.5px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    color: var(--primary);
    background: transparent;
    cursor: pointer;
    transition: background .15s, color .15s;
  }

  .login-register-btn:hover {
    background: var(--primary-tint);
    color: var(--primary-dark);
  }

  /* Back to home */
  .login-back {
    text-align: center;
    margin-top: 20px;
  }

  .login-back a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: var(--ink-faint);
    transition: color .15s;
  }

  .login-back a:hover {
    color: var(--ink-soft);
  }

  /* Spinner */
  .login-spinner {
    display: none;
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  .login-submit:disabled .login-spinner {
    display: inline-block;
  }
</style>
@endpush

@section('content')
<div class="login-split">
    {{-- KOLOM KIRI: Branding --}}
    <div class="login-left">
<a href="{{ route('landing') }}" class="login-left-logo" style="font-family:'Space Grotesk',sans-serif;font-weight:700;font-size:22px;color:#fff;text-decoration:none;">
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

    {{-- KOLOM KANAN: Form --}}
    <div class="login-right">
        <div class="login-form-wrap">
            <div class="login-form-logo">
                <img src="{{ asset('images/netsaz/NetSaz_logo_transparent.png') }}" alt="NetSaz Logo" style="height: 48px;">
            </div>

            <div class="login-form-header">
                <h2>Selamat Datang Kembali</h2>
                <p>Masuk ke akun NetSaz Anda untuk mengelola pelanggan, tagihan, dan pembayaran.</p>
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

            <form action="{{ url()->current() }}" method="POST" class="login-form" onsubmit="var b=this.querySelector('button[type=submit]');b.disabled=true;b.textContent='Memproses...';">
                @csrf

                <div style="display:flex;flex-direction:column;gap:18px">
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', request('email')) }}"
                               required
                               autocomplete="email"
                               class="form-input"
                               placeholder="admin@isp.com"
                               autofocus>
                    </div>

                    <div>
                        <label for="password" class="form-label">Password</label>
                        <div class="password-field">
                            <input type="password"
                                   name="password"
                                   id="password"
                                   required
                                   autocomplete="current-password"
                                   class="form-input"
                                   style="padding-right:44px"
                                   placeholder="Password">
                            <button type="button" class="password-toggle" onclick="var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';" aria-label="Tampilkan/Sembunyikan password">
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="login-options" style="margin-top:16px">
                    <label>
                        <input type="checkbox" name="remember" id="remember" class="form-checkbox">
                        Ingat saya
                    </label>
                    <a href="{{ route('password.request') }}">Lupa password?</a>
                </div>

                <button type="submit" class="login-submit">
                    <span style="display:flex;align-items:center;justify-content:center;gap:10px">
                        <span class="login-spinner"></span>
                        <span>Masuk</span>
                    </span>
                </button>
            </form>

            <div class="login-divider"><span>Belum punya akun NetSaz untuk ISP Anda?</span></div>

            <a href="{{ route('register') }}" class="login-register-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Gabung Sekarang
            </a>

            <div class="login-back">
                <a href="{{ route('landing') }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection