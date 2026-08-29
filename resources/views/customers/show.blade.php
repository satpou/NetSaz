@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@section('content')
<div x-data="{ copied: false }">
    <x-page-header :title="$customer->name" :subtitle="'Bergabung ' . ($customer->join_date?->format('d M Y') ?? '-')">
        <div style="display:flex;gap:10px;flex-wrap:wrap">
            <form action="{{ route('customers.send-portal-link', $customer->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    Kirim Link Portal
                </button>
            </form>
            <form action="{{ route('customers.send-welcome', $customer->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                    Kirim Selamat Datang
                </button>
            </form>
            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
        </div>
    </x-page-header>

    <div class="panel" style="padding:24px;margin-bottom:20px">
        <div class="panel-head">
            <h3 style="font-size:16px;font-weight:600">Portal Pelanggan</h3>
        </div>
        <p style="font-size:13px;color:var(--ink-soft);margin:4px 0 16px">Klik tombol "Kirim Link Portal" untuk mengirim link langsung masuk + PIN ke WhatsApp pelanggan. Pelanggan juga bisa login manual dengan No HP + PIN.</p>
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:var(--bg);border:1px solid var(--line);border-radius:10px;padding:12px 16px">
            <a href="{{ $customer->portalUrl() }}" target="_blank" rel="noopener" class="mono" style="font-size:13px;color:var(--primary);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $customer->portalUrl() }}</a>
            <button type="button" @click="navigator.clipboard.writeText('{{ $customer->portalUrl() }}').then(()=>{copied=true;setTimeout(()=>copied=false,1500)})" class="btn btn-ghost" style="padding:8px 12px;flex-shrink:0">
                <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
            </button>
        </div>
    </div>

    <div class="lower-grid">
        <div class="panel" style="padding:24px">
            <div class="panel-head">
                <h3 style="font-size:16px;font-weight:600">Informasi Pelanggan</h3>
            </div>
            <div style="margin-top:8px">
                <div class="summary-row">
                    <span class="summary-label">Email</span>
                    <span class="summary-value" style="font-family:'Inter';font-weight:500">{{ $customer->email ?: '-' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Telepon</span>
                    <span class="summary-value" style="font-family:'Inter';font-weight:500">{{ $customer->phone ?: '-' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">No. KTP</span>
                    <span class="summary-value" style="font-family:'Inter';font-weight:500">{{ $customer->ktp_id ?: '-' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Alamat</span>
                    <span class="summary-value" style="font-family:'Inter';font-weight:500;text-align:right;max-width:60%">{{ $customer->address ?: '-' }}</span>
                </div>
                @if($customer->area)
                <div class="summary-row">
                    <span class="summary-label">Area</span>
                    <span class="badge badge-primary">{{ $customer->area }}</span>
                </div>
                @endif
                @if($customer->latitude && $customer->longitude)
                <div class="summary-row">
                    <span class="summary-label">Koordinat</span>
                    <span class="summary-value">{{ $customer->latitude }}, {{ $customer->longitude }}</span>
                </div>
                @endif
                <div class="summary-row">
                    <span class="summary-label">Paket</span>
                    <span class="summary-value" style="font-family:'Inter';font-weight:500">{{ $customer->package?->name ?? '-' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Status</span>
                    @if($customer->status == 'active')
                        <span class="badge badge-paid">Aktif</span>
                    @elseif($customer->status == 'isolated')
                        <span class="badge badge-late">Isolir</span>
                    @else
                        <span class="badge" style="background:var(--bg);color:var(--ink-soft);border:1px solid var(--line)">Suspend</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="panel" style="padding:24px">
            <div class="panel-head">
                <h3 style="font-size:16px;font-weight:600">Invoice</h3>
                <span class="mono" style="font-size:18px;font-weight:600">{{ $customer->invoices->count() }}</span>
            </div>
            <p class="panel-sub">total invoice</p>

            @if($customer->invoices->count() > 0)
                <div style="display:flex;flex-direction:column">
                    @foreach($customer->invoices->take(5) as $invoice)
                    <a href="{{ route('invoices.show', $invoice->id) }}" style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-top:1px solid var(--line);transition:background .15s">
                        <div>
                            <div style="font-size:13.5px;font-weight:500;color:var(--ink)">{{ $invoice->invoice_number }}</div>
                            <div style="font-size:12px;color:var(--ink-faint);margin-top:2px">{{ $invoice->due_date->format('d M Y') }}</div>
                        </div>
                        <div class="mono" style="font-size:13.5px;font-weight:600;color:var(--ink)">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                    </a>
                    @endforeach
                </div>
            @else
                <div style="text-align:center;padding:24px 0;color:var(--ink-faint);font-size:13.5px">Belum ada invoice</div>
            @endif
        </div>
    </div>
</div>
@endsection
