@extends('layouts.portal')

@section('title', 'Profil')

@section('content')
<div style="margin-bottom:24px">
    <h1 style="font-size:24px;font-weight:700;color:var(--ink);margin-bottom:4px">Halo, {{ $customer->name }}</h1>
    <p style="font-size:14px;color:var(--ink-soft)">Selamat datang di portal pelanggan {{ $customer->tenant->name ?? '' }}</p>
</div>

@if($subscribePackage)
<div style="background:var(--primary-tint);border:1px solid var(--primary);border-radius:14px;padding:20px 24px;margin-bottom:24px;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
    <div style="flex:1;min-width:200px">
        <div style="font-size:14px;font-weight:600;color:var(--ink);margin-bottom:4px">Anda ingin berlangganan paket ini:</div>
        <div style="font-size:18px;font-weight:700;color:var(--primary)">{{ $subscribePackage->name }} — {{ $subscribePackage->speed }} Mbps</div>
        <div style="font-size:14px;color:var(--ink-soft);margin-top:2px">Rp{{ number_format($subscribePackage->price, 0, ',', '.') }}/bulan</div>
    </div>
    <div style="font-size:13px;color:var(--ink-soft);line-height:1.5">
        Untuk berlangganan, silakan hubungi admin {{ $customer->tenant->name }} melalui WhatsApp atau datang langsung ke kantor. Paket akan diaktifkan setelah pembayaran dikonfirmasi.
    </div>
</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:24px">
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px">
        <div style="font-size:13px;color:var(--ink-soft);font-weight:500;margin-bottom:8px">Paket</div>
        <div style="font-size:18px;font-weight:700;color:var(--ink)">{{ $customer->package->name ?? '-' }}</div>
        <div style="font-size:13px;color:var(--ink-faint);margin-top:4px">{{ $customer->package->speed ?? '' }}</div>
    </div>
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px">
        <div style="font-size:13px;color:var(--ink-soft);font-weight:500;margin-bottom:8px">Status</div>
        @php $s = $customer->status; @endphp
        <div style="font-size:18px;font-weight:700;color:{{ $s === 'active' ? 'var(--green)' : ($s === 'isolated' ? 'var(--amber)' : 'var(--red)') }}">{{ ucfirst($s) }}</div>
        <div style="font-size:13px;color:var(--ink-faint);margin-top:4px">Bergabung {{ $customer->join_date ? $customer->join_date->format('d M Y') : '-' }}</div>
    </div>
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:20px">
        <div style="font-size:13px;color:var(--ink-soft);font-weight:500;margin-bottom:8px">Tagihan Aktif</div>
        <div style="font-size:18px;font-weight:700;color:var(--ink)">{{ $activeInvoiceCount }} Invoice</div>
        <div style="font-size:13px;color:var(--ink-faint);margin-top:4px">Total: Rp{{ number_format($totalDue, 0, ',', '.') }}</div>
    </div>
</div>

<div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
    <h2 style="font-size:16px;font-weight:600;color:var(--ink);margin-bottom:16px">Data Diri</h2>
    <form method="POST" action="{{ route('customer.portal.profile.update') }}">
        @csrf
        @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
                <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">Nama</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="form-input" style="width:100%" required>
                @error('name')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">Email</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="form-input" style="width:100%" required>
                @error('email')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">No HP</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-input" style="width:100%" required>
                @error('phone')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
            </div>
            <div>
                <label style="display:block;font-size:13px;font-weight:500;color:var(--ink-soft);margin-bottom:6px">Alamat</label>
                <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="form-input" style="width:100%" required>
                @error('address')<div style="font-size:12px;color:var(--red);margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:20px">Simpan</button>
    </form>
</div>
@endSection
