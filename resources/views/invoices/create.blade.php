@extends('layouts.app')

@section('title', 'Buat Invoice Otomatis')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">Buat Invoice Otomatis</h1>
        <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">Pilih pelanggan — sistem akan menghitung tagihan dari paketnya</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;align-items:start">
    <div class="panel" style="max-width:600px">
        <div class="panel-body">
            <x-errors />

            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf

                <div class="form-field" style="margin-bottom:20px">
                    <label class="form-label">Pilih Pelanggan <span style="color:var(--red)">*</span></label>
                    <select name="customer_id" id="customer-select" required class="form-input">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                data-package-name="{{ $customer->package?->name }}"
                                data-package-price="{{ $customer->package?->price }}"
                                data-package-tax="{{ $customer->package?->is_taxable ? '1' : '0' }}"
                                data-join-date="{{ $customer->join_date?->format('Y-m-d') }}"
                                data-billing-cycle="{{ $customer->billing_cycle_day ?? 1 }}"
                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} — {{ $customer->package?->name ?? 'Tanpa Paket' }} (Rp{{ number_format($customer->package?->price ?? 0, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-field" style="margin-bottom:20px">
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="date" name="due_date" id="due-date" value="{{ old('due_date', now()->addDays(5)->format('Y-m-d')) }}" class="form-input">
                    <p style="font-size:12px;color:var(--ink-faint);margin-top:4px">Default: 5 hari dari sekarang</p>
                </div>

                <div class="form-field" style="margin-bottom:20px">
                    <label class="form-label">Diskon</label>
                    <input type="number" name="discount" id="discount-input" value="{{ old('discount', 0) }}" class="form-input" step="0.01" min="0" placeholder="0">
                </div>

                <div class="form-field" style="margin-bottom:24px">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-input" rows="2" placeholder="Catatan opsional...">{{ old('notes') }}</textarea>
                </div>

                <div style="display:flex;gap:12px">
                    <button type="submit" class="btn btn-primary">Buat Invoice</button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline">Batal</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Live Preview --}}
    <div class="panel" style="padding:24px;position:sticky;top:24px">
        <div style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:16px">Preview Invoice</div>

        <div id="preview-empty" style="text-align:center;padding:32px 0;color:var(--ink-faint);font-size:13px">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;opacity:.4"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6M9 13h6M9 17h4"/></svg>
            Pilih pelanggan untuk melihat preview
        </div>

        <div id="preview-content" style="display:none">
            <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:16px">
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">Pelanggan</span>
                    <span id="preview-customer" style="font-weight:500;color:var(--ink)">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">Paket</span>
                    <span id="preview-package" style="font-weight:500;color:var(--ink)">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">Periode</span>
                    <span id="preview-period" style="font-weight:500;color:var(--ink)">—</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">Hari Tagihan</span>
                    <span id="preview-billing-day" style="font-weight:500;color:var(--ink)">—</span>
                </div>
            </div>

            <div style="border-top:1px solid var(--line);padding-top:12px;display:flex;flex-direction:column;gap:8px">
                <div style="display:flex;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">Harga Paket</span>
                    <span id="preview-base-price" style="font-family:JetBrains Mono;color:var(--ink)">—</span>
                </div>
                <div id="preview-prorata-row" style="display:none;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">Prorata</span>
                    <span id="preview-prorata-note" style="font-size:12px;color:var(--amber)">—</span>
                </div>
                <div id="preview-tax-row" style="display:none;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">PPN (11%)</span>
                    <span id="preview-tax" style="font-family:JetBrains Mono;color:var(--ink)">—</span>
                </div>
                <div id="preview-discount-row" style="display:none;justify-content:space-between;font-size:13px">
                    <span style="color:var(--ink-soft)">Diskon</span>
                    <span id="preview-discount" style="font-family:JetBrains Mono;color:var(--green)">—</span>
                </div>
            </div>

            <div style="border-top:2px solid var(--line);margin-top:12px;padding-top:12px;display:flex;justify-content:space-between;font-size:15px;font-weight:700">
                <span style="color:var(--ink)">Total</span>
                <span id="preview-total" style="font-family:JetBrains Mono;color:var(--primary)">—</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select = document.getElementById('customer-select');
    const discountInput = document.getElementById('discount-input');
    const now = new Date();

    function formatRp(n) {
        return 'Rp' + n.toLocaleString('id-ID');
    }

    function updatePreview() {
        const opt = select.options[select.selectedIndex];
        const empty = document.getElementById('preview-empty');
        const content = document.getElementById('preview-content');

        if (!opt || !opt.value) {
            empty.style.display = '';
            content.style.display = 'none';
            return;
        }

        empty.style.display = 'none';
        content.style.display = '';

        const pkgName = opt.dataset.packageName || 'Tanpa Paket';
        const pkgPrice = parseFloat(opt.dataset.packagePrice) || 0;
        const isTaxable = opt.dataset.packageTax === '1';
        const joinDate = opt.dataset.joinDate;
        const billingCycle = opt.dataset.billingCycle || 1;

        document.getElementById('preview-customer').textContent = opt.textContent.split('—')[0].trim();
        document.getElementById('preview-package').textContent = pkgName + ' — ' + formatRp(pkgPrice);
        document.getElementById('preview-period').textContent = now.toLocaleDateString('id-ID', {month:'long', year:'numeric'});

        const billingDayText = 'Tanggal ' + billingCycle + ' setiap bulan';
        document.getElementById('preview-billing-day').textContent = billingDayText;

        let amount = pkgPrice;
        let prorated = false;
        let prorataDays = 0;
        let totalDaysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();

        if (joinDate) {
            const jd = new Date(joinDate);
            if (jd.getMonth() === now.getMonth() && jd.getFullYear() === now.getFullYear()) {
                const dailyRate = pkgPrice / totalDaysInMonth;
                prorataDays = totalDaysInMonth - jd.getDate() + 1;
                amount = Math.round(dailyRate * prorataDays * 100) / 100;
                prorated = true;
            }
        }

        document.getElementById('preview-base-price').textContent = formatRp(amount);

        const prorataRow = document.getElementById('preview-prorata-row');
        if (prorated) {
            prorataRow.style.display = '';
            document.getElementById('preview-prorata-note').textContent = prorataDays + ' hari dari ' + totalDaysInMonth + ' hari';
        } else {
            prorataRow.style.display = 'none';
        }

        let tax = 0;
        const taxRow = document.getElementById('preview-tax-row');
        if (isTaxable) {
            tax = Math.round(amount * 0.11 * 100) / 100;
            taxRow.style.display = '';
            document.getElementById('preview-tax').textContent = formatRp(tax);
        } else {
            taxRow.style.display = 'none';
        }

        let discount = parseFloat(discountInput.value) || 0;
        const discountRow = document.getElementById('preview-discount-row');
        if (discount > 0) {
            discountRow.style.display = '';
            document.getElementById('preview-discount').textContent = '-' + formatRp(discount);
        } else {
            discountRow.style.display = 'none';
        }

        let total = amount + tax - discount;
        document.getElementById('preview-total').textContent = formatRp(Math.max(0, Math.round(total)));
    }

    select.addEventListener('change', updatePreview);
    discountInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>
@endpush
@endsection
