@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
<style>
    .forgot-split {
        display: flex;
        min-height: 100vh;
    }

    .forgot-left {
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
        .forgot-left {
            display: flex;
            width: 50%;
        }
    }

    .forgot-left-logo {
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

    .forgot-left-inner {
        max-width: 480px;
        width: 100%;
        text-align: center;
    }

    .forgot-left h1 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: clamp(28px, 5vw, 42px);
        font-weight: 700;
        line-height: 1.1;
        color: #fff;
        margin-bottom: 20px;
        letter-spacing: -0.02em;
    }

    .forgot-left p {
        font-size: 17px;
        color: rgba(255,255,255,.75);
        line-height: 1.6;
    }

    .forgot-left-footer {
        position: absolute;
        bottom: 24px;
        right: 32px;
        font-size: 13px;
        color: rgba(255,255,255,.25);
        font-family: 'JetBrains Mono', monospace;
    }

    .forgot-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 24px;
        background: var(--bg);
    }

    .forgot-form-wrap {
        width: 100%;
        max-width: 420px;
    }

    .forgot-form-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .forgot-form-logo img {
        height: 48px;
    }

    .forgot-form-header h2 {
        font-family: 'Space Grotesk', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: var(--ink);
        text-align: center;
        margin-bottom: 8px;
        letter-spacing: -0.02em;
    }

    .forgot-form-header p {
        font-size: 14px;
        color: var(--ink-soft);
        text-align: center;
        margin-bottom: 28px;
        line-height: 1.5;
    }

    .forgot-submit {
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
    }

    .forgot-submit:hover {
        background: var(--primary-dark);
        transform: translateY(-1px);
    }

    .forgot-submit:active {
        transform: translateY(0);
    }

    .forgot-link {
        text-align: center;
        margin-top: 24px;
    }

    .forgot-link a {
        font-size: 14px;
        font-weight: 500;
        color: var(--primary);
        text-decoration: none;
        transition: color .15s;
    }

    .forgot-link a:hover {
        color: var(--primary-dark);
    }

    .forgot-back {
        text-align: center;
        margin-top: 20px;
    }

    .forgot-back a {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--ink-faint);
        text-decoration: none;
        transition: color .15s;
    }

    .forgot-back a:hover {
        color: var(--ink-soft);
    }
</style>

<div class="forgot-split">
    {{-- LEFT: Branding --}}
    <div class="forgot-left">
        <a href="{{ route('landing') }}" class="forgot-left-logo">NetSaz</a>

        <div class="forgot-left-inner">
            <h1>Lupa Password?</h1>
            <p>Jangan khawatir. Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>
        </div>

        <div class="forgot-left-footer">NetSaz</div>
    </div>

    {{-- RIGHT: Form --}}
    <div class="forgot-right">
        <div class="forgot-form-wrap">
            <div class="forgot-form-logo">
                <img src="{{ asset('images/netsaz/NetSaz_logo_transparent.png') }}" alt="NetSaz Logo">
            </div>

            <div class="forgot-form-header">
                <h2>Lupa Password</h2>
                <p>Masukkan email Anda untuk reset password</p>
            </div>

            @if(session('success'))
                <div class="form-error" style="background:var(--green-tint);border-color:var(--green);color:var(--green)" role="alert">
                    {{ session('success') }}
                </div>
            @endif

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

            <form action="{{ route('password.email') }}" method="POST">
                @csrf

                <div style="display:flex;flex-direction:column;gap:18px">
                    <div>
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="admin@isp.com">
                    </div>
                </div>

                <button type="submit" class="forgot-submit" style="margin-top:24px">
                    Kirim Link Reset
                </button>
            </form>

            <div class="forgot-link">
                <p style="font-size:14px;color:var(--ink-soft)">
                    Ingat password?
                    <a href="{{ route('login') }}">Masuk</a>
                </p>
            </div>

            <div class="forgot-back">
                <a href="{{ route('landing') }}">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
