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

@section('title', 'Deployment Center')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Deployment Center</b>
</div>

<x-page-header title="NetSaz Deployment Center" subtitle="Pusat instalasi cepat, script zero-touch provisioning MikroTik, dan setup tunnel terenkripsi">
</x-page-header>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Platform Target</div>
        <div style="font-size:22px;font-weight:700;color:var(--ink);margin-top:6px">MikroTik RouterOS</div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Didukung: RouterOS v6.48+ &amp; v7.x</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Metode Integrasi</div>
        <div style="font-size:22px;font-weight:700;color:var(--primary);margin-top:6px">REST / Binary API &amp; Agent</div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Port default: 8728 (API) / 8729 (SSL)</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Otomasi Terpasang</div>
        <div style="font-size:22px;font-weight:700;color:var(--green);margin-top:6px">PPPoE &amp; Auto Isolir NAT</div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Walled Garden redirect aktif</div>
    </div>
</div>

<!-- Deployment Steps & Script Box -->
<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;padding:24px;margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div>
            <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin:0">1-Click MikroTik Bootstrap Script</h3>
            <p style="font-size:13px;color:var(--ink-soft);margin:4px 0 0">
                Salin baris perintah ini ke menu <b>New Terminal</b> di Winbox router Anda. Script ini akan menyiapkan user API, isolir pool, dan firewall filter otomatis.
            </p>
        </div>
        <button onclick="copyDeploymentScript()" class="btn btn-primary" id="btn-copy">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Salin Script
        </button>
    </div>

    <div style="background:#090d16;padding:20px;border-radius:10px;border:1px solid var(--line);position:relative">
        <pre id="deployment-script-content" style="margin:0;font-family:'JetBrains Mono',monospace;font-size:12.5px;color:#38bdf8;white-space:pre-wrap;line-height:1.6">{{ $bootstrapScript }}</pre>
    </div>
</div>

<!-- Step by Step Wizard -->
<div class="card" style="padding:24px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
    <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0 0 16px">Langkah Integrasi Router Baru</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:20px">
        <div style="border-left:3px solid var(--primary);padding-left:14px">
            <div style="font-size:12px;font-weight:700;color:var(--primary);text-transform:uppercase">Langkah 1</div>
            <div style="font-size:14px;font-weight:600;color:var(--ink);margin-top:2px">Jalankan Bootstrap Script</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-top:4px">Buka Winbox &gt; Terminal, paste script di atas lalu tekan Enter.</p>
        </div>
        <div style="border-left:3px solid var(--primary);padding-left:14px">
            <div style="font-size:12px;font-weight:700;color:var(--primary);text-transform:uppercase">Langkah 2</div>
            <div style="font-size:14px;font-weight:600;color:var(--ink);margin-top:2px">Daftarkan di NetSaz</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-top:4px">Buka menu <b>Router &gt; My Mikrotik</b>, klik Tambah Router dan masukkan IP router.</p>
        </div>
        <div style="border-left:3px solid var(--green);padding-left:14px">
            <div style="font-size:12px;font-weight:700;color:var(--green);text-transform:uppercase">Langkah 3</div>
            <div style="font-size:14px;font-weight:600;color:var(--ink);margin-top:2px">Verifikasi Koneksi API</div>
            <p style="font-size:13px;color:var(--ink-soft);margin-top:4px">Klik tombol "Test Ping API" untuk memastikan status router berubah menjadi Online.</p>
        </div>
    </div>
</div>

<script>
function copyDeploymentScript() {
    var text = document.getElementById('deployment-script-content').innerText;
    navigator.clipboard.writeText(text).then(function() {
        var btn = document.getElementById('btn-copy');
        var originalHtml = btn.innerHTML;
        btn.innerHTML = '<span>Tersalin!</span>';
        setTimeout(function() {
            btn.innerHTML = originalHtml;
        }, 2000);
    });
}
</script>
@endsection
