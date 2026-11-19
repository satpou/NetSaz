@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran Tenant')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <span>Super Admin</span> &gt;
    <b>Verifikasi Pembayaran</b>
</div>

<x-page-header title="Verifikasi Pembayaran Tenant" subtitle="Kelola dan konfirmasi aktivasi akun ISP baru setelah melakukan pembayaran langganan tahunan">
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Pengaturan Rekening & Midtrans
    </a>
</x-page-header>

@if(session('success'))
<div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

@if(session('warning'))
<div style="background:rgba(245, 158, 11, 0.15);border:1px solid #F59E0B;color:#FCD34D;padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>{{ session('warning') }}</span>
</div>
@endif

<!-- STATUS TABS -->
<div style="display:flex;gap:12px;margin-bottom:20px;border-bottom:1px solid var(--line);padding-bottom:12px">
    <a href="{{ route('admin.verifications.index', ['status' => 'pending_payment']) }}"
       class="btn {{ $status === 'pending_payment' ? 'btn-primary' : 'btn-outline' }}"
       style="font-size:13.5px;padding:8px 18px;position:relative">
        Menunggu Verifikasi
        @if($counts['pending'] > 0)
        <span style="background:#EF4444;color:#fff;padding:2px 7px;border-radius:10px;font-size:11px;font-weight:700;margin-left:6px">{{ $counts['pending'] }}</span>
        @endif
    </a>
    <a href="{{ route('admin.verifications.index', ['status' => 'active']) }}"
       class="btn {{ $status === 'active' ? 'btn-primary' : 'btn-outline' }}"
       style="font-size:13.5px;padding:8px 18px">
        Sudah Aktif ({{ $counts['active'] }})
    </a>
    <a href="{{ route('admin.verifications.index', ['status' => 'rejected']) }}"
       class="btn {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline' }}"
       style="font-size:13.5px;padding:8px 18px">
        Ditolak ({{ $counts['rejected'] }})
    </a>
</div>

<!-- SEARCH -->
<div class="panel" style="padding:16px 20px;margin-bottom:20px">
    <form method="GET" action="{{ route('admin.verifications.index') }}" style="display:flex;gap:10px">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="text" name="search" placeholder="Cari nama ISP, subdomain, email, no WhatsApp..." value="{{ request('search') }}"
               class="form-input" style="flex:1;font-size:13.5px;padding:8px 14px">
        <button type="submit" class="btn btn-outline" style="padding:8px 16px;font-size:13.5px">Cari</button>
        @if(request('search'))
        <a href="{{ route('admin.verifications.index', ['status' => $status]) }}" class="btn btn-outline" style="padding:8px 16px;font-size:13.5px">Reset</a>
        @endif
    </form>
</div>

