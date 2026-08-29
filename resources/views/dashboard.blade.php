@php
    $user = Auth::user();
    $ts = $user && $user->tenant ? $user->tenant->slug : null;
@endphp
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="breadcrumb reveal">Home &gt; <b>Dashboard</b></div>

<div class="welcome">
    <h2>Selamat datang, {{ Auth::user()->name ?? 'User' }}</h2>
    <div class="date" id="today-date"></div>
    @php
        $hasMidtrans = $ts && $user->tenant && ($user->tenant->settings['midtrans_server_key'] ?? false);
    @endphp
    @if($hasMidtrans)
    <div class="status-pill ok"><span class="dot"></span>Pembayaran online aktif</div>
    @else
    <div class="status-pill warn"><span class="dot"></span>Pembayaran online — aktifkan Midtrans di pengaturan tenant</div>
    @endif
</div>

<div class="quick-actions reveal d1">
    <a class="qa primary" href="{{ $ts ? route('customers.create', ['tenant_slug' => $ts]) : '#' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
        Tambah Pelanggan
    </a>
    <a class="qa" href="{{ $ts ? route('invoices.create', ['tenant_slug' => $ts]) : '#' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M4 12h10M4 17h7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Tambah Invoice
    </a>
    <a class="qa" href="{{ $ts ? route('payments.index', ['tenant_slug' => $ts]) : '#' }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
        Verifikasi Pembayaran
    </a>
</div>

<div class="stat-grid">
    <div class="stat-card reveal d1">
        <div class="stat-top"><span class="stat-label">Pelanggan</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="8" r="3.5" stroke="currentColor" stroke-width="2"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span></div>
        <div class="stat-value" data-count="{{ $totalCustomers }}">{{ $totalCustomers }}</div>
        <div class="stat-desc">{{ $totalCustomers > 0 ? $activeCustomers . ' aktif, ' . $isolatedCustomers . ' isolir, ' . $suspendedCustomers . ' suspend' : 'Belum ada pelanggan.' }}</div>
    </div>
    <div class="stat-card reveal d2">
        <div class="stat-top"><span class="stat-label">Bayar Hari Ini</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/><path d="M12 7v5l3 3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></span></div>
        <div class="stat-value">Rp{{ number_format($todayPayments, 0, ',', '.') }}</div>
        <div class="stat-desc">{{ $todayPayments > 0 ? 'Pembayaran masuk hari ini' : 'Belum ada pembayaran hari ini.' }}</div>
    </div>
    <div class="stat-card reveal d3">
        <div class="stat-top"><span class="stat-label">Sudah Bayar (Bulan Ini)</span><span class="stat-icon green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4 10-10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div>
        <div class="stat-value">{{ $paidInvoicesThisMonth > 0 ? 'Rp' . number_format($paidRevenueThisMonth, 0, ',', '.') : '0' }}</div>
        <div class="stat-desc">{{ $paidInvoicesThisMonth > 0 ? $paidInvoicesThisMonth . ' invoice lunas' : 'Belum ada invoice lunas bulan ini.' }}</div>
    </div>
    <div class="stat-card reveal d4">
        <div class="stat-top"><span class="stat-label">Collection Rate</span><span class="stat-icon {{ $collectionRate['rate'] >= 75 ? 'green' : ($collectionRate['rate'] >= 50 ? 'amber' : 'red') }}"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg></span></div>
        <div class="stat-value">{{ $collectionRate['rate'] }}%</div>
        <div class="stat-desc">{{ $collectionRate['paid'] }} dari {{ $collectionRate['total'] }} invoice bulan ini</div>
    </div>
    <div class="stat-card reveal d5">
        <div class="stat-top"><span class="stat-label">Invoice Overdue</span><span class="stat-icon red"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 9v4M12 16.5h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg></span></div>
        <div class="stat-value">{{ $overdueInvoices }}</div>
        <div class="stat-desc">{{ $overdueInvoices > 0 ? 'Rp' . number_format($totalOverdueAmount, 0, ',', '.') . ' total tertunggak' : 'Tidak ada invoice overdue.' }}</div>
    </div>
