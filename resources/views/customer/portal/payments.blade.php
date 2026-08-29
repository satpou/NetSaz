@extends('layouts.portal')

@section('title', 'Pembayaran')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
    <div>
        <h1 style="font-size:24px;font-weight:700;color:var(--ink);margin-bottom:4px">Riwayat Pembayaran</h1>
        <p style="font-size:14px;color:var(--ink-soft)">Daftar pembayaran {{ $payments->total() }}</p>
    </div>
</div>

<div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;overflow:hidden">
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%">
            <thead>
                <tr>
                    <th style="padding:14px 20px">Invoice</th>
                    <th style="padding:14px 20px">Tanggal</th>
                    <th style="padding:14px 20px">Metode</th>
                    <th style="padding:14px 20px">Jumlah</th>
                    <th style="padding:14px 20px">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $pmt)
                <tr>
                    <td style="padding:14px 20px;color:var(--ink);font-weight:500">
                        <a href="{{ route('customer.portal.invoices.show', $pmt->invoice_id) }}" style="color:var(--ink);text-decoration:none">{{ $pmt->invoice->invoice_number ?? '#' . $pmt->invoice_id }}</a>
                    </td>
                    <td style="padding:14px 20px;color:var(--ink-soft);font-size:13px">{{ $pmt->paid_at ? $pmt->paid_at->format('d M Y') : $pmt->created_at->format('d M Y') }}</td>
                    <td style="padding:14px 20px">
                        <span class="badge badge-ghost" style="font-size:12px">
                            {{ $pmt->payment_method === 'transfer' ? 'Transfer' : ($pmt->payment_method === 'qris' ? 'QRIS' : 'Gateway') }}
                        </span>
                    </td>
                    <td style="padding:14px 20px;font-family:JetBrains Mono;font-size:14px;font-weight:600;color:{{ $pmt->status === 'success' ? 'var(--green)' : 'var(--ink-soft)' }}">Rp{{ number_format($pmt->amount, 0, ',', '.') }}</td>
                    <td style="padding:14px 20px">
                        @if($pmt->status === 'success')
                            <span class="badge badge-paid" style="font-size:12px">Lunas</span>
                        @elseif(in_array($pmt->status, ['failed', 'expired', 'refunded']))
                            <span class="badge" style="font-size:12px;background:#fee2e2;color:#b91c1c">Ditolak</span>
                        @else
                            <span class="badge" style="font-size:12px;background:var(--amber-tint);color:var(--amber)">Menunggu Verifikasi</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="padding:60px 20px;text-align:center;color:var(--ink-faint);font-size:14px">
                        <div style="margin-bottom:8px;opacity:.4">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                        </div>
                        Belum ada pembayaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($payments->hasPages())
<div style="margin-top:20px">
    {{ $payments->links() }}
</div>
@endif
@endSection
