@extends('layouts.app')

@section('title', 'Invoice')

@section('content')
<x-page-header title="Invoice" subtitle="{{ $invoices->total() }} invoice">
    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Buat Invoice
    </a>
</x-page-header>

<div class="panel" style="padding:24px;margin-bottom:24px">
    <form method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px">
        <div style="flex:1;min-width:200px">
            <label class="form-label">Bulan</label>
            <input type="month" name="month" value="{{ request('month') }}" class="form-input">
        </div>
        <div style="min-width:160px">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Batal</option>
            </select>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-outline">Filter</button>
            @if(request()->has('month') || request()->has('status'))
                <a href="{{ route('invoices.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="panel" style="overflow:hidden;padding:0">
    <table class="data-table">
        <thead>
            <tr>
                <th style="padding:18px 20px 12px 28px">Invoice</th>
                <th style="padding:18px 20px 12px">Pelanggan</th>
                <th style="padding:18px 20px 12px">Jatuh Tempo</th>
                <th style="padding:18px 20px 12px;text-align:right">Total</th>
                <th style="padding:18px 20px 12px;text-align:center">Status</th>
                <th style="padding:18px 28px 12px;text-align:right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
            <tr>
                <td style="padding:18px 20px 18px 28px">
                    <div style="font-weight:600;color:var(--ink);margin-bottom:2px">{{ $invoice->invoice_number }}</div>
                    <div style="font-size:13px;color:var(--ink-soft)">{{ $invoice->created_at->format('d M Y') }}</div>
                </td>
                <td style="padding:18px 20px">
                    <div style="color:var(--ink)">{{ $invoice->customer->name }}</div>
                </td>
                <td style="padding:18px 20px">
                    <div style="color:var(--ink)">{{ $invoice->due_date->format('d M Y') }}</div>
                </td>
                <td style="padding:18px 20px;text-align:right">
                    <div style="font-weight:600;color:var(--ink)">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                </td>
                <td style="padding:18px 20px;text-align:center">
                    @if($invoice->status == 'paid')
                        <span class="badge badge-paid">Lunas</span>
                    @elseif($invoice->status == 'cancelled')
                        <span class="badge badge-ghost">Batal</span>
                    @else
                        <span class="badge badge-due">Belum Bayar</span>
                    @endif
                </td>
                <td style="padding:18px 28px 18px 20px;text-align:right">
                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-ghost" style="padding:8px;color:var(--primary)" title="Detail">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:60px 20px;text-align:center">
                    <x-empty-state title="Belum ada invoice" description="Belum ada invoice yang dibuat. Buat invoice untuk menagih pelanggan.">
                        <x-slot:icon>
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-faint)"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </x-slot:icon>
                        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                            Buat Invoice Pertama
                        </a>
                    </x-empty-state>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($invoices->hasPages())
<div style="margin-top:24px">
    {{ $invoices->links() }}
</div>
@endif
@endsection
