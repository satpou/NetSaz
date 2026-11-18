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

<x-page-header title="Pengaturan ISP" subtitle="Kelola profil, integrasi payment gateway Midtrans, WhatsApp gateway (Open-WA), dan otomasi billing">
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background:rgba(239, 68, 68, 0.1);border:1px solid #EF4444;color:#EF4444;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
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
                <input type="text" disabled value="{{ $tenant->slug }}.{{ config('app.tenant_domain') }}" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:14px;cursor:not-allowed">
            </div>

            <div style="margin-bottom:16px">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Nomor WhatsApp CS / Admin</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $tenant->whatsapp_number) }}" placeholder="Contoh: 081234567890" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>

            <div style="margin-bottom:16px">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Slogan / Tagline ISP</label>
                <input type="text" name="slogan" value="{{ old('slogan', $settings['slogan'] ?? '') }}" placeholder="Internet Cepat, Stabil, & Terpercaya" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Warna Utama Brand</label>
                <div style="display:flex;align-items:center;gap:12px">
                    <input type="color" name="brand_color" value="{{ old('brand_color', $tenant->brand_color ?? '#0071E3') }}" style="width:48px;height:42px;border:none;border-radius:8px;cursor:pointer;background:transparent">
                    <span style="font-size:13px;color:var(--ink-soft)">Dipakai pada invoice, portal pelanggan, dan struk bayar.</span>
                </div>
            </div>
        </div>

        <!-- Panel 2: Otomasi Billing -->
        <div class="panel reveal d2" style="padding:26px 28px">
            <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:16px;display:flex;align-items:center;gap:10px">
                <span style="width:28px;height:28px;border-radius:7px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                </span>
                Otomasi & Siklus Tagihan
            </h3>

            <div style="margin-bottom:16px">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Masa Jatuh Tempo Standar (Hari)</label>
                <input type="number" name="invoice_due_days" min="1" max="30" value="{{ old('invoice_due_days', $settings['invoice_due_days'] ?? 7) }}" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Batas waktu bayar sejak invoice terbit otomatis setiap bulannya.</div>
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

    <!-- Panel 4: Integrasi WhatsApp Gateway (Termasuk Open-WA) -->
    <div class="panel reveal d4" style="padding:26px 28px;margin-bottom:24px" x-data="{ provider: '{{ old('wa_gateway_provider', $settings['wa_gateway_provider'] ?? 'open_wa') }}', statusLoading: false, statusResult: null }">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:10px">
            <span style="width:28px;height:28px;border-radius:7px;background:rgba(16, 185, 129, 0.15);color:#10B981;display:inline-flex;align-items:center;justify-content:center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
            </span>
            WhatsApp Gateway (Kirim Tagihan & Reminder Otomatis)
        </h3>
        <p style="font-size:13px;color:var(--ink-soft);margin-bottom:18px">
            Hubungkan WhatsApp API Anda untuk pengiriman otomatis notifikasi invoice baru, pengingat jatuh tempo H-3 & H-1, kredensial portal pelanggan, dan bukti pembayaran.
        </p>

        <!-- Open-WA Highlight Banner -->
        <div style="background:rgba(16, 185, 129, 0.08);border:1px solid rgba(16, 185, 129, 0.25);border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:flex-start;justify-content:space-between;gap:14px">
            <div style="display:flex;gap:12px">
                <span style="color:#10B981;margin-top:2px">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                </span>
                <div>
                    <div style="font-size:13.5px;font-weight:700;color:var(--ink)">Dukungan Resmi: Open-WA (wa-automate)</div>
                    <div style="font-size:12.5px;color:var(--ink-soft);margin-top:3px;line-height:1.5">
                        NetSaz mendukung integrasi penuh dengan <a href="https://www.open-wa.org/" target="_blank" rel="noopener" style="color:#10B981;font-weight:600;text-decoration:underline">Open-WA (wa-automate)</a>, gateway WhatsApp open-source yang dapat Anda host sendiri (Self-Hosted) secara gratis tanpa biaya langganan bulanan.
                    </div>
                </div>
            </div>
            <a href="https://www.open-wa.org/" target="_blank" rel="noopener" class="btn btn-ghost" style="white-space:nowrap;font-size:12px;padding:6px 12px;border:1px solid rgba(16, 185, 129, 0.4);color:#10B981">
                Buka Docs Open-WA &rarr;
            </a>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Provider Gateway</label>
                <select name="wa_gateway_provider" x-model="provider" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                    <option value="open_wa">Open-WA (wa-automate - www.open-wa.org) [Self-Hosted]</option>
                    <option value="fonnte">Fonnte (Cloud API)</option>
                    <option value="waha">WAHA (WhatsApp HTTP API)</option>
                    <option value="callmebot">CallMeBot / Webhook Custom</option>
                </select>
            </div>

            <!-- Server URL for Open-WA / WAHA -->
            <div x-show="provider === 'open_wa' || provider === 'waha' || provider === 'callmebot'">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">
                    <span x-show="provider === 'open_wa'">Endpoint URL Open-WA</span>
                    <span x-show="provider === 'waha'">Endpoint URL WAHA</span>
                    <span x-show="provider === 'callmebot'">Webhook API URL</span>
                </label>
                <input type="text" name="wa_api_url" value="{{ old('wa_api_url', $settings['wa_api_url'] ?? 'http://localhost:2785') }}" placeholder="http://localhost:2785 atau https://wa.domain-anda.com" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <div style="font-size:11.5px;color:var(--ink-soft);margin-top:4px">Contoh port default Open-WA Easy API: <code>http://localhost:2785</code></div>
            </div>

            <!-- API Key / Token -->
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">
                    <span x-show="provider === 'open_wa'">API Key / Secret Token (Opsional)</span>
                    <span x-show="provider === 'fonnte'">Token API Fonnte</span>
                    <span x-show="provider === 'waha'">X-Api-Key WAHA</span>
                    <span x-show="provider === 'callmebot'">API Key CallMeBot</span>
                </label>
                <input type="password" name="wa_api_token" value="{{ old('wa_api_token', $tenant->getSecretSetting('wa_api_token') ?? ($settings['wa_api_token'] ?? '')) }}" placeholder="Token / API Key..." class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>

            <!-- Session ID for Open-WA / WAHA -->
            <div x-show="provider === 'open_wa' || provider === 'waha'">
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Session ID</label>
                <input type="text" name="wa_session_id" value="{{ old('wa_session_id', $settings['wa_session_id'] ?? 'default') }}" placeholder="default" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <div style="font-size:11.5px;color:var(--ink-soft);margin-top:4px">Nama sesi instance Open-WA Anda (default: <code>default</code>)</div>
            </div>
        </div>

        <!-- Cara Menjalankan Open-WA Info Box -->
        <div x-show="provider === 'open_wa'" style="margin-top:16px;padding:16px;background:var(--bg-alt);border-radius:8px;border:1px solid var(--line);font-size:12.5px;line-height:1.6;color:var(--ink)">
            <b style="color:#10B981">💡 Cara Menjalankan Open-WA di Server/VPS Anda:</b>
            <div style="margin-top:6px">
                Jalankan perintah ini di terminal server Anda untuk menyalakan REST API Open-WA:
                <pre style="background:#0F172A;color:#38BDF8;padding:10px 14px;border-radius:6px;margin:8px 0;overflow-x:auto;font-family:'JetBrains Mono',monospace;font-size:12px">npx @open-wa/wa-automate --port 2785 --api-key "kunci-rahasia-anda"</pre>
                Scan QR code yang muncul di terminal menggunakan aplikasi WhatsApp di HP Anda. Setelah terhubung, status akan langsung aktif!
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;margin-bottom:28px">
        <button type="submit" class="btn btn-primary" style="padding:12px 32px;font-size:15px;font-weight:700">
            Simpan Semua Pengaturan
        </button>
    </div>
