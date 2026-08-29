<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        .total { font-size: 16px; font-weight: bold; text-align: right; }
        .footer { text-align: center; margin-top: 40px; color: #999; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p>{{ $invoice->invoice_number }}</p>
    </div>
    <p><strong>Kepada:</strong> {{ $invoice->customer->name }}</p>
    <p><strong>Periode:</strong> {{ $invoice->period_start?->format('d M Y') }} — {{ $invoice->period_end?->format('d M Y') }}</p>
    <p><strong>Jatuh Tempo:</strong> {{ $invoice->due_date?->format('d M Y') }}</p>

    <table>
        <thead>
            <tr><th>Deskripsi</th><th style="text-align:right">Jumlah</th></tr>
        </thead>
        <tbody>
            @forelse($invoice->invoiceItems as $item)
            <tr>
                <td>{{ $item->description }} @if($item->days)({{ $item->days }} hari)@endif</td>
                <td style="text-align:right">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td>{{ $invoice->package->name ?? 'Paket' }}</td>
                <td style="text-align:right">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            @if($invoice->discount > 0)
            <tr><td><strong>Diskon</strong></td><td style="text-align:right">-Rp{{ number_format($invoice->discount, 0, ',', '.') }}</td></tr>
            @endif
            @if($invoice->tax > 0)
            <tr><td><strong>Pajak</strong></td><td style="text-align:right">Rp{{ number_format($invoice->tax, 0, ',', '.') }}</td></tr>
            @endif
            <tr><td><strong>Total</strong></td><td style="text-align:right"><strong>Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</strong></td></tr>
        </tfoot>
    </table>

    <p>Terima kasih atas kepercayaan Anda.</p>
    <div class="footer">{{ config('app.name') }} — Invoice generated on {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
