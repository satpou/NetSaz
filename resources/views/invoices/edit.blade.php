@extends('layouts.app')

@section('title', 'Edit Invoice')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">Edit Invoice</h1>
        <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">{{ $invoice->invoice_number }}</p>
    </div>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
        Kembali
    </a>
</div>

<div class="panel" style="max-width:700px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="form-field" style="margin-bottom:20px">
                <label class="form-label">Pelanggan <span style="color:var(--red)">*</span></label>
                <select name="customer_id" required class="form-input">
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $invoice->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} — {{ $customer->package?->name ?? 'Tanpa Paket' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div class="form-field">
                    <label class="form-label">Periode Mulai <span style="color:var(--red)">*</span></label>
                    <input type="date" name="period_start" value="{{ old('period_start', $invoice->period_start?->format('Y-m-d')) }}" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label">Periode Selesai <span style="color:var(--red)">*</span></label>
                    <input type="date" name="period_end" value="{{ old('period_end', $invoice->period_end?->format('Y-m-d')) }}" required class="form-input">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px">
                <div class="form-field">
                    <label class="form-label">Jumlah <span style="color:var(--red)">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $invoice->amount) }}" required class="form-input" step="0.01" min="0">
                </div>
                <div class="form-field">
                    <label class="form-label">Diskon</label>
                    <input type="number" name="discount" value="{{ old('discount', $invoice->discount) }}" class="form-input" step="0.01" min="0">
                </div>
                <div class="form-field">
                    <label class="form-label">Pajak</label>
                    <input type="number" name="tax" value="{{ old('tax', $invoice->tax) }}" class="form-input" step="0.01" min="0">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div class="form-field">
                    <label class="form-label">Jatuh Tempo <span style="color:var(--red)">*</span></label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        <option value="unpaid" {{ old('status', $invoice->status) == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                        <option value="paid" {{ old('status', $invoice->status) == 'paid' ? 'selected' : '' }}>Lunas</option>
                        <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>Batal</option>
                    </select>
                </div>
            </div>

            <div class="form-field" style="margin-bottom:24px">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-input" rows="3">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
