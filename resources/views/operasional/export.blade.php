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

@section('title', 'Pusat Ekspor Data Operasional')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Operasional</span> &gt; <b>Export</b>
</div>

<x-page-header title="Pusat Ekspor Data Operasional" subtitle="Unduh backup dan rekapitulasi data pelanggan, tagihan, pembayaran, dan tiket gangguan dalam format CSV / Excel">
</x-page-header>

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:24px">
    <!-- Export Customers -->
    <div class="card" style="padding:24px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px;display:flex;flex-direction:column;justify-content:space-between">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <h4 style="margin:0;font-size:16px;color:var(--ink)">Data Pelanggan</h4>
                    <span style="font-size:12px;color:var(--ink-soft)">{{ $stats['customers'] }} Record Tersedia</span>
                </div>
            </div>
            <p style="font-size:13px;color:var(--ink-soft);line-height:1.5;margin-bottom:16px">
                Daftar lengkap kode pelanggan, nama, no HP, alamat, paket aktif, IP address, dan tanggal pasang.
            </p>
        </div>
        <a href="{{ route('operasional.export.download', array_merge($ts ? ['tenant_slug' => $ts] : [], ['type' => 'customers'])) }}" class="btn btn-primary" style="text-align:center;text-decoration:none;justify-content:center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Ekspor Pelanggan (.csv)
        </a>
    </div>

    <!-- Export Invoices -->
    <div class="card" style="padding:24px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px;display:flex;flex-direction:column;justify-content:space-between">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div>
                    <h4 style="margin:0;font-size:16px;color:var(--ink)">Data Tagihan / Invoice</h4>
                    <span style="font-size:12px;color:var(--ink-soft)">{{ $stats['invoices'] }} Record Tersedia</span>
                </div>
            </div>
            <p style="font-size:13px;color:var(--ink-soft);line-height:1.5;margin-bottom:16px">
                Nomor invoice, nama pelanggan, periode penagihan, total nominal, jatuh tempo, dan status lunas / belum lunas.
            </p>
        </div>
        <a href="{{ route('operasional.export.download', array_merge($ts ? ['tenant_slug' => $ts] : [], ['type' => 'invoices'])) }}" class="btn btn-primary" style="text-align:center;text-decoration:none;justify-content:center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Ekspor Invoice (.csv)
        </a>
    </div>

    <!-- Export Payments -->
    <div class="card" style="padding:24px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px;display:flex;flex-direction:column;justify-content:space-between">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <h4 style="margin:0;font-size:16px;color:var(--ink)">Data Pembayaran</h4>
                    <span style="font-size:12px;color:var(--ink-soft)">{{ $stats['payments'] }} Record Tersedia</span>
                </div>
            </div>
            <p style="font-size:13px;color:var(--ink-soft);line-height:1.5;margin-bottom:16px">
                Riwayat transaksi masuk, gateway payment (Duitku, Midtrans, Manual Transfer), status verifikasi, dan timestamp.
            </p>
        </div>
        <a href="{{ route('operasional.export.download', array_merge($ts ? ['tenant_slug' => $ts] : [], ['type' => 'payments'])) }}" class="btn btn-primary" style="text-align:center;text-decoration:none;justify-content:center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Ekspor Pembayaran (.csv)
        </a>
    </div>

    <!-- Export Tickets -->
    <div class="card" style="padding:24px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px;display:flex;flex-direction:column;justify-content:space-between">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <div style="width:36px;height:36px;border-radius:8px;background:var(--primary-tint);color:var(--primary);display:flex;align-items:center;justify-content:center">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div>
                    <h4 style="margin:0;font-size:16px;color:var(--ink)">Tiket Gangguan</h4>
                    <span style="font-size:12px;color:var(--ink-soft)">{{ $stats['tickets'] }} Record Tersedia</span>
                </div>
            </div>
            <p style="font-size:13px;color:var(--ink-soft);line-height:1.5;margin-bottom:16px">
                Laporan kendala pelanggan, tingkat urgensi / prioritas, status perbaikan tim lapangan, dan resolusi tiket.
            </p>
        </div>
        <a href="{{ route('operasional.export.download', array_merge($ts ? ['tenant_slug' => $ts] : [], ['type' => 'tickets'])) }}" class="btn btn-primary" style="text-align:center;text-decoration:none;justify-content:center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Ekspor Tiket (.csv)
        </a>
    </div>
</div>
@endsection
