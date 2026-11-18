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

@section('title', 'Export & Import')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Export & import</b>
</div>

<x-page-header title="Export & import" subtitle="Export ke Excel (CSV) data operasional Anda. Import data migrasi dengan aman tanpa mengubah atau menghapus secret Mikrotik.">
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('warning'))
    <div style="background:var(--amber-tint);border:1px solid var(--amber);color:var(--amber);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('warning') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background:var(--red-tint);border:1px solid var(--red);color:var(--red);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- SECTION 1: EXPORT DATA -->
<div class="panel reveal d1" style="padding:26px 28px;margin-bottom:24px">
    <h3 style="font-size:17px;font-weight:700;color:var(--ink);margin-bottom:14px;display:flex;align-items:center;gap:10px">
        <span style="width:32px;height:32px;border-radius:8px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </span>
        Export
    </h3>

    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;font-size:13.5px;color:var(--ink-soft);margin-bottom:12px">
        <div style="display:flex;align-items:center;gap:6px">
            <span>Dari</span>
            <input type="date" id="export_start" value="{{ date('Y-m-01') }}" class="form-input" style="padding:7px 10px;font-size:13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink)">
            <span>s/d</span>
            <input type="date" id="export_end" value="{{ date('Y-m-d') }}" class="form-input" style="padding:7px 10px;font-size:13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink)">
        </div>

        <div style="display:flex;align-items:center;gap:6px">
            <span>atau tahun</span>
            <select id="export_year" class="form-input" style="padding:7px 10px;font-size:13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink)">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

        <button type="button" class="btn btn-outline" onclick="document.getElementById('export_start').value='{{ date('Y-01-01') }}'; document.getElementById('export_end').value='{{ date('Y-12-31') }}';" style="padding:7px 14px;font-size:12.5px">
            Setahun penuh
        </button>
    </div>

    <p style="font-size:12px;color:var(--ink-faint);margin-bottom:16px">
        Pilih rentang dari-sampai, atau satu bulan, atau satu tahun. Berlaku untuk tagihan, pembayaran, dan pelanggan.
    </p>

    <!-- Export Action Buttons -->
    <div style="display:flex;flex-wrap:wrap;gap:8px">
        <a href="{{ route('export.data', array_merge(['type' => 'pelanggan_semua'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="font-size:13px;padding:8px 14px">
            Pelanggan (semua)
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'pelanggan_aktif'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="font-size:13px;padding:8px 14px">
            Pelanggan aktif
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'pelanggan_isolir'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="font-size:13px;padding:8px 14px">
            Pelanggan isolir
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'pelanggan_berhenti'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="font-size:13px;padding:8px 14px">
            Pelanggan berhenti
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'tagihan_belum_bayar'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="font-size:13px;padding:8px 14px">
            Tagihan belum bayar
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'tagihan_lunas'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="font-size:13px;padding:8px 14px">
            Tagihan lunas
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'pembayaran'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="font-size:13px;padding:8px 14px">
            Pembayaran
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'pelanggan_beatcom', 'format' => 'beatcom_xls'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn" style="font-size:13px;padding:8px 16px;background:rgba(16, 185, 129, 0.15);color:#10B981;border:1px solid #10B981;font-weight:600;display:inline-flex;align-items:center;gap:6px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Format Beatcom (.xls)
        </a>
        <a href="{{ route('export.data', array_merge(['type' => 'pelanggan_beatcom', 'format' => 'beatcom_csv'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn" style="font-size:13px;padding:8px 16px;background:rgba(16, 185, 129, 0.15);color:#10B981;border:1px solid #10B981;font-weight:600;display:inline-flex;align-items:center;gap:6px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Format Beatcom (.csv)
        </a>
    </div>
</div>

