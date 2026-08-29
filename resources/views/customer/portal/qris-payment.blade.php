@extends('layouts.portal')

@section('title', 'Bayar QRIS - ' . $invoice->invoice_number)

@section('content')
<a href="{{ route('customer.portal.invoices.show', $invoice->id) }}" style="font-size:13px;color:var(--ink-faint);text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:20px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    Kembali ke invoice
</a>

<div style="display:grid;grid-template-columns:1fr 1.2fr;gap:20px;align-items:start">
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px;display:flex;flex-direction:column;align-items:center;text-align:center">
        <h1 style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:4px">Scan QRIS</h1>
        <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px">
            {{ $invoice->invoice_number }}<br>
            {{ $invoice->customer->name ?? '' }}
        </p>
        <img src="{{ $qrDataUri }}" alt="QRIS" style="width:260px;height:260px;border-radius:12px;border:1px solid var(--line);background:#fff;padding:8px">
        <div style="font-family:JetBrains Mono;font-size:20px;font-weight:700;color:var(--ink);margin-top:16px">Rp{{ number_format($payment->amount, 0, ',', '.') }}</div>
        <p style="font-size:12px;color:var(--ink-faint);margin-top:10px">Scan dengan aplikasi pembayaran mana pun yang mendukung QRIS (GoPay, OVO, DANA, m-Banking, dll).</p>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
            <h3 style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:8px">Sudah membayar?</h3>
            <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px">Setelah menyelesaikan pembayaran, klik tombol di bawah untuk memeriksa status secara otomatis.</p>
            <form method="POST" action="{{ route('customer.portal.invoices.pay.qris.check', $invoice->id) }}">
                @csrf
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:14px">
                    Cek Status Pembayaran
                </button>
            </form>
        </div>

        <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
            <h3 style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:12px">Detail</h3>
            <table style="width:100%;font-size:13px;color:var(--ink-soft)">
                <tr>
                    <td style="padding:6px 0">Referensi</td>
                    <td style="padding:6px 0;text-align:right;font-family:JetBrains Mono;color:var(--ink);font-size:12px">{{ $payment->gateway_reference }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0">Metode</td>
                    <td style="padding:6px 0;text-align:right">QRIS</td>
                </tr>
                <tr>
                    <td style="padding:6px 0">Status</td>
                    <td style="padding:6px 0;text-align:right"><span style="background:var(--amber-tint);color:var(--amber);border-radius:4px;padding:2px 8px;font-size:12px">Menunggu pembayaran</span></td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
