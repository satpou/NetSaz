@php
    $user = Auth::user();
    $host = request()->getHost();
    $tenantDomain = config('app.tenant_domain');
    $isTenantDomain = $host !== $tenantDomain && str_ends_with($host, '.'.$tenantDomain);
    $ts = $isTenantDomain ? str_replace('.'.$tenantDomain, '', $host) : ($user?->tenant?->slug ?? session('tenant_slug') ?? null);
    if ($ts) {
        \Illuminate\Support\Facades\URL::defaults(['tenant_slug' => $ts]);
    }
@endphp
@extends('layouts.app')

@section('title', 'Broadcast WhatsApp')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>WhatsApp</span> &gt; <b>Broadcast</b>
</div>

<x-page-header title="Kirim Pesan Siaran (Broadcast WhatsApp)" subtitle="Kirim pengumuman pemeliharaan jaringan, promo paket, atau pesan massal tertarget ke pelanggan">
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('warning'))
    <div style="background:var(--amber-tint);border:1px solid var(--amber);color:var(--amber);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('warning') }}</span>
    </div>
@endif

<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;padding:24px;max-width:750px">
    <form action="{{ route('whatsapp.broadcast.send', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengirimkan pesan siaran massal ini?');">
        @csrf

        <div class="form-field" style="margin-bottom:18px">
            <label style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px">Target Penerima Broadcast</label>
            <select name="target_filter" id="target_filter" class="form-input" onchange="toggleFilterFields(this.value)" required>
                <option value="all">Semua Pelanggan Terdaftar ({{ $totalCustomers }} Pelanggan)</option>
                <option value="area">Berdasarkan Area / Wilayah Tertentu</option>
                <option value="package">Berdasarkan Paket Langganan Tertentu</option>
                <option value="isolated">Khusus Pelanggan yang Terisolir</option>
            </select>
        </div>

        <div id="filter_area_group" class="form-field" style="display:none;margin-bottom:18px">
            <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Pilih Area Target</label>
            <select name="area_id" class="form-input">
                <option value="">-- Pilih Area --</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->customers_count ?? 0 }} Pelanggan)</option>
                @endforeach
            </select>
        </div>

        <div id="filter_package_group" class="form-field" style="display:none;margin-bottom:18px">
            <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Pilih Paket Target</label>
            <select name="package_id" class="form-input">
                <option value="">-- Pilih Paket --</option>
                @foreach($packages as $pkg)
                    <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-field" style="margin-bottom:20px">
            <label style="font-size:13px;font-weight:700;color:var(--ink);display:block;margin-bottom:6px">Isi Pesan Broadcast</label>
            <textarea name="message" rows="6" class="form-input" style="resize:vertical" placeholder="Tuliskan pesan pengumuman Anda di sini... Gunakan {nama} untuk menyebut nama pelanggan secara personal." required></textarea>
            <small style="font-size:11.5px;color:var(--ink-faint);display:block;margin-top:4px">
                Tag personalisasi yang didukung: <code class="mono" style="color:var(--primary)">{nama}</code>
            </small>
        </div>

        <button type="submit" class="btn btn-primary" style="padding:10px 24px">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            Kirim Broadcast Sekarang
        </button>
    </form>
</div>

<script>
function toggleFilterFields(val) {
    document.getElementById('filter_area_group').style.display = (val === 'area') ? 'block' : 'none';
    document.getElementById('filter_package_group').style.display = (val === 'package') ? 'block' : 'none';
}
</script>
@endsection
