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

    <div class="panel" style="padding:24px;margin-bottom:20px">
        <div class="panel-head">
            <h3 style="font-size:16px;font-weight:600">Akun PPPoE</h3>
            @if($customer->mikrotikAccount)
                @if($customer->mikrotikAccount->status === 'active')
                    <span class="badge badge-paid">Aktif</span>
                @else
                    <span class="badge badge-late">Nonaktif</span>
                @endif
            @endif
        </div>

        @if($customer->mikrotikAccount)
            @php($account = $customer->mikrotikAccount)
            <div style="margin-top:8px">
                <div class="summary-row">
                    <span class="summary-label">Username</span>
                    <span class="summary-value mono">{{ $account->pppoe_secret }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Service</span>
                    <span class="summary-value">{{ $account->pppoe_service }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Router</span>
                    <span class="summary-value">{{ $account->nasServer?->name ?? '-' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Diproses di Router</span>
                    <span class="summary-value">{{ $account->provisioned_at?->format('d M Y H:i') ?? 'Belum' }}</span>
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px">
                @if($account->status === 'active')
                    <form action="{{ route('customers.pppoe.disable', $customer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline">Nonaktifkan di Router</button>
                    </form>
                @else
                    <form action="{{ route('customers.pppoe.enable', $customer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary">Aktifkan di Router</button>
                    </form>
                @endif

                <form action="{{ route('customers.pppoe.reset-password', $customer->id) }}" method="POST" style="display:flex;gap:8px;flex-wrap:wrap">
                    @csrf
                    <input type="password" name="pppoe_password" placeholder="Password baru" class="form-input" style="width:180px" required>
                    <button type="submit" class="btn btn-outline">Reset Password</button>
                </form>

                <form action="{{ route('customers.pppoe.delete', $customer->id) }}" method="POST" onsubmit="return confirm('Hapus akun PPPoE pelanggan ini?')">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="color:var(--red)">Hapus Akun</button>
                </form>
            </div>
        @else
            <p style="font-size:13.5px;color:var(--ink-faint);margin-top:8px">Belum ada akun PPPoE. Isi username &amp; password PPPoE saat edit pelanggan untuk memprovision akun ke router.</p>
        @endif
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
                    <span class="summary-value mono">{{ $customer->latitude }}, {{ $customer->longitude }}</span>
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

    {{-- DOKUMEN & FOTO RUMAH PELANGGAN --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-top:20px">
        <div class="panel" style="padding:24px">
            <div class="panel-head" style="margin-bottom:14px">
                <h3 style="font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Foto Rumah Pelanggan
                </h3>
            </div>
            @if($customer->house_image)
                <div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--line)">
                    <img src="{{ asset('storage/' . $customer->house_image) }}" alt="Foto Rumah {{ $customer->name }}" style="width:100%;height:220px;object-fit:cover;display:block">
                    <div style="padding:10px 14px;background:var(--bg);border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12px;color:var(--ink-soft)">Tampak Depan Rumah</span>
                        <a href="{{ asset('storage/' . $customer->house_image) }}" target="_blank" rel="noopener" class="btn btn-outline" style="padding:4px 10px;font-size:12px">
                            Lihat Penuh ↗
                        </a>
                    </div>
                </div>
            @else
                <div style="padding:36px 16px;text-align:center;color:var(--ink-faint);background:var(--bg);border-radius:10px;border:1px dashed var(--line);font-size:13px">
                    Foto rumah belum diunggah. <a href="{{ route('customers.edit', $customer->id) }}" style="color:var(--primary);font-weight:600">Unggah foto sekarang</a>
                </div>
            @endif
        </div>

        <div class="panel" style="padding:24px">
            <div class="panel-head" style="margin-bottom:14px">
                <h3 style="font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    Scan / Foto KTP
                </h3>
            </div>
            @if($customer->ktp_image)
                <div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--line)">
                    <img src="{{ asset('storage/' . $customer->ktp_image) }}" alt="KTP {{ $customer->name }}" style="width:100%;height:220px;object-fit:cover;display:block">
                    <div style="padding:10px 14px;background:var(--bg);border-top:1px solid var(--line);display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:12px;color:var(--ink-soft)">No: {{ $customer->ktp_id ?: 'Tercatat' }}</span>
                        <a href="{{ asset('storage/' . $customer->ktp_image) }}" target="_blank" rel="noopener" class="btn btn-outline" style="padding:4px 10px;font-size:12px">
                            Lihat Penuh ↗
                        </a>
                    </div>
                </div>
            @else
                <div style="padding:36px 16px;text-align:center;color:var(--ink-faint);background:var(--bg);border-radius:10px;border:1px dashed var(--line);font-size:13px">
                    Scan KTP belum diunggah. <a href="{{ route('customers.edit', $customer->id) }}" style="color:var(--primary);font-weight:600">Unggah KTP</a>
                </div>
            @endif
        </div>
    </div>

    {{-- PETA LOKASI RUMAH PELANGGAN --}}
    @if($customer->latitude && $customer->longitude)
    <div class="panel" style="padding:24px;margin-top:20px">
        <div class="panel-head" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px">
            <div>
                <h3 style="font-size:16px;font-weight:600;display:flex;align-items:center;gap:8px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Peta Lokasi Pemasangan Pelanggan
                </h3>
                <p style="font-size:13px;color:var(--ink-soft);margin:2px 0 0">Koordinat: <span class="mono">{{ $customer->latitude }}, {{ $customer->longitude }}</span></p>
            </div>
            <div style="display:flex;gap:8px">
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank" rel="noopener" class="btn btn-primary" style="padding:7px 14px;font-size:12.5px;display:inline-flex;align-items:center;gap:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                    Petunjuk Arah (Navigasi GPS)
                </a>
                <a href="https://www.google.com/maps/search/?api=1&query={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank" rel="noopener" class="btn btn-outline" style="padding:7px 14px;font-size:12.5px">
                    Buka Google Maps ↗
                </a>
            </div>
        </div>
        <div style="height:280px;border-radius:10px;overflow:hidden;border:1px solid var(--line)" id="customer-map"></div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var lat = {{ $customer->latitude }};
            var lng = {{ $customer->longitude }};
            var map = L.map('customer-map').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([lat, lng]).addTo(map)
                .bindPopup('<b>{{ $customer->name }}</b><br>{{ $customer->address }}')
                .openPopup();
        });
    </script>
    @endpush
    @endif
    </div>
</div>
@endsection
