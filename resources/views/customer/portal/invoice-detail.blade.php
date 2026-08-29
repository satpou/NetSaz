@extends('layouts.portal')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')
<a href="{{ route('customer.portal.invoices') }}" style="font-size:13px;color:var(--ink-faint);text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:20px">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7l-7 7 7 7"/></svg>
    Kembali
</a>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:20px">
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px">
            <div>
                <h1 style="font-size:20px;font-weight:700;color:var(--ink);margin-bottom:4px">{{ $invoice->invoice_number }}</h1>
                <p style="font-size:13px;color:var(--ink-soft)">{{ $invoice->customer->name ?? '' }}</p>
            </div>
            <span class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : ($invoice->status === 'overdue' ? 'badge-late' : 'badge-due') }}" style="font-size:13px">{{ ucfirst($invoice->status) }}</span>
        </div>

        @if($invoice->period_start && $invoice->period_end)
        <div style="font-size:13px;color:var(--ink-soft);margin-bottom:20px;padding:10px 14px;background:var(--bg);border-radius:8px">
            Periode: {{ $invoice->period_start->format('d M Y') }} — {{ $invoice->period_end->format('d M Y') }}
        </div>
        @endif

        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="border-bottom:1px solid var(--line)">
                    <th style="padding:10px 0;text-align:left;font-size:12px;color:var(--ink-faint);font-weight:500">Deskripsi</th>
                    <th style="padding:10px 0;text-align:right;font-size:12px;color:var(--ink-faint);font-weight:500">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->invoiceItems as $item)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:12px 0;font-size:14px;color:var(--ink)">
                        {{ $item->description }}
                        @if($item->days)<div style="font-size:12px;color:var(--ink-faint)">{{ $item->days }} hari</div>@endif
                    </td>
                    <td style="padding:12px 0;text-align:right;font-family:JetBrains Mono;font-size:14px;color:var(--ink)">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:12px 0;font-size:14px;color:var(--ink-soft)" colspan="2">{{ $invoice->package->name ?? 'Paket' }}</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td style="padding:12px 0;font-size:13px;color:var(--ink-soft)">Subtotal</td>
                    <td style="padding:12px 0;text-align:right;font-family:JetBrains Mono;font-size:14px;color:var(--ink)">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->discount > 0)
                <tr>
                    <td style="padding:4px 0;font-size:13px;color:var(--ink-soft)">Diskon</td>
                    <td style="padding:4px 0;text-align:right;font-family:JetBrains Mono;font-size:14px;color:var(--red)">-Rp{{ number_format($invoice->discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->tax > 0)
                <tr>
                    <td style="padding:4px 0;font-size:13px;color:var(--ink-soft)">Pajak</td>
                    <td style="padding:4px 0;text-align:right;font-family:JetBrains Mono;font-size:14px;color:var(--ink)">Rp{{ number_format($invoice->tax, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:12px 0;font-size:15px;font-weight:700;color:var(--ink);border-top:2px solid var(--line)">Total</td>
                    <td style="padding:12px 0;text-align:right;font-family:JetBrains Mono;font-size:18px;font-weight:700;color:var(--ink);border-top:2px solid var(--line)">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top:20px;font-size:13px;color:var(--ink-faint)">
            Jatuh tempo: <strong style="color:{{ $invoice->due_date->isPast() && $invoice->status !== 'paid' ? 'var(--red)' : 'var(--ink)' }}">{{ $invoice->due_date->format('d M Y') }}</strong>
        </div>
    </div>

    <div>
        <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px;margin-bottom:16px">
            <h3 style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:12px">Pembayaran</h3>
            @if($invoice->status === 'paid')
                <div style="background:var(--green-tint);border-radius:8px;padding:12px;font-size:13px;color:var(--green);display:flex;align-items:center;gap:8px">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4 10-10"/></svg>
                    Invoice sudah lunas
                </div>
            @else
                <p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px">Lakukan pembayaran sebelum jatuh tempo.</p>
                <div style="display:flex;flex-direction:column;gap:10px">
                    @if($hasGateway)
                    <form method="POST" action="{{ route('customer.portal.invoices.pay', $invoice->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:14px">
                            Bayar via Gateway (Card / VA / E-wallet)
                        </button>
                    </form>
                    @endif

                    @if(!empty($invoice->tenant->settings['xendit_api_key'] ?? null))
                    <form method="POST" action="{{ route('customer.portal.invoices.pay.qris', $invoice->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline" style="width:100%;justify-content:center;font-size:13px;display:flex;align-items:center;gap:6px">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM20 14h1M17 17h4M17 20h4"/></svg>
                            Bayar via QRIS
                        </button>
                    </form>
                    @endif

                    <div style="margin-top:12px">
                        <a href="{{ route('customer.portal.invoices.download', $invoice->id) }}" class="btn btn-outline" style="width:100%;justify-content:center;font-size:13px;display:flex;align-items:center;gap:6px">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4m4-5l5 5 5-5m-5 5V3"/></svg>
                            Download PDF
                        </a>
                    </div>
                </div>
            @endif
        </div>

        @if($bankAccounts->isNotEmpty() && $invoice->status !== 'paid')
        <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px;margin-bottom:16px">
            <h3 style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:4px">Transfer Manual</h3>
            <p style="font-size:12px;color:var(--ink-faint);margin-bottom:14px">Transfer ke rekening berikut, lalu upload bukti untuk diverifikasi.</p>
            <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:16px">
                @foreach($bankAccounts as $acc)
                <div style="padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--bg)">
                    <div style="font-size:13px;font-weight:600;color:var(--ink)">{{ $acc['bank'] }}</div>
                    <div style="font-size:12px;color:var(--ink-soft)">{{ $acc['account_name'] ?? '' }}</div>
                    <div style="font-family:JetBrains Mono;font-size:14px;font-weight:600;color:var(--ink);margin-top:2px">{{ $acc['account_number'] }}</div>
                </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('customer.portal.invoices.pay.manual', $invoice->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="payment_method" value="transfer">
                <div style="margin-bottom:10px">
                    <label style="display:block;font-size:12px;font-weight:500;color:var(--ink-soft);margin-bottom:4px">Nomor Referensi / Bukti Transfer</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number') }}" placeholder="Nomor referensi transfer"
                           class="form-input" style="width:100%;font-size:13px">
                </div>
                <div style="margin-bottom:10px">
                    <label style="display:block;font-size:12px;font-weight:500;color:var(--ink-soft);margin-bottom:4px">Catatan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan tambahan (opsional)" class="form-input" style="width:100%;font-size:13px">{{ old('notes') }}</textarea>
                </div>
                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;font-weight:500;color:var(--ink-soft);margin-bottom:4px">Upload Bukti Transfer <span style="color:var(--red)">*</span></label>
                    <input type="file" name="proof_of_payment" accept="image/jpeg,image/png" required class="form-input" style="width:100%;font-size:13px">
                    @error('proof_of_payment')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;font-size:13px">
                    Kirim Bukti Pembayaran
                </button>
            </form>
        </div>
        @endif

        @if($invoice->payments->count() > 0)
        <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
            <h3 style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:12px">Riwayat Bayar</h3>
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($invoice->payments as $pmt)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--line)">
                    <div>
                        <div style="font-size:13px;color:var(--ink)">
                            {{ $pmt->payment_method }}
                            @if($pmt->status === 'pending')
                                <span style="font-size:11px;background:var(--yellow-tint, #fef9c3);color:#a16207;border-radius:4px;padding:2px 6px;margin-left:6px">Menunggu verifikasi</span>
                            @elseif($pmt->status === 'success')
                                <span style="font-size:11px;background:var(--green-tint);color:var(--green);border-radius:4px;padding:2px 6px;margin-left:6px">Lunas</span>
                            @elseif(in_array($pmt->status, ['failed','expired','refunded']))
                                <span style="font-size:11px;background:#fee2e2;color:#b91c1c;border-radius:4px;padding:2px 6px;margin-left:6px">{{ ucfirst($pmt->status) }}</span>
                            @endif
                        </div>
                        <div style="font-size:12px;color:var(--ink-faint)">{{ $pmt->created_at->format('d M Y') }}</div>
                    </div>
                    <span style="font-family:JetBrains Mono;font-size:14px;font-weight:600;color:{{ $pmt->status === 'success' ? 'var(--green)' : 'var(--ink-soft)' }}">Rp{{ number_format($pmt->amount, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endSection
