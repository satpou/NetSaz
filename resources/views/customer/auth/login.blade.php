@extends('layouts.guest')

@section('title', 'Login Portal')

@section('content')
<div style="min-height:80vh;display:flex;align-items:center;justify-content:center;padding:40px 20px">
    <div style="width:100%;max-width:420px">
        <div style="text-align:center;margin-bottom:32px">
            <div style="width:48px;height:48px;border-radius:12px;background:var(--primary);display:flex;align-items:center;justify-content:center;font-family:'Space Grotesk';font-weight:700;font-size:22px;color:#fff;margin:0 auto 12px">PB</div>
            <h1 style="font-size:22px;font-weight:700;color:var(--ink);margin-bottom:4px">Portal Pelanggan</h1>
            <p style="font-size:14px;color:var(--ink-soft)">Masuk untuk lihat tagihan dan pembayaran</p>
        </div>

        @if(session('error'))
        <div style="background:var(--red-tint);border:1px solid var(--red);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13.5px;color:var(--red)">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('customer.auth.authenticate') }}" style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:28px">
            @csrf

            <div style="margin-bottom:16px">
                <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">No HP atau Email</label>
                <input type="text" name="phone_or_email" value="{{ old('phone_or_email') }}" required placeholder="0877xxxx atau email@example.com"
                    class="form-input" style="width:100%">
                @error('phone_or_email')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
                @error('tenant')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div style="margin-bottom:20px">
                <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">PIN</label>
                <input type="password" name="pin" required inputmode="numeric" maxlength="6" placeholder="6 digit PIN"
                    class="form-input" style="width:100%;font-family:JetBrains Mono;font-size:14px;letter-spacing:4px">
                @error('pin')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
                <div style="font-size:12px;color:var(--ink-faint);margin-top:4px">PIN dikirim admin via WhatsApp bersama link portal.</div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px 24px;font-size:15px">
                Masuk
            </button>
        </form>

        <div style="text-align:center;margin-top:20px">
            <a href="{{ route('dashboard') }}" style="font-size:13px;color:var(--ink-faint);text-decoration:underline">← Kembali ke NetSaz</a>
        </div>
    </div>
</div>
@endSection
