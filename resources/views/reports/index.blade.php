@php
    $user = Auth::user();
    $ts = $user && $user->tenant ? $user->tenant->slug : null;
@endphp
@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="breadcrumb reveal">Home &gt; <b>Laporan</b></div>

<div class="welcome" style="margin-bottom:24px">
    <h2>Laporan & Ekspor Data</h2>
    <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">Download data dalam format CSV untuk analisis lebih lanjut.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px;margin-bottom:24px">
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px">
        <div style="font-size:13px;color:var(--ink-soft);font-weight:500;margin-bottom:8px">Pendapatan Bulan Ini</div>
        <div style="font-size:22px;font-weight:700;color:var(--green);font-family:JetBrains Mono">Rp{{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
    </div>
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px">
        <div style="font-size:13px;color:var(--ink-soft);font-weight:500;margin-bottom:8px">Total Outstanding</div>
        <div style="font-size:22px;font-weight:700;color:var(--red);font-family:JetBrains Mono">Rp{{ number_format($totalOutstanding, 0, ',', '.') }}</div>
    </div>
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px">
        <div style="font-size:13px;color:var(--ink-soft);font-weight:500;margin-bottom:8px">Total Pelanggan</div>
        <div style="font-size:22px;font-weight:700;color:var(--ink);font-family:JetBrains Mono">{{ $customerCount }}</div>
    </div>
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px">
        <div style="font-size:13px;color:var(--ink-soft);font-weight:500;margin-bottom:8px">Invoice Bulan Ini</div>
        <div style="font-size:22px;font-weight:700;color:var(--ink);font-family:JetBrains Mono">{{ $invoiceCount }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
    <div class="panel">
        <div class="panel-head"><h3>Data Pelanggan</h3></div>
        <div class="panel-sub">Ekspor daftar seluruh pelanggan.</div>
        <div style="display:flex;flex-direction:column;gap:10px">
            <a href="{{ route('reports.export', ['type' => 'customers', 'format' => 'csv', 'tenant_slug' => $ts]) }}" class="btn btn-outline" style="justify-content:center">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download CSV
            </a>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Data Invoice</h3></div>
        <div class="panel-sub">Ekspor daftar invoice (filter status).</div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <a href="{{ route('reports.export', ['type' => 'invoices', 'format' => 'csv', 'tenant_slug' => $ts]) }}" class="btn btn-outline" style="justify-content:center;font-size:13px">Semua Invoice</a>
            <div style="display:flex;gap:6px">
                <a href="{{ route('reports.export', ['type' => 'invoices', 'format' => 'csv', 'status' => 'paid', 'tenant_slug' => $ts]) }}" class="btn btn-ghost" style="flex:1;justify-content:center;font-size:12px">Lunas</a>
                <a href="{{ route('reports.export', ['type' => 'invoices', 'format' => 'csv', 'status' => 'unpaid', 'tenant_slug' => $ts]) }}" class="btn btn-ghost" style="flex:1;justify-content:center;font-size:12px">Belum Bayar</a>
                <a href="{{ route('reports.export', ['type' => 'invoices', 'format' => 'csv', 'status' => 'overdue', 'tenant_slug' => $ts]) }}" class="btn btn-ghost" style="flex:1;justify-content:center;font-size:12px">Overdue</a>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Data Pembayaran</h3></div>
        <div class="panel-sub">Ekspor riwayat pembayaran (filter tanggal).</div>
        <form method="GET" action="{{ route('reports.export', ['type' => 'payments', 'format' => 'csv', 'tenant_slug' => $ts]) }}" style="display:flex;flex-direction:column;gap:10px">
            <div style="display:flex;gap:8px">
                <div style="flex:1">
                    <label style="font-size:12px;color:var(--ink-faint);margin-bottom:4px;display:block">Dari</label>
                    <input type="date" name="start" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="form-input" style="width:100%;font-size:13px">
                </div>
                <div style="flex:1">
                    <label style="font-size:12px;color:var(--ink-faint);margin-bottom:4px;display:block">Sampai</label>
                    <input type="date" name="end" value="{{ now()->format('Y-m-d') }}" class="form-input" style="width:100%;font-size:13px">
                </div>
            </div>
            <button type="submit" class="btn btn-outline" style="justify-content:center;font-size:13px">Download CSV</button>
        </form>
    </div>

    <div class="panel">
        <div class="panel-head"><h3>Pendapatan Tahunan</h3></div>
        <div class="panel-sub">Rekap pendapatan per bulan.</div>
        <form method="GET" action="{{ route('reports.export', ['type' => 'revenue', 'format' => 'csv', 'tenant_slug' => $ts]) }}" style="display:flex;flex-direction:column;gap:10px">
            <div>
                <label style="font-size:12px;color:var(--ink-faint);margin-bottom:4px;display:block">Tahun</label>
                <select name="year" class="form-input" style="width:100%">
                    @for($y = now()->year; $y >= 2024; $y--)
                    <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-outline" style="justify-content:center;font-size:13px">Download CSV</button>
        </form>
    </div>
</div>
@endSection
