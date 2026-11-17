@extends('layouts.app')

@section('title', 'Mitra')

@section('content')
<x-page-header title="Mitra" subtitle="{{ $partners->count() }} mitra terdaftar">
    <a href="{{ route('partners.create', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Mitra
    </a>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="panel">
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kontak</th>
                    <th>Telepon</th>
                    <th>Komisi</th>
                    <th>Status</th>
                    <th style="text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($partners as $partner)
                <tr>
                    <td>
                        <a href="{{ route('partners.show', ['tenant_slug' => request()->route('tenant_slug'), 'partner' => $partner->id]) }}" style="font-weight:600;color:var(--ink)">
                            {{ $partner->name }}
                        </a>
                    </td>
                    <td>{{ $partner->contact_person ?? '-' }}</td>
                    <td class="mono">{{ $partner->phone ?? '-' }}</td>
                    <td class="mono">{{ number_format((float) $partner->commission_rate, 2) }}%</td>
                    <td>
                        @if($partner->status === 'active')
                            <span class="badge badge-paid">Active</span>
                        @else
                            <span class="badge" style="background:var(--bg);color:var(--ink-soft);border:1px solid var(--line)">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        <div style="display:inline-flex;gap:8px">
                            <a href="{{ route('partners.show', ['tenant_slug' => request()->route('tenant_slug'), 'partner' => $partner->id]) }}" class="btn btn-ghost" style="padding:6px 12px">Detail</a>
                            <a href="{{ route('partners.edit', ['tenant_slug' => request()->route('tenant_slug'), 'partner' => $partner->id]) }}" class="btn btn-outline" style="padding:6px 12px">Edit</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--ink-faint);padding:36px 16px">Belum ada mitra.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
