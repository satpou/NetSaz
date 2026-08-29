<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kuitansi {{ $payment->payment_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 28px; }
        .header h1 { font-size: 22px; margin-bottom: 4px; }
        .header .sub { color: #777; font-size: 12px; }
        .kwitansi { font-size: 18px; font-weight: bold; letter-spacing: 4px; margin: 16px 0 4px; }
        .row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #eee; }
        .row .label { color: #555; }
        .row .value { font-weight: 600; text-align: right; }
        table { width: 100%; border-collapse: collapse; margin: 18px 0; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; }
        .total { font-size: 16px; font-weight: bold; }
        .footer { text-align: center; margin-top: 32px; color: #999; font-size: 10px; }
        .terbilang { font-style: italic; color: #444; margin: 8px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>KUITANSI PEMBAYARAN</h1>
        <div class="sub">{{ config('app.name') }} — {{ $payment->invoice->tenant?->name ?? config('app.name') }}</div>
    </div>

    <div class="kwitansi">No. {{ $payment->payment_number }}</div>

    <div class="row"><div class="label">Tanggal Pembayaran</div><div class="value">{{ $payment->paid_at?->format('d M Y H:i') ?? $payment->created_at->format('d M Y H:i') }}</div></div>
    <div class="row"><div class="label">No. Invoice</div><div class="value">{{ $payment->invoice->invoice_number }}</div></div>

    <table>
        <thead>
            <tr><th style="width:60%">Keterangan</th><th style="text-align:right">Jumlah</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>Pembayaran tagihan internet<br>
                    <span style="font-size:11px;color:#777">Pelanggan: {{ $payment->customer?->name ?? $payment->invoice->customer->name }}</span><br>
                    <span style="font-size:11px;color:#777">{{ $payment->invoice->package->name ?? '' }}</span>
                </td>
                <td style="text-align:right">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align:right"><strong>Total</strong></td>
                <td style="text-align:right"><strong>Rp{{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="terbilang">Terima kasih atas pembayaran Anda.</div>

    <div class="row"><div class="label">Metode Pembayaran</div><div class="value">{{ strtoupper($payment->payment_method) }}</div></div>
    @if($payment->reference_number)
    <div class="row"><div class="label">No. Referensi</div><div class="value">{{ $payment->reference_number }}</div></div>
    @endif
    @if($payment->status === 'success' && $payment->verifiedBy)
    <div class="row"><div class="label">Diverifikasi oleh</div><div class="value">{{ $payment->verifiedBy->name }}</div></div>
    @endif

    <div class="footer">{{ config('app.name') }} — Kuitansi dicetak pada {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