</div>

<div class="lower-grid">
    <div class="panel reveal d2">
        <div class="panel-head"><h3>Pendapatan 30 Hari Terakhir</h3></div>
        <div class="panel-sub">Arus kas harian dari pembayaran pelanggan.</div>
        <div class="chart-wrap" style="position:relative;height:220px">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="panel reveal d3">
        <div class="panel-head"><h3>Ringkasan Billing</h3></div>
        <div class="panel-sub">Status tagihan bulan berjalan.</div>
        <div class="summary-row">
            <div class="summary-label"><i style="background:var(--green)"></i>Total tertagih</div>
            <div class="summary-value">Rp{{ number_format($paidRevenueThisMonth, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label"><i style="background:var(--amber)"></i>Menunggu pembayaran</div>
            <div class="summary-value">Rp{{ number_format($totalUnpaidAmount, 0, ',', '.') }}</div>
        </div>
        <div class="summary-row">
            <div class="summary-label"><i style="background:var(--red)"></i>Tertunggak</div>
            <div class="summary-value">Rp{{ number_format($totalOverdueAmount, 0, ',', '.') }}</div>
        </div>
    </div>
</div>

<div class="chart-grid">
    <div class="panel reveal d2">
        <div class="panel-head"><h3>Pertumbuhan Pelanggan</h3></div>
        <div class="panel-sub">Total pelanggan per bulan (12 bulan terakhir).</div>
        <div class="chart-wrap" style="position:relative;height:200px">
            <canvas id="customerChart"></canvas>
        </div>
    </div>
    <div class="panel reveal d3">
        <div class="panel-head"><h3>Metode Pembayaran</h3></div>
        <div class="panel-sub">Distribusi pembayaran berdasarkan metode.</div>
        <div class="chart-wrap" style="position:relative;height:200px">
            <canvas id="paymentMethodChart"></canvas>
        </div>
    </div>
</div>

<div class="rekap-grid">
    <div class="panel reveal d2">
        <div class="panel-head">
            <h3>Top Revenue Customer</h3>
            <a href="{{ $ts ? route('customers.index', ['tenant_slug' => $ts]) : '#' }}" class="panel-link">Lihat semua →</a>
        </div>
        <div class="panel-sub">Pelanggan dengan kontribusi pendapatan terbesar.</div>
        <table class="rekap-table">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Paket</th>
                    <th>Total Bayar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topCustomers as $tc)
                <tr>
                    <td>{{ $tc->customer->name }}</td>
                    <td>{{ $tc->customer->package->name ?? '-' }}</td>
                    <td>Rp{{ number_format($tc->total_paid, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;padding:24px 0;color:var(--ink-faint);font-size:13px">Belum ada data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel reveal d3">
        <div class="panel-head"><h3>Rekapitulasi Tagihan</h3></div>
        <div class="panel-sub">Rekap status invoice bulan ini.</div>
        @php
            $totalInv = max($paidInvoicesThisMonth + $unpaidInvoices + $overdueInvoices, 1);
            $paidPct = round($paidInvoicesThisMonth / $totalInv * 100);
            $unpaidPct = round($unpaidInvoices / $totalInv * 100);
            $overduePct = round($overdueInvoices / $totalInv * 100);
        @endphp
        <div class="rekap-summary">
            <div class="rekap-row">
                <div class="rekap-label"><i style="background:var(--green)"></i>Lunas</div>
                <div class="rekap-value">{{ $paidInvoicesThisMonth }}</div>
                <div class="rekap-bar"><div class="rekap-bar-fill" style="width:{{ $paidPct }}%;background:var(--green)"></div></div>
            </div>
            <div class="rekap-row">
                <div class="rekap-label"><i style="background:var(--amber)"></i>Belum Bayar</div>
                <div class="rekap-value">{{ $unpaidInvoices }}</div>
                <div class="rekap-bar"><div class="rekap-bar-fill" style="width:{{ $unpaidPct }}%;background:var(--amber)"></div></div>
            </div>
            <div class="rekap-row">
                <div class="rekap-label"><i style="background:var(--red)"></i>Nunggak</div>
                <div class="rekap-value">{{ $overdueInvoices }}</div>
                <div class="rekap-bar"><div class="rekap-bar-fill" style="width:{{ $overduePct }}%;background:var(--red)"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="rekap-grid">
    <div class="panel reveal d2">
        <div class="panel-head">
            <h3>Invoice Terbaru</h3>
            <a href="{{ $ts ? route('invoices.index', ['tenant_slug' => $ts]) : '#' }}" class="panel-link">Lihat semua →</a>
        </div>
        <div class="panel-sub">5 invoice paling baru.</div>
        <table class="rekap-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Nominal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentInvoices as $inv)
                <tr>
                    <td>{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->customer->name ?? '-' }}</td>
                    <td>Rp{{ number_format($inv->amount, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $inv->status == 'paid' ? 'badge-paid' : 'badge-due' }}">{{ $inv->status }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:24px 0;color:var(--ink-faint);font-size:13px">Belum ada invoice</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel reveal d3">
        <div class="panel-head">
            <h3>Pembayaran Terbaru</h3>
            <a href="{{ $ts ? route('payments.index', ['tenant_slug' => $ts]) : '#' }}" class="panel-link">Lihat semua →</a>
        </div>
        <div class="panel-sub">5 pembayaran paling baru.</div>
        <table class="rekap-table">
            <thead>
                <tr>
                    <th>Pelanggan</th>
                    <th>Tanggal</th>
                    <th>Nominal</th>
                    <th>Metode</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentPayments as $pmt)
                <tr>
                    <td>{{ $pmt->customer->name ?? ($pmt->invoice->customer->name ?? '-') }}</td>
                    <td>{{ $pmt->created_at->format('d M Y') }}</td>
                    <td>Rp{{ number_format($pmt->amount, 0, ',', '.') }}</td>
                    <td><span class="badge badge-ghost">{{ $pmt->payment_method }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:24px 0;color:var(--ink-faint);font-size:13px">Belum ada pembayaran</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Revenue Chart
    var revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels: @json(collect($revenueChart)->pluck('date')),
                datasets: [{
                    label: 'Pendapatan',
                    data: @json(collect($revenueChart)->pluck('total')),
                    backgroundColor: 'rgba(0, 113, 227, 0.08)',
                    borderColor: 'rgba(0, 113, 227, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 2,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, maxTicksLimit: 10 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: function (v) { return 'Rp' + v.toLocaleString('id-ID'); }
                        }
                    }
                }
            }
        });
    }

    // Customer Growth Chart
    var custCtx = document.getElementById('customerChart');
    if (custCtx) {
        new Chart(custCtx, {
            type: 'bar',
            data: {
                labels: @json(collect($customerChart)->pluck('label')),
                datasets: [{
                    label: 'Total Pelanggan',
                    data: @json(collect($customerChart)->pluck('total')),
                    backgroundColor: 'rgba(0, 113, 227, 0.6)',
                    borderColor: 'rgba(0, 113, 227, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9 }, maxTicksLimit: 8 }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            stepSize: 1,
                            callback: function (v) { return Number.isInteger(v) ? v : ''; }
                        }
                    }
                }
            }
        });
    }

    // Payment Method Chart
    var pmtCtx = document.getElementById('paymentMethodChart');
    if (pmtCtx) {
        var pmtData = @json($paymentMethodChart);
        var colors = ['#0071E3', '#34C759', '#FF9500', '#FF3B30', '#AF52DE', '#5856D6', '#8E8E93'];
        new Chart(pmtCtx, {
            type: 'doughnut',
            data: {
                labels: pmtData.map(function (d) { return d.payment_method || 'Lainnya'; }),
                datasets: [{
                    data: pmtData.map(function (d) { return d.total; }),
                    backgroundColor: colors.slice(0, pmtData.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            font: { size: 11 },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = ((ctx.parsed / total) * 100).toFixed(1);
                                return ' Rp' + ctx.parsed.toLocaleString('id-ID') + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
