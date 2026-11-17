@extends('layouts.app')

@section('title', 'Laporan Platform')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Laporan Platform</b>
</div>

<x-page-header title="Laporan & Analitik Platform" subtitle="Statistik performa, revenue, pelanggan, dan aktivitas seluruh ISP">
    <a href="{{ route('admin.reports.export') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV Laporan
    </a>
</x-page-header>

<!-- Platform High-Level Stat Grid -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:26px">
    <div class="stat-card reveal d1">
        <div class="stat-top"><span class="stat-label">Total Revenue Platform</span><span class="stat-icon green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></span></div>
        <div class="stat-value">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
        <div class="stat-desc">Bulan ini: Rp{{ number_format($thisMonthRevenue, 0, ',', '.') }}</div>
    </div>
    <div class="stat-card reveal d2">
        <div class="stat-top"><span class="stat-label">Total Pelanggan Aktif</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/></svg></span></div>
        <div class="stat-value">{{ number_format($activeCustomers) }}</div>
        <div class="stat-desc">{{ $isolatedCustomers }} isolir, {{ $suspendedCustomers }} suspend</div>
    </div>
    <div class="stat-card reveal d3">
        <div class="stat-top"><span class="stat-label">Total ISP Terdaftar</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></span></div>
        <div class="stat-value">{{ $tenantsCount }}</div>
        <div class="stat-desc">{{ $activeTenantsCount }} aktif melayani</div>
    </div>
    <div class="stat-card reveal d4">
        <div class="stat-top"><span class="stat-label">Total Invoice & Tagihan</span><span class="stat-icon amber"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span></div>
        <div class="stat-value">{{ number_format($totalInvoices) }}</div>
        <div class="stat-desc">{{ $paidInvoices }} lunas, {{ $unpaidInvoices }} belum dibayar</div>
    </div>
</div>

<!-- Breakdown Table per ISP -->
<div class="panel" style="overflow:hidden;padding:0">
    <div style="padding:20px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
        <div>
            <h3 style="font-size:16px;font-weight:700;color:var(--ink)">Performa & Kontribusi per ISP</h3>
            <p style="font-size:12.5px;color:var(--ink-soft);margin-top:2px">Data operasional, jumlah pelanggan, invoice, dan perputaran uang tiap ISP</p>
        </div>
        <form method="GET" action="{{ route('admin.reports.index') }}" style="display:flex;gap:8px">
            <input type="text" name="search" placeholder="Cari ISP..." value="{{ request('search') }}" class="form-input" style="padding:8px 12px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:13px">
            <button type="submit" class="btn btn-outline" style="padding:8px 14px;font-size:13px">Cari</button>
        </form>
    </div>

    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase;letter-spacing:.04em">
                    <th style="padding:16px 20px 14px 24px">Nama ISP</th>
                    <th style="padding:16px 16px 14px">Paket Langganan</th>
                    <th style="padding:16px 16px 14px;text-align:center">Pelanggan</th>
                    <th style="padding:16px 16px 14px;text-align:center">Pengguna</th>
                    <th style="padding:16px 16px 14px;text-align:center">Invoices</th>
                    <th style="padding:16px 16px 14px;text-align:right">Revenue Bulan Ini</th>
                    <th style="padding:16px 24px 14px;text-align:right">Total Revenue ISP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenantBreakdown as $t)
                    <tr style="border-bottom:1px solid var(--line);transition:background .15s">
                        <td style="padding:16px 20px 16px 24px">
                            <div style="font-weight:600;color:var(--ink);font-size:14.5px">{{ $t->name }}</div>
                            <div style="font-size:12px;color:var(--ink-faint);margin-top:2px">
                                <code>{{ $t->slug }}.{{ config('app.tenant_domain') }}</code>
                            </div>
                        </td>
                        <td style="padding:16px 16px">
                            <span style="font-weight:600;color:var(--primary)">{{ $t->activeSubscription?->plan?->name ?? 'None' }}</span>
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            <span class="mono" style="font-weight:600">{{ number_format($t->customers_count) }}</span>
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            <span class="mono" style="font-weight:600">{{ $t->users_count }}</span>
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            <span class="mono" style="font-weight:600">{{ number_format($t->invoices_count) }}</span>
                        </td>
                        <td style="padding:16px 16px;text-align:right">
                            <span class="mono" style="font-weight:600;color:var(--green)">
                                Rp{{ number_format($t->this_month_revenue ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                        <td style="padding:16px 24px 16px 16px;text-align:right">
                            <span class="mono" style="font-weight:700;color:var(--ink)">
                                Rp{{ number_format($t->total_revenue ?? 0, 0, ',', '.') }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:48px 24px;text-align:center;color:var(--ink-faint)">
                            Belum ada data transaksi ISP.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenantBreakdown->hasPages())
        <div style="padding:16px 24px;border-top:1px solid var(--line)">
            {{ $tenantBreakdown->links() }}
        </div>
    @endif
</div>
@endsection