</form>

<!-- Panel Uji Coba WhatsApp -->
<div class="panel reveal d5" style="padding:24px 28px;margin-bottom:40px;background:var(--bg-card);border:1px dashed var(--line)">
    <h4 style="font-size:15px;font-weight:700;color:var(--ink);margin-bottom:6px;display:flex;align-items:center;gap:8px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
        Uji Coba Pengiriman WhatsApp
    </h4>
    <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px">
        Kirim pesan tes ke nomor Anda untuk memastikan koneksi WhatsApp Gateway (Open-WA / Fonnte / WAHA) berfungsi dengan benar.
    </p>

    <form method="POST" action="{{ route('tenant.settings.test-whatsapp', $ts ? ['tenant_slug' => $ts] : []) }}" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        @csrf
        <div style="flex:1;min-width:240px">
            <label class="form-label" style="display:block;font-size:12.5px;font-weight:600;margin-bottom:6px;color:var(--ink)">Nomor WhatsApp Tujuan</label>
            <input type="text" name="test_phone" required placeholder="Contoh: 081234567890" value="{{ old('test_phone', $tenant->whatsapp_number) }}" class="form-input" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:13.5px">
        </div>
        <div style="flex:2;min-width:300px">
            <label class="form-label" style="display:block;font-size:12.5px;font-weight:600;margin-bottom:6px;color:var(--ink)">Pesan Uji Coba (Opsional)</label>
            <input type="text" name="test_message" placeholder="Pesan tes otomatis dari NetSaz..." class="form-input" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:13.5px">
        </div>
        <button type="submit" class="btn" style="background:#10B981;color:#fff;padding:9px 20px;font-weight:600;font-size:13.5px;border-radius:8px;display:flex;align-items:center;gap:6px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
            Kirim Pesan Uji Coba
        </button>
    </form>
</div>
@endsection
