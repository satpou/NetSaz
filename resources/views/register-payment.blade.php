@php $hideFooter = true; @endphp
@extends('layouts.auth')

@section('title', 'Pembayaran & Aktivasi - NetSaz')

@push('styles')
@vite(['resources/css/register.css'])
<style>
.pay-wrap {
    max-width: 980px;
    margin: 40px auto;
    padding: 0 20px;
}
.pay-card {
    background: #111827;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
}
.pay-header {
    padding: 28px 32px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.pay-body {
    display: grid;
    grid-template-columns: 1fr 1.25fr;
    gap: 0;
}
@media (max-width: 860px) {
    .pay-body { grid-template-columns: 1fr; }
}
.pay-summary {
    padding: 32px;
    background: rgba(17, 24, 39, 0.6);
    border-right: 1px solid rgba(255, 255, 255, 0.08);
}
.pay-methods {
    padding: 32px;
}
.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    font-size: 13.5px;
}
.summary-item:last-child {
    border-bottom: none;
}
.tab-btn {
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}
.tab-btn.active {
    background: #2563EB;
    color: #fff;
    border-color: #3B82F6;
}
.tab-btn:not(.active) {
    background: rgba(255, 255, 255, 0.05);
    color: #9CA3AF;
}
.bank-box {
    background: rgba(31, 41, 55, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 12px;
}
</style>
@endpush

@section('content')
<div class="pay-wrap" x-data="{ tab: 'manual' }">
    <div style="text-align:center;margin-bottom:28px">
        <a href="{{ route('landing') }}">
            <img src="{{ asset('images/netsaz/NetSaz_logo_white.png') }}" alt="NetSaz" style="height:44px;margin:0 auto;display:inline-block">
        </a>
        <div style="margin-top:14px;display:flex;align-items:center;justify-content:center;gap:12px;font-size:13px;color:#9CA3AF">
            <span style="color:#10B981;font-weight:600">✓ 1. Pendaftaran Data ISP</span>
            <span>→</span>
            <span style="color:#60A5FA;font-weight:700">2. Pembayaran & Aktivasi Akun</span>
        </div>
    </div>

    @if(session('success'))
    <div style="background:rgba(16, 185, 129, 0.15);border:1px solid #10B981;color:#34D399;padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:14px">
        {{ session('success') }}
    </div>
    @endif

    @if(session('info'))
    <div style="background:rgba(59, 130, 246, 0.15);border:1px solid #3B82F6;color:#93C5FD;padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:14px">
        {{ session('info') }}
    </div>
    @endif

    @if(session('warning'))
    <div style="background:rgba(245, 158, 11, 0.15);border:1px solid #F59E0B;color:#FCD34D;padding:14px 20px;border-radius:12px;margin-bottom:20px;font-size:14px">
        {{ session('warning') }}
    </div>
    @endif

    <div class="pay-card">
        <div class="pay-header">
            <div>
                <h2 style="font-size:19px;font-weight:700;color:#fff;margin:0 0 4px">Selesaikan Pembayaran Langganan</h2>
                <p style="font-size:13px;color:#9CA3AF;margin:0">Akun ISP Anda akan aktif setelah pembayaran diverifikasi oleh tim kami.</p>
            </div>
            <div>
                @if(($settings['payment_status'] ?? '') === 'waiting_verification')
                <span style="background:rgba(245, 158, 11, 0.15);color:#FBBF24;border:1px solid rgba(245, 158, 11, 0.3);padding:6px 14px;border-radius:20px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#FBBF24"></span>
                    Menunggu Verifikasi Admin
                </span>
                @else
                <span style="background:rgba(239, 68, 68, 0.15);color:#F87171;border:1px solid rgba(239, 68, 68, 0.3);padding:6px 14px;border-radius:20px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
                    <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#F87171"></span>
                    Menunggu Pembayaran
                </span>
                @endif
            </div>
        </div>

        <div class="pay-body">
            {{-- SUMMARY KIRI --}}
            <div class="pay-summary">
                <h3 style="font-size:15px;font-weight:600;color:#fff;margin:0 0 16px;text-transform:uppercase;letter-spacing:0.5px">Rincian Pesanan</h3>

                <div class="summary-item">
                    <span style="color:#9CA3AF">Nama ISP</span>
                    <span style="font-weight:600;color:#fff">{{ $tenant->name }}</span>
                </div>
                <div class="summary-item">
                    <span style="color:#9CA3AF">Subdomain</span>
                    <span style="font-family:'JetBrains Mono',monospace;color:#60A5FA">{{ $tenant->slug }}.netsaz.web.id</span>
                </div>
                <div class="summary-item">
                    <span style="color:#9CA3AF">Nama PIC</span>
                    <span style="color:#fff">{{ $settings['pic_name'] ?? $tenant->name }}</span>
                </div>
                <div class="summary-item">
                    <span style="color:#9CA3AF">Email Login</span>
                    <span style="color:#fff">{{ $tenant->email }}</span>
                </div>
                <div class="summary-item">
                    <span style="color:#9CA3AF">Paket Langganan</span>
                    <span style="font-weight:600;color:#10B981">{{ $plan ? $plan->name : 'Standar' }} (Tahunan)</span>
                </div>
                <div class="summary-item">
                    <span style="color:#9CA3AF">Kapasitas</span>
                    <span style="color:#fff">{{ $plan ? $plan->max_active_customers . ' Pelanggan' : 'Sesuai Paket' }}</span>
                </div>
                <div class="summary-item" style="border-top:1px dashed rgba(255, 255, 255, 0.15);margin-top:12px;padding-top:16px">
                    <span style="font-size:14px;font-weight:600;color:#fff">Total Tagihan (1 Tahun)</span>
                    <span style="font-size:20px;font-weight:700;color:#60A5FA">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                </div>

                @if(!empty($salesWhatsapp))
                <div style="margin-top:28px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:14px;font-size:12.5px;color:#9CA3AF">
                    <div style="font-weight:600;color:#fff;margin-bottom:4px">Butuh bantuan pendaftaran?</div>
                    <div>Hubungi Sales Consultant ({{ $salesName }}):</div>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $salesWhatsapp) }}?text={{ urlencode('Halo Sales NetSaz, saya mendaftar ISP ' . $tenant->name . ' (' . $tenant->slug . ') dan ingin konfirmasi pembayaran.') }}" target="_blank" rel="noopener" style="color:#34D399;font-weight:600;display:inline-flex;align-items:center;gap:4px;margin-top:6px;text-decoration:none">
                        Chat WhatsApp Sales →
                    </a>
                </div>
                @endif
            </div>

            {{-- METODE PEMBAYARAN KANAN --}}
            <div class="pay-methods">
                <div style="display:flex;gap:10px;margin-bottom:20px">
                    <button type="button" class="tab-btn" :class="{ 'active': tab === 'manual' }" @click="tab = 'manual'">
                        Transfer Bank Manual
                    </button>
                    @if($isMidtransAvailable && $snapToken)
                    <button type="button" class="tab-btn" :class="{ 'active': tab === 'online' }" @click="tab = 'online'">
                        Bayar Online (QRIS / VA)
                    </button>
                    @endif
                </div>

                {{-- TAB 1: TRANSFER MANUAL --}}
                <div x-show="tab === 'manual'">
                    <p style="font-size:13px;color:#9CA3AF;line-height:1.6;margin-bottom:14px">
                        Silakan transfer tepat sejumlah <b style="color:#60A5FA">Rp {{ number_format($amount, 0, ',', '.') }}</b> ke salah satu rekening resmi NetSaz:
                    </p>

                    @foreach($bankAccounts as $acc)
                    <div class="bank-box">
                        <div style="display:flex;align-items:center;justify-content:space-between">
                            <span style="font-weight:700;color:#fff;font-size:14px">{{ $acc['bank'] }}</span>
                            <span style="font-size:11px;color:#9CA3AF;background:rgba(255,255,255,0.08);padding:2px 8px;border-radius:4px">Transfer Bank</span>
                        </div>
                        <div style="margin-top:6px;display:flex;align-items:center;justify-content:space-between">
                            <div>
                                <div style="font-family:'JetBrains Mono',monospace;font-size:17px;font-weight:700;color:#60A5FA;letter-spacing:1px">{{ $acc['account_number'] }}</div>
                                <div style="font-size:12.5px;color:#9CA3AF">a/n {{ $acc['account_name'] }}</div>
                            </div>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $acc['account_number'] }}'); alert('Nomor rekening disalin!');" style="background:rgba(255,255,255,0.08);border:none;color:#fff;padding:6px 12px;border-radius:6px;font-size:12px;cursor:pointer">
                                Salin
                            </button>
                        </div>
                    </div>
                    @endforeach

                    @if(!empty($paymentInstructions))
                    <div style="font-size:12px;color:#9CA3AF;line-height:1.5;margin-bottom:18px;background:rgba(255,255,255,0.03);padding:10px 14px;border-radius:8px">
                        {{ $paymentInstructions }}
                    </div>
                    @endif

                    {{-- FORM UNGGAH BUKTI TRANSFER --}}
                    <div style="border-top:1px solid rgba(255,255,255,0.08);padding-top:20px;margin-top:16px">
                        <h4 style="font-size:14px;font-weight:600;color:#fff;margin:0 0 12px">Unggah Bukti Transfer</h4>

                        <form method="POST" action="{{ route('register.payment.manual', ['slug' => $tenant->slug]) }}" enctype="multipart/form-data">
                            @csrf
                            <div style="margin-bottom:12px">
                                <label style="display:block;font-size:12px;color:#9CA3AF;margin-bottom:4px">Bank Pengirim <span style="color:#EF4444">*</span></label>
                                <input type="text" name="sender_bank" required placeholder="Contoh: BCA / Mandiri / BRI" value="{{ old('sender_bank', $settings['sender_bank'] ?? '') }}" style="width:100%;padding:10px 14px;background:#1F2937;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;font-size:13px">
                            </div>

                            <div style="margin-bottom:12px">
                                <label style="display:block;font-size:12px;color:#9CA3AF;margin-bottom:4px">Nama Pemilik Rekening Pengirim <span style="color:#EF4444">*</span></label>
                                <input type="text" name="sender_account_name" required placeholder="Nama sesuai rekening Anda" value="{{ old('sender_account_name', $settings['sender_account_name'] ?? '') }}" style="width:100%;padding:10px 14px;background:#1F2937;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;font-size:13px">
                            </div>

                            <div style="margin-bottom:14px">
                                <label style="display:block;font-size:12px;color:#9CA3AF;margin-bottom:4px">Foto / Tangkapan Layar Bukti Transfer <span style="color:#EF4444">*</span></label>
                                <input type="file" name="proof_image" accept="image/jpeg,image/png,image/webp" required style="width:100%;padding:8px 12px;background:#1F2937;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;font-size:12.5px">
                                <span style="display:block;font-size:11px;color:#6B7280;margin-top:4px">Format JPG/PNG maks. 5MB</span>
                            </div>

                            <button type="submit" style="width:100%;padding:12px;background:#2563EB;border:none;border-radius:8px;color:#fff;font-weight:600;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Kirim Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                </div>

                {{-- TAB 2: MIDTRANS ONLINE --}}
                @if($isMidtransAvailable && $snapToken)
                <div x-show="tab === 'online'">
                    <p style="font-size:13px;color:#9CA3AF;line-height:1.6;margin-bottom:16px">
                        Bayar otomatis melalui gateway Midtrans. Mendukung QRIS (GoPay, OVO, Dana, ShopeePay), BCA Virtual Account, Mandiri, BRI, dan BNI. Akun langsung aktif otomatis setelah berhasil!
                    </p>

                    <button type="button" id="pay-button" style="width:100%;padding:14px;background:#10B981;border:none;border-radius:8px;color:#fff;font-weight:700;font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        Bayar Sekarang Rp {{ number_format($amount, 0, ',', '.') }}
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($isMidtransAvailable && $snapToken)
<script src="{{ $snapScriptUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
<script>
document.getElementById('pay-button')?.addEventListener('click', function () {
    window.snap.pay('{{ $snapToken }}', {
        onSuccess: function(result) {
            window.location.reload();
        },
        onPending: function(result) {
            window.location.reload();
        },
        onError: function(result) {
            alert('Pembayaran gagal atau dibatalkan.');
        }
    });
});
</script>
@endif
@endsection
