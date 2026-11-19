@extends('layouts.app')

@section('title', 'Detail Mitra')

@section('content')
<x-page-header :title="$partner->name" subtitle="Detail informasi mitra">
    <div style="display:flex;gap:10px">
        <a href="{{ route('partners.edit', ['tenant_slug' => request()->route('tenant_slug'), 'partner' => $partner->id]) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('partners.destroy', ['tenant_slug' => request()->route('tenant_slug'), 'partner' => $partner->id]) }}" method="POST"
              data-confirm="Apakah Anda yakin ingin menghapus data mitra '{{ $partner->name }}'?"
              data-confirm-title="Hapus Data Mitra"
              data-confirm-type="danger"
              data-confirm-text="Ya, Hapus Mitra">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline" style="color:var(--red)">Hapus</button>
        </form>
    </div>
</x-page-header>

<div class="panel" style="max-width:640px;padding:24px">
    <div class="summary-row">
        <span class="summary-label">Nama Mitra</span>
        <span class="summary-value" style="font-family:'Inter';font-weight:600">{{ $partner->name }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Kontak (PIC)</span>
        <span class="summary-value" style="font-family:'Inter'">{{ $partner->contact_person ?? '-' }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Telepon</span>
        <span class="summary-value mono">{{ $partner->phone ?? '-' }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Email</span>
        <span class="summary-value" style="font-family:'Inter'">{{ $partner->email ?? '-' }}</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Komisi</span>
        <span class="summary-value mono">{{ number_format((float) $partner->commission_rate, 2) }}%</span>
    </div>
    <div class="summary-row">
        <span class="summary-label">Status</span>
        <span class="badge {{ $partner->status === 'active' ? 'badge-paid' : '' }}">{{ ucfirst($partner->status) }}</span>
    </div>
    <div class="summary-row" style="border-bottom:none">
        <span class="summary-label">Catatan</span>
        <span class="summary-value" style="font-family:'Inter';max-width:65%;text-align:right">{{ $partner->notes ?? '-' }}</span>
    </div>
</div>
@endsection