<!-- LIST TENANTS -->
<div class="panel" style="padding:0;overflow:hidden">
    @if($tenants->isEmpty())
    <div style="text-align:center;padding:50px 20px;color:var(--ink-soft)">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;display:block;opacity:0.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        <div style="font-size:15px;font-weight:600;color:var(--ink)">Tidak ada data pendaftaran</div>
        <div style="font-size:13px;margin-top:4px">Belum ada tenant pada status ini.</div>
    </div>
    @else
    <div class="table-responsive">
        <table class="table" style="width:100%;margin:0;font-size:13.5px">
            <thead>
                <tr style="background:var(--bg-alt);border-bottom:1px solid var(--line)">
                    <th style="padding:14px 18px">ISP & Subdomain</th>
                    <th style="padding:14px 18px">PIC & Kontak</th>
                    <th style="padding:14px 18px">Paket & Tagihan</th>
                    <th style="padding:14px 18px">Bukti Pembayaran</th>
                    <th style="padding:14px 18px">Status</th>
                    <th style="padding:14px 18px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tenants as $t)
                @php
                    $s = $t->settings ?? [];
                    $proof = $s['proof_image_path'] ?? null;
                    $pkgName = $s['selected_package'] ?? 'launch';
                    $amount = $s['payment_amount'] ?? 399000;
                    $method = $s['payment_method'] ?? 'pending';
                    $payStatus = $s['payment_status'] ?? 'pending';
                @endphp
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:14px 18px">
                        <div style="font-weight:700;color:var(--ink);font-size:14.5px">{{ $t->name }}</div>
                        <div style="font-family:'JetBrains Mono',monospace;font-size:12px;color:var(--primary);margin-top:2px">
                            {{ $t->slug }}.netsaz.web.id
                        </div>
                        <div style="font-size:11.5px;color:var(--ink-faint);margin-top:4px">
                            Daftar: {{ $t->created_at->format('d M Y, H:i') }}
                        </div>
                    </td>
                    <td style="padding:14px 18px">
                        <div style="font-weight:600;color:var(--ink)">{{ $s['pic_name'] ?? '-' }}</div>
                        <div style="font-size:12.5px;color:var(--ink-soft)">{{ $t->email }}</div>
                        @if($t->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $t->whatsapp_number) }}" target="_blank" rel="noopener" style="font-size:12px;color:#10B981;display:inline-flex;align-items:center;gap:4px;margin-top:4px;text-decoration:none">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
                            <span>{{ $t->whatsapp_number }}</span>
                        </a>
                        @endif
                    </td>
                    <td style="padding:14px 18px">
                        @if($pkgName === 'enterprise')
                        <div style="font-weight:700;color:#C084FC;display:inline-flex;align-items:center;gap:6px">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Enterprise</span>
                            <span style="font-size:10px;background:rgba(168,85,247,0.18);color:#C084FC;border:1px solid #A855F7;padding:1px 6px;border-radius:10px">KUSTOM</span>
                        </div>
                        <div style="font-size:13px;font-weight:700;color:var(--ink-soft);margin-top:2px">
                            Sesuai Penawaran / B2B
                        </div>
                        <div style="font-size:11.5px;color:var(--ink-faint);margin-top:2px">
                            Status: <b style="color:#C084FC">Konsultasi Sales</b>
                        </div>
                        @else
                        <div style="font-weight:700;color:var(--ink);text-transform:capitalize">{{ $pkgName }} (Tahunan)</div>
                        <div style="font-size:14px;font-weight:700;color:var(--primary);margin-top:2px">
                            Rp {{ number_format($amount, 0, ',', '.') }}
                        </div>
                        <div style="font-size:11.5px;color:var(--ink-faint);margin-top:2px">
                            Metode: <b style="text-transform:capitalize">{{ $method }}</b>
                        </div>
                        @endif
                    </td>
                    <td style="padding:14px 18px">
                        @if($pkgName === 'enterprise')
                        <span style="font-size:12px;color:#C084FC;background:rgba(168,85,247,0.1);border:1px solid rgba(168,85,247,0.3);padding:4px 8px;border-radius:6px;display:inline-flex;align-items:center;gap:6px">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>Permintaan Enterprise</span>
                        </span>
                        @elseif($proof)
                        <div style="display:flex;align-items:center;gap:10px">
                            <a href="{{ asset('storage/' . $proof) }}" target="_blank" rel="noopener">
                                <img src="{{ asset('storage/' . $proof) }}" alt="Bukti Transfer" style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid var(--line)">
                            </a>
                            <div>
                                <div style="font-size:12px;font-weight:600;color:var(--ink)">{{ $s['sender_bank'] ?? 'Bank' }}</div>
                                <div style="font-size:11.5px;color:var(--ink-soft)">a/n {{ $s['sender_account_name'] ?? '-' }}</div>
                                <a href="{{ asset('storage/' . $proof) }}" target="_blank" rel="noopener" style="font-size:11.5px;color:var(--primary);text-decoration:none">Lihat Penuh ↗</a>
                            </div>
                        </div>
                        @else
                        <span style="font-size:12px;color:var(--ink-faint)">Belum ada bukti</span>
                        @endif
                    </td>
                    <td style="padding:14px 18px">
                        @if($t->status === 'active')
                        <span style="background:rgba(16, 185, 129, 0.15);color:#10B981;border:1px solid #10B981;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Aktif</span>
                        </span>
                        @elseif($t->status === 'rejected')
                        <span style="background:rgba(239, 68, 68, 0.15);color:#EF4444;border:1px solid #EF4444;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            <span>Ditolak</span>
                        </span>
                        @if(!empty($s['rejection_reason']))
                        <div style="font-size:11px;color:#EF4444;margin-top:4px;max-width:160px">
                            Alasan: {{ $s['rejection_reason'] }}
                        </div>
                        @endif
                        @else
                        <span style="background:rgba(245, 158, 11, 0.15);color:#F59E0B;border:1px solid #F59E0B;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:4px">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Menunggu</span>
                        </span>
                        @endif
                    </td>
                    <td style="padding:14px 18px;text-align:right">
                        @if($t->status === 'pending_payment' || $t->status === 'rejected')
                        <div style="display:inline-flex;gap:8px">
                            {{-- ACCEPT BUTTON --}}
                            <form method="POST" action="{{ route('admin.verifications.accept', $t->id) }}"
                                  data-confirm="Apakah Anda yakin ingin MENERIMA dan MENGAKTIFKAN tenant '{{ $t->name }}' selama 1 tahun?"
                                  data-confirm-title="Konfirmasi Aktivasi Tenant"
                                  data-confirm-type="success"
                                  data-confirm-text="Ya, Terima & Aktifkan">
                                @csrf
                                <button type="submit" class="btn" style="background:#10B981;color:#fff;border:none;padding:7px 14px;border-radius:6px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                                    Terima &amp; Aktifkan
                                </button>
                            </form>

                            @if($t->status !== 'rejected')
                            {{-- REJECT BUTTON --}}
                            <button type="button" onclick="NetSazModal.prompt({ title: 'Tolak Pembayaran Tenant', message: 'Masukkan alasan penolakan untuk tenant \'{{ $t->name }}\':', placeholder: 'Contoh: Bukti transfer tidak valid / nominal tidak cocok', type: 'danger', confirmText: 'Tolak Tenant' }).then(r => { if(r !== null && r.trim() !== '') { document.getElementById('reject-reason-{{ $t->id }}').value = r; document.getElementById('reject-form-{{ $t->id }}').submit(); } })" class="btn" style="background:rgba(239,68,68,0.1);color:#EF4444;border:1px solid #EF4444;padding:7px 12px;border-radius:6px;font-size:12.5px;font-weight:600">
                                Tolak
                            </button>
                            <form id="reject-form-{{ $t->id }}" method="POST" action="{{ route('admin.verifications.reject', $t->id) }}" style="display:none">
                                @csrf
                                <input type="hidden" name="reason" id="reject-reason-{{ $t->id }}">
                            </form>
                            @endif
                        </div>
                        @else
                        <span style="font-size:12px;color:var(--ink-faint)">Terverifikasi</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--line)">
        {{ $tenants->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
