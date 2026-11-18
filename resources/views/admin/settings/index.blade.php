@extends('layouts.app')

@section('title', 'Pengaturan Platform & Rekening Bank')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <span>Super Admin</span> &gt;
    <b>Pengaturan Platform</b>
</div>

<x-page-header title="Pengaturan Platform & Rekening Bank" subtitle="Kustomisasi rekening bank tujuan transfer manual, kredensial payment gateway Midtrans, dan kontak sales">
    <a href="{{ route('admin.verifications.index') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Lihat Verifikasi Pembayaran
    </a>
</x-page-header>

@if(session('success'))
<div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    <div style="display:grid;grid-template-columns:1.2fr 1fr;gap:24px" x-data="{
        banks: {{ json_encode($bankAccounts) }},
        addBank() {
            this.banks.push({ bank: '', account_number: '', account_name: '' });
        },
        removeBank(index) {
            if (this.banks.length > 1) {
                this.banks.splice(index, 1);
            } else {
                alert('Minimal harus ada satu rekening bank.');
            }
        }
    }">
        {{-- KOLOM KIRI: REKENING BANK TRANSFER MANUAL --}}
        <div>
            <div class="panel" style="padding:24px;margin-bottom:24px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;border-bottom:1px solid var(--line);padding-bottom:12px">
                    <div>
                        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0 0 4px">Rekening Bank Tujuan Transfer</h3>
                        <p style="font-size:12.5px;color:var(--ink-soft);margin:0">Nomor rekening ini akan ditampilkan kepada calon tenant yang mendaftar paket langganan tahunan.</p>
                    </div>
                    <button type="button" @click="addBank()" class="btn btn-outline" style="font-size:12.5px;padding:6px 12px;display:inline-flex;align-items:center;gap:4px">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        Tambah Rekening
                    </button>
                </div>

                <template x-for="(b, idx) in banks" :key="idx">
                    <div style="background:var(--bg-alt);border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:14px">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                            <span style="font-size:13px;font-weight:700;color:var(--ink)" x-text="'Rekening #' + (idx + 1)"></span>
                            <button type="button" @click="removeBank(idx)" style="background:none;border:none;color:#EF4444;font-size:12px;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                Hapus
                            </button>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:10px">
                            <div>
                                <label style="display:block;font-size:12px;color:var(--ink-soft);margin-bottom:4px">Nama Bank</label>
                                <input type="text" :name="'banks[' + idx + '][bank]'" x-model="b.bank" placeholder="Contoh: BCA / Mandiri / BRI / BNI" required class="form-input" style="width:100%;font-size:13px;padding:8px 12px">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;color:var(--ink-soft);margin-bottom:4px">Nomor Rekening</label>
                                <input type="text" :name="'banks[' + idx + '][account_number]'" x-model="b.account_number" placeholder="Nomor rekening tanpa spasi" required class="form-input" style="width:100%;font-size:13px;padding:8px 12px;font-family:'JetBrains Mono',monospace">
                            </div>
                        </div>

                        <div>
                            <label style="display:block;font-size:12px;color:var(--ink-soft);margin-bottom:4px">Atas Nama (Pemilik Rekening)</label>
                            <input type="text" :name="'banks[' + idx + '][account_name]'" x-model="b.account_name" placeholder="Nama pemilik rekening" required class="form-input" style="width:100%;font-size:13px;padding:8px 12px">
                        </div>
                    </div>
                </template>

                <div style="margin-top:16px">
                    <label style="display:block;font-size:12.5px;color:var(--ink-soft);margin-bottom:6px">Instruksi Pembayaran Tambahan (Opsional)</label>
                    <textarea name="payment_instructions" rows="3" class="form-input" style="width:100%;font-size:13px;padding:10px 12px" placeholder="Contoh: Cantumkan nama ISP pada berita transfer...">{{ old('payment_instructions', $paymentInstructions) }}</textarea>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: MIDTRANS GATEWAY & SALES CONTACT --}}
        <div>
            {{-- MIDTRANS GATEWAY PLATFORM --}}
            <div class="panel" style="padding:24px;margin-bottom:24px">
                <div style="margin-bottom:16px;border-bottom:1px solid var(--line);padding-bottom:12px">
                    <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0 0 4px">Payment Gateway Midtrans (Otomatis)</h3>
                    <p style="font-size:12.5px;color:var(--ink-soft);margin:0">Jika diisi, calon tenant bisa langsung bayar otomatis via QRIS / Virtual Account dan akun aktif seketika.</p>
                </div>

                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:12px;color:var(--ink-soft);margin-bottom:4px">Midtrans Server Key</label>
                    <input type="text" name="midtrans_server_key" value="{{ old('midtrans_server_key', $midtransServerKey) }}" placeholder="SB-Mid-server-..." class="form-input" style="width:100%;font-size:13px;padding:8px 12px;font-family:'JetBrains Mono',monospace">
                </div>

                <div style="margin-bottom:14px">
                    <label style="display:block;font-size:12px;color:var(--ink-soft);margin-bottom:4px">Midtrans Client Key</label>
                    <input type="text" name="midtrans_client_key" value="{{ old('midtrans_client_key', $midtransClientKey) }}" placeholder="SB-Mid-client-..." class="form-input" style="width:100%;font-size:13px;padding:8px 12px;font-family:'JetBrains Mono',monospace">
                </div>

                <div style="margin-bottom:14px;background:var(--bg-alt);border:1px solid var(--line);border-radius:8px;padding:10px 14px">
                    <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--ink);cursor:pointer">
                        <input type="checkbox" name="midtrans_is_production" value="1" {{ $midtransIsProduction ? 'checked' : '' }} style="width:16px;height:16px">
                        <span>Gunakan Mode <b>Production</b> (Bukan Sandbox)</span>
                    </label>
                </div>

                <div style="font-size:12px;color:var(--ink-faint);line-height:1.5">
                    <b>URL Webhook Subscription:</b><br>
                    <code style="background:var(--bg);padding:4px 8px;border-radius:4px;display:block;margin-top:4px;word-break:break-all">https://netsaz.web.id/webhook/midtrans-subscription</code>
                    <span style="font-size:11px;color:var(--ink-faint);display:block;margin-top:2px">Daftarkan URL ini di Dashboard Midtrans &gt; Settings &gt; Configuration.</span>
                </div>
            </div>

            {{-- SALES CONSULTANT CONTACT --}}
            <div class="panel" style="padding:24px;margin-bottom:24px">
                <div style="margin-bottom:16px;border-bottom:1px solid var(--line);padding-bottom:12px">
                    <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0 0 4px">Kontak Sales &amp; Konfirmasi WhatsApp</h3>
                    <p style="font-size:12.5px;color:var(--ink-soft);margin:0">Nomor ini tampil pada halaman pembayaran sebagai tombol konfirmasi cepat.</p>
                </div>

                <div style="margin-bottom:12px">
                    <label style="display:block;font-size:12px;color:var(--ink-soft);margin-bottom:4px">Nomor WhatsApp Sales / Admin</label>
                    <input type="text" name="sales_whatsapp" value="{{ old('sales_whatsapp', $salesWhatsapp) }}" placeholder="083162238930" class="form-input" style="width:100%;font-size:13px;padding:8px 12px">
                </div>

                <div>
                    <label style="display:block;font-size:12px;color:var(--ink-soft);margin-bottom:4px">Nama Sales Consultant</label>
                    <input type="text" name="sales_name" value="{{ old('sales_name', $salesName) }}" placeholder="Ahmad Zidnan" class="form-input" style="width:100%;font-size:13px;padding:8px 12px">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;padding:12px;font-size:14px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Simpan Semua Pengaturan Platform
            </button>
        </div>
    </div>
</form>
@endsection
