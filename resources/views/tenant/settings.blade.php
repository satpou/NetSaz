@php
    $user = Auth::user();
    $host = request()->getHost();
    $tenantDomain = config('app.tenant_domain');
    $isTenantDomain = $host !== $tenantDomain && str_ends_with($host, '.'.$tenantDomain);
    $ts = $isTenantDomain ? str_replace('.'.$tenantDomain, '', $host) : ($user?->tenant?->slug ?? session('tenant_slug') ?? null);
    if ($ts) {
        \Illuminate\Support\Facades\URL::defaults(['tenant_slug' => $ts]);
    }
@endphp
@extends('layouts.app')

@section('title', 'Pengaturan ISP')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Pengaturan ISP</b>
</div>

<x-page-header title="Pengaturan ISP" subtitle="Kelola profil, integrasi payment gateway Midtrans, WhatsApp gateway, dan otomasi billing">
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<form method="POST" action="{{ route('tenant.settings.update', $ts ? ['tenant_slug' => $ts] : []) }}">
    @csrf
    @method('PUT')

    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:20px;margin-bottom:24px">
        <!-- Panel 1: Profil ISP -->
        <div class="panel reveal d1" style="padding:26px 28px">
            <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:16px;display:flex;align-items:center;gap:10px">
                <span style="width:28px;height:28px;border-radius:7px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                Identitas & Branding ISP
            </h3>

            <div style="margin-bottom:16px">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Nama ISP <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>

            <div style="margin-bottom:16px">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Subdomain Aktif</label>
                <input type="text" disabled value="{{ $tenant->slug }}.{{ config('app.tenant_domain') }}" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:14px">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
                <div>
                    <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">No. WhatsApp CS</label>
                    <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $tenant->whatsapp_number) }}" placeholder="081234567890" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                </div>
                <div>
                    <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Warna Tema Brand</label>
                    <div style="display:flex;align-items:center;gap:8px">
                        <input type="color" name="brand_color" value="{{ old('brand_color', $tenant->brand_color ?? '#00D084') }}" style="width:42px;height:42px;border:none;border-radius:8px;cursor:pointer;background:transparent">
                        <span style="font-size:13px;color:var(--ink-soft);font-family:monospace">{{ $tenant->brand_color ?? '#00D084' }}</span>
                    </div>
                </div>
            </div>

            <div style="margin-bottom:16px">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Slogan / Catatan di Footer Invoice</label>
                <input type="text" name="slogan" value="{{ old('slogan', $settings['slogan'] ?? '') }}" placeholder="Contoh: Terima kasih atas kepercayaan Anda menggunakan layanan kami." class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>
        </div>

        <!-- Panel 2: Otomasi Billing & Reminder -->
        <div class="panel reveal d2" style="padding:26px 28px">
            <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:16px;display:flex;align-items:center;gap:10px">
                <span style="width:28px;height:28px;border-radius:7px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                Otomasi Tagihan & Isolir
            </h3>

            <div style="margin-bottom:18px">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Batas Hari Jatuh Tempo Tagihan</label>
                <div style="display:flex;align-items:center;gap:10px">
                    <input type="number" name="invoice_due_days" value="{{ old('invoice_due_days', $settings['invoice_due_days'] ?? 7) }}" min="1" max="30" class="form-input" style="width:100px;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                    <span style="font-size:13.5px;color:var(--ink-soft)">hari setelah tanggal invoice terbit</span>
                </div>
            </div>

            <div style="margin-bottom:20px;padding:14px 16px;background:var(--bg-alt);border-radius:10px;border:1px solid var(--line)">
                <label style="display:flex;align-items:flex-start;gap:12px;cursor:pointer">
                    <input type="checkbox" name="auto_isolate_enabled" value="1" {{ old('auto_isolate_enabled', $settings['auto_isolate_enabled'] ?? true) ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px;margin-top:2px">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--ink)">Otomatis Isolir Pelanggan Menunggak</div>
                        <div style="font-size:12px;color:var(--ink-soft);margin-top:2px">Jika diaktifkan, scheduler pukul 03.00 WIB akan otomatis menonaktifkan secret PPPoE di MikroTik bagi pelanggan yang telah melewati batas jatuh tempo.</div>
                    </div>
                </label>
            </div>
        </div>
    </div>

    <!-- Panel 3: Integrasi Payment Gateway (Midtrans) -->
    <div class="panel reveal d3" style="padding:26px 28px;margin-bottom:24px">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:10px">
            <span style="width:28px;height:28px;border-radius:7px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </span>
            Integrasi Midtrans (Pembayaran Otomatis QRIS & VA Bank)
        </h3>
        <p style="font-size:13px;color:var(--ink-soft);margin-bottom:18px">
            Dapatkan Server Key dan Client Key dari dashboard Midtrans akun Anda di menu <b>Settings &rarr; Access Keys</b>. Sistem akan mengenkripsi kunci rahasia secara aman.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Midtrans Server Key</label>
                <input type="password" name="midtrans_server_key" value="{{ old('midtrans_server_key', $tenant->getSecretSetting('midtrans_server_key')) }}" placeholder="SB-Mid-server-..." class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Midtrans Client Key</label>
                <input type="text" name="midtrans_client_key" value="{{ old('midtrans_client_key', $tenant->getSecretSetting('midtrans_client_key')) }}" placeholder="SB-Mid-client-..." class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            <input type="checkbox" name="midtrans_is_production" value="1" id="midtrans_prod" {{ old('midtrans_is_production', $settings['midtrans_is_production'] ?? false) ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px">
            <label for="midtrans_prod" style="font-size:13.5px;font-weight:600;color:var(--ink);cursor:pointer">Mode Production / Live (Bukan Sandbox)</label>
        </div>
    </div>

    <!-- Panel 4: Integrasi WhatsApp Gateway -->
    <div class="panel reveal d4" style="padding:26px 28px;margin-bottom:24px">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:10px">
            <span style="width:28px;height:28px;border-radius:7px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            WhatsApp Gateway (Kirim Tagihan & Reminder Otomatis)
        </h3>
        <p style="font-size:13px;color:var(--ink-soft);margin-bottom:18px">
            Hubungkan WhatsApp API Anda (Fonnte, Waha, Fonteva) untuk pengiriman notifikasi invoice dan reminder tagihan otomatis.
        </p>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Provider Gateway</label>
                <select name="wa_gateway_provider" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                    <option value="fonnte" {{ ($settings['wa_gateway_provider'] ?? '') == 'fonnte' ? 'selected' : '' }}>Fonnte</option>
                    <option value="waha" {{ ($settings['wa_gateway_provider'] ?? '') == 'waha' ? 'selected' : '' }}>WAHA (WhatsApp HTTP API)</option>
                    <option value="fonteva" {{ ($settings['wa_gateway_provider'] ?? '') == 'fonteva' ? 'selected' : '' }}>Fonteva / Custom Webhook</option>
                </select>
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">API Token / Key</label>
                <input type="password" name="wa_api_token" value="{{ old('wa_api_token', $settings['wa_api_token'] ?? '') }}" placeholder="Token WhatsApp API..." class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;margin-bottom:40px">
        <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:15px;font-weight:700">
            Simpan Semua Pengaturan
        </button>
    </div>
</form>
@endsection
