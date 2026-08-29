@extends('layouts.portal')

@section('title', 'Invoice')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-size:24px;font-weight:700;color:var(--ink);margin-bottom:4px">Invoice</h1>
        <p style="font-size:14px;color:var(--ink-soft)">Daftar tagihan {{ $invoices->total() }}</p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('customer.portal.invoices', ['status' => '']) }}" class="btn btn-outline" style="font-size:12px">Semua</a>
        <a href="{{ route('customer.portal.invoices', ['status' => 'unpaid']) }}" class="btn btn-outline" style="font-size:12px">Belum Bayar</a>
        <a href="{{ route('customer.portal.invoices', ['status' => 'paid']) }}" class="btn btn-outline" style="font-size:12px">Lunas</a>
        <a href="{{ route('customer.portal.invoices', ['status' => 'overdue']) }}" class="btn btn-outline" style="font-size:12px">Overdue</a>
    </div>
</div>

<div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;overflow:hidden">
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%">
            <thead>
                <tr>
                    <th style="padding:14px 20px">No Invoice</th>
                    <th style="padding:14px 20px">Periode</th>
                    <th style="padding:14px 20px">Total</th>
                    <th style="padding:14px 20px">Jatuh Tempo</th>
                    <th style="padding:14px 20px">Status</th>
                    <th style="padding:14px 20px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td style="padding:14px 20px;font-weight:600;color:var(--ink)">{{ $inv->invoice_number }}</td>
                    <td style="padding:14px 20px;color:var(--ink-soft);font-size:13px">
                        @if($inv->period_start && $inv->period_end)
                            {{ $inv->period_start->format('d M') }} - {{ $inv->period_end->format('d M Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="padding:14px 20px;font-weight:600;font-family:JetBrains Mono;font-size:14px">Rp{{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td style="padding:14px 20px;font-size:13px;color:{{ $inv->due_date->isPast() && $inv->status !== 'paid' ? 'var(--red)' : 'var(--ink-soft)' }}">{{ $inv->due_date->format('d M Y') }}</td>
                    <td style="padding:14px 20px">
                        <span class="badge {{ $inv->status === 'paid' ? 'badge-paid' : ($inv->status === 'overdue' ? 'badge-late' : 'badge-due') }}">{{ ucfirst($inv->status) }}</span>
                    </td>
                    <td style="padding:14px 20px">
                        <a href="{{ route('customer.portal.invoices.show', $inv->id) }}" class="btn btn-ghost" style="font-size:12px">Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:60px 20px;text-align:center;color:var(--ink-faint);font-size:14px">
                        <div style="margin-bottom:8px;opacity:.4">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m2 0a9 9 0 11-12 0v6a9 9 0 0112 0z"/></svg>
                        </div>
                        Belum ada invoice.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($invoices->hasPages())
<div style="margin-top:20px">
    {{ $invoices->links() }}
</div>
@endif
@endSection
