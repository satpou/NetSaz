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

@section('title', 'Template Pesan WhatsApp')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>WhatsApp</span> &gt; <b>Template</b>
</div>

<x-page-header title="Template Notifikasi WhatsApp" subtitle="Kustomisasi pesan otomatis untuk tagihan terbit, pengingat jatuh tempo, kuitansi pembayaran, dan isolir">
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;padding:24px;margin-bottom:24px">
    <div style="background:var(--bg-alt);border:1px solid var(--line);border-radius:8px;padding:14px 18px;margin-bottom:24px;font-size:13px;color:var(--ink-soft)">
        <b style="color:var(--ink)">Variabel Dinamis yang Didukung:</b>
        <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:8px">
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{nama}</code>
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{invoice_no}</code>
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{periode}</code>
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{nominal}</code>
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{jatuh_tempo}</code>
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{link_portal}</code>
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{ticket_no}</code>
            <code class="mono" style="background:var(--bg-card);padding:2px 6px;border-radius:4px;color:var(--primary)">{status}</code>
        </div>
    </div>

    <form action="{{ route('whatsapp.templates.update', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Template 1: Tagihan Terbit -->
        <div class="form-field" style="margin-bottom:22px">
            <label style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px">1. Template Tagihan Baru Terbit</label>
            <textarea name="wa_template_invoice" rows="5" class="form-input" style="font-family:'JetBrains Mono',monospace;font-size:13px;resize:vertical">{{ old('wa_template_invoice', $settings['wa_template_invoice'] ?? $defaultTemplates['wa_template_invoice']) }}</textarea>
        </div>

        <!-- Template 2: Pengingat Jatuh Tempo -->
        <div class="form-field" style="margin-bottom:22px">
            <label style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px">2. Template Pengingat Jatuh Tempo</label>
            <textarea name="wa_template_reminder" rows="5" class="form-input" style="font-family:'JetBrains Mono',monospace;font-size:13px;resize:vertical">{{ old('wa_template_reminder', $settings['wa_template_reminder'] ?? $defaultTemplates['wa_template_reminder']) }}</textarea>
        </div>

        <!-- Template 3: Pembayaran Sukses -->
        <div class="form-field" style="margin-bottom:22px">
            <label style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px">3. Template Notifikasi Pembayaran Diterima / Lunas</label>
            <textarea name="wa_template_paid" rows="5" class="form-input" style="font-family:'JetBrains Mono',monospace;font-size:13px;resize:vertical">{{ old('wa_template_paid', $settings['wa_template_paid'] ?? $defaultTemplates['wa_template_paid']) }}</textarea>
        </div>

        <!-- Template 4: Pemberitahuan Isolir -->
        <div class="form-field" style="margin-bottom:22px">
            <label style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px">4. Template Notifikasi Layanan Terisolir</label>
            <textarea name="wa_template_isolir" rows="5" class="form-input" style="font-family:'JetBrains Mono',monospace;font-size:13px;resize:vertical">{{ old('wa_template_isolir', $settings['wa_template_isolir'] ?? $defaultTemplates['wa_template_isolir']) }}</textarea>
        </div>

        <!-- Template 5: Tiket Gangguan -->
        <div class="form-field" style="margin-bottom:28px">
            <label style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px">5. Template Pembaruan Tiket Gangguan</label>
            <textarea name="wa_template_ticket" rows="4" class="form-input" style="font-family:'JetBrains Mono',monospace;font-size:13px;resize:vertical">{{ old('wa_template_ticket', $settings['wa_template_ticket'] ?? $defaultTemplates['wa_template_ticket']) }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:10px 24px">
            Simpan Semua Template
        </button>
    </form>
</div>
@endsection