<!-- SECTION 2: SINKRON DATA DARI BEATCOM / MIGRASI MASSAL -->
<div class="panel reveal d2" style="padding:26px 28px;margin-bottom:24px">
    <h3 style="font-size:17px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:10px">
        <span style="width:32px;height:32px;border-radius:8px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </span>
        Sinkron data dari Beatcom & Aplikasi Billing Lain
    </h3>

    <p style="font-size:13px;color:var(--ink-soft);line-height:1.6;margin-bottom:14px">
        Pilih satu atau beberapa file ekspor Beatcom / aplikasi lama (<code class="mono" style="color:var(--primary)">.xls</code> / <code class="mono" style="color:var(--primary)">.csv</code> / <code class="mono" style="color:var(--primary)">.html</code> / <code class="mono" style="color:var(--primary)">.txt</code>) sekaligus: <b>Pelanggan, Pelanggan Berhenti, Pelanggan Isolir, Lunas Bayar, Tagihan Belum Bayar</b>.
    </p>

    <div style="background:rgba(16, 185, 129, 0.08);border:1px solid rgba(16, 185, 129, 0.25);border-radius:8px;padding:12px 16px;margin-bottom:18px;font-size:12.5px;color:var(--ink);display:flex;align-items:center;gap:10px">
        <span style="font-size:18px">✨</span>
        <div>
            <b>Kompatibel 100% dengan File Export Beatcom:</b> Cukup unggah langsung file <code class="mono">.xls</code>, <code class="mono">.html</code>, atau <code class="mono">.csv</code> hasil unduhan dari Beatcom. Semua Paket, Area, User PPPoE, Alamat, Tanggal Tagihan, dan Koordinat Lat/Long akan langsung masuk otomatis tanpa perlu edit kolom!
        </div>
    </div>

    <form method="POST" action="{{ route('import.sync-beatcom', $ts ? ['tenant_slug' => $ts] : []) }}" enctype="multipart/form-data">
        @csrf
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:14px">
            <div style="position:relative;display:inline-block">
                <input type="file" name="files[]" multiple required accept=".csv,.xls,.xlsx,.html,.htm,.txt" class="form-input" style="padding:9px 12px;font-size:13.5px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);cursor:pointer">
            </div>
            <button type="submit" class="btn btn-primary" style="padding:10px 22px;font-size:13.5px;font-weight:600">
                Simpan semua berurutan
            </button>
        </div>
        <div style="font-size:11.5px;color:var(--ink-faint);margin-top:8px">
            Mendukung multi-file upload (.xls, .html, .csv, .xlsx, .txt) hingga 10MB per file.
        </div>
    </form>
</div>

<!-- SECTION 3: IMPORT PELANGGAN BARU (TEMPLAT BILLING) -->
<div class="panel reveal d3" style="padding:26px 28px;margin-bottom:24px">
    <h3 style="font-size:17px;font-weight:700;color:var(--ink);margin-bottom:8px;display:flex;align-items:center;gap:10px">
        <span style="width:32px;height:32px;border-radius:8px;background:var(--primary-tint);color:var(--primary);display:inline-flex;align-items:center;justify-content:center">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
        </span>
        Import pelanggan baru
    </h3>

    <p style="font-size:13px;color:var(--ink-soft);line-height:1.6;margin-bottom:18px">
        Unduh templat standar atau templat format Beatcom, isi data, lalu unggah. Baris dengan telepon atau user PPPoE yang sudah ada akan otomatis diperbarui tanpa duplikasi data.
    </p>

    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:14px">
        <!-- Download Template Buttons -->
        <a href="{{ route('import.template', $ts ? ['tenant_slug' => $ts] : []) }}" class="btn btn-outline" style="padding:10px 18px;font-size:13.5px;display:inline-flex;align-items:center;gap:8px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Unduh Templat CSV
        </a>
        <a href="{{ route('import.template', array_merge(['format' => 'beatcom'], $ts ? ['tenant_slug' => $ts] : [])) }}" class="btn btn-outline" style="padding:10px 18px;font-size:13.5px;display:inline-flex;align-items:center;gap:8px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Unduh Format Beatcom (.xls)
        </a>

        <!-- Upload CSV/XLS Form -->
        <form method="POST" action="{{ route('import.store', $ts ? ['tenant_slug' => $ts] : []) }}" enctype="multipart/form-data" style="display:flex;flex-wrap:wrap;align-items:center;gap:12px">
            @csrf
            <input type="file" name="file" accept=".csv,.xls,.xlsx,.html,.htm,.txt" required class="form-input" style="padding:9px 12px;font-size:13.5px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);cursor:pointer">
            <button type="submit" class="btn btn-primary" style="padding:10px 22px;font-size:13.5px;font-weight:600">
                Unggah & Proses
            </button>
        </form>
    </div>
</div>
@endsection
