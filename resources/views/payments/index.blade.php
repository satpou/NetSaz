@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<x-page-header title="Pembayaran" subtitle="{{ $payments->total() }} transaksi">
    <a href="{{ route('payments.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Catat Pembayaran
    </a>
</x-page-header>

<div class="panel" style="padding:24px;margin-bottom:24px">
    <form method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px">
        <div style="flex:1;min-width:200px">
            <label class="form-label">Cari Pembayaran</label>
            <input type="text" name="search" placeholder="No. invoice atau pelanggan..." value="{{ request('search') }}" class="form-input" autocomplete="off">
        </div>
        <div style="min-width:160px">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sukses</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-outline">Filter</button>
            @if(request()->has('search') || request()->has('status'))
                <a href="{{ route('payments.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="panel" style="overflow:hidden;padding:0">
    <table class="data-table">
        <thead>
            <tr>
                <th style="padding:18px 20px 12px 28px">Tanggal</th>
                <th style="padding:18px 20px 12px">Pelanggan</th>
                <th style="padding:18px 20px 12px">Invoice</th>
                <th style="padding:18px 20px 12px;text-align:right">Jumlah</th>
                <th style="padding:18px 20px 12px;text-align:center">Status</th>
                <th style="padding:18px 28px 12px;text-align:right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td style="padding:18px 20px 18px 28px">
                    <div style="color:var(--ink);margin-bottom:2px">{{ $payment->created_at->format('d M Y') }}</div>
                    <div style="font-size:13px;color:var(--ink-soft)">{{ $payment->created_at->format('H:i') }}</div>
                </td>
                <td style="padding:18px 20px">
                    <div style="color:var(--ink)">{{ $payment->invoice->customer->name }}</div>
                </td>
                <td style="padding:18px 20px">
                    <div style="font-family:'JetBrains Mono';font-size:13px">{{ $payment->invoice->invoice_number }}</div>
                </td>
                <td style="padding:18px 20px;text-align:right">
                    <div style="font-weight:600;color:var(--ink)">Rp{{ number_format($payment->amount, 0, ',', '.') }}</div>
                    <div style="font-size:12px;color:var(--ink-soft);text-transform:uppercase;margin-top:2px">{{ $payment->payment_method }}</div>
                </td>
                <td style="padding:18px 20px;text-align:center">
                    @if($payment->status == 'success')
                        <span class="badge badge-paid">Sukses</span>
                    @elseif(in_array($payment->status, ['failed', 'expired', 'refunded']))
                        <span class="badge badge-late">Ditolak</span>
                    @else
                        <span class="badge badge-ghost">Pending</span>
                    @endif
                </td>
                <td style="padding:18px 28px 18px 20px;text-align:right;white-space:nowrap">
                    <a href="{{ route('payments.receipt', $payment->id) }}" style="font-size:13px;color:var(--primary);text-decoration:none;font-weight:500;margin-right:12px">Kuitansi</a>
                    <a href="{{ route('payments.show', $payment->id) }}" style="font-size:13px;color:var(--primary);text-decoration:none;font-weight:500">Lihat</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding:60px 20px;text-align:center">
                    <x-empty-state title="Belum ada pembayaran" description="Belum ada transaksi pembayaran yang tercatat.">
                        <x-slot:icon>
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-faint)"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </x-slot:icon>
                        <a href="{{ route('payments.create') }}" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                            Catat Pembayaran Pertama
                        </a>
                    </x-empty-state>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($payments->hasPages())
<div style="margin-top:24px">
    {{ $payments->links() }}
</div>
@endif
@endsection
