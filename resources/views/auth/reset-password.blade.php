@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<style>
    .reset-split {
        display: flex;
        min-height: 100vh;
    }

    .reset-left {
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
        .reset-left {
            display: flex;
            width: 50%;
        }
    }

    .reset-left-logo {
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

    .reset-left-inner {
        max-width: 480px;
        width: 100%;
        text-align: center;
    }

    .reset-left h1 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: clamp(28px, 5vw, 42px);
        font-weight: 700;
        line-height: 1.1;
        color: #fff;
        margin-bottom: 20px;
        letter-spacing: -0.02em;
    }

    .reset-left p {
        font-size: 17px;
        color: rgba(255,255,255,.75);
        line-height: 1.6;
    }

    .reset-left-footer {
        position: absolute;
        bottom: 24px;
        right: 32px;
        font-size: 13px;
        color: rgba(255,255,255,.25);
        font-family: 'JetBrains Mono', monospace;
    }

    .reset-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 24px;
        background: var(--bg);
    }

    .reset-form-wrap {
        width: 100%;
        max-width: 420px;
    }

    .reset-form-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .reset-form-logo img {
        height: 48px;
    }

    .reset-form-header h2 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--ink);
        text-align: center;
        margin-bottom: 8px;
        letter-spacing: -0.02em;
    }

    .reset-form-header p {
        font-size: 14px;
        color: var(--ink-soft);
        text-align: center;
        margin-bottom: 28px;
        line-height: 1.5;
    }

    .reset-submit {
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

    .reset-submit:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .reset-submit:active {
        transform: translateY(0);
    }

    .reset-back {
        text-align: center;
        margin-top: 20px;
    }

    .reset-back a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--ink-faint);
        text-decoration: none;
        transition: color .15s;
    }

    .reset-back a:hover {
        color: var(--ink-soft);
    }
</style>

<div class="reset-split">
    {{-- LEFT: Branding --}}
    <div class="reset-left">
        <a href="{{ route('landing') }}" class="reset-left-logo">NetSaz</a>

        <div class="reset-left-inner">
            <h1>Password Baru</h1>
            <p>Masukkan password baru Anda untuk mengamankan akun NetSaz.</p>
        </div>

        <div class="reset-left-footer">NetSaz</div>
    </div>

    {{-- RIGHT: Form --}}
    <div class="reset-right">
        <div class="reset-form-wrap">
            <div class="reset-form-logo">
                <img src="{{ asset('images/netsaz/NetSaz_logo_transparent.png') }}" alt="NetSaz Logo">
            </div>

            <div class="reset-form-header">
                <h2>Reset Password</h2>
                <p>Masukkan password baru Anda</p>
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

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div style="display:flex;flex-direction:column;gap:18px">
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required class="form-input" placeholder="admin@isp.com">
                    </div>

                    <div>
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" id="password" name="password" required class="form-input" placeholder="Min. 8 karakter">
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="form-input" placeholder="Ulangi password">
                    </div>
                </div>

                <button type="submit" class="reset-submit" style="margin-top:24px">
                    Reset Password
                </button>
            </form>

            <div class="reset-back">
                <a href="{{ route('login') }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
