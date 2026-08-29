@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
<div x-data="{ showDelete: false, deleteAction: '' }">
<x-page-header title="Pelanggan" subtitle="{{ $customers->total() }} pelanggan terdaftar">
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Pelanggan
    </a>
</x-page-header>

<div class="panel" style="padding:24px;margin-bottom:24px">
    <form method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px">
        <div style="flex:1;min-width:220px">
            <label class="form-label">Cari Pelanggan</label>
            <input type="text" name="search" placeholder="Nama, email, atau telepon..." value="{{ request('search') }}" class="form-input" autocomplete="off">
        </div>
        <div style="flex-shrink:0">
            <label class="form-label">Area</label>
            <select name="area" class="form-input" style="min-width:150px">
                <option value="">Semua Area</option>
                @foreach($areas as $a)
                    <option value="{{ $a }}" {{ request('area') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex-shrink:0">
            <label class="form-label">Status</label>
            <select name="status" class="form-input" style="min-width:150px">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="isolated" {{ request('status') == 'isolated' ? 'selected' : '' }}>Isolir</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspend</option>
            </select>
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-outline">Filter</button>
            @if(request()->hasAny(['search', 'status', 'area']))
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="panel" style="overflow:hidden;padding:0">
    <table class="data-table">
        <thead>
            <tr>
                <th style="padding:18px 20px 12px 28px">Pelanggan</th>
                <th style="padding:18px 20px 12px">Kontak</th>
                <th style="padding:18px 20px 12px">Area</th>
                <th style="padding:18px 20px 12px">Paket</th>
                <th style="padding:18px 20px 12px">Status</th>
                <th style="padding:18px 28px 12px;text-align:right">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr>
                <td style="padding:18px 20px 18px 28px">
                    <div style="display:flex;align-items:center;gap:14px">
                        <div style="width:40px;height:40px;border-radius:10px;background:var(--primary-tint);color:var(--primary-dark);display:flex;align-items:center;justify-content:center;font-family:'JetBrains Mono';font-weight:700;font-size:14px;flex-shrink:0">{{ substr($customer->name, 0, 1) }}</div>
                        <div>
                            <div style="font-weight:600;color:var(--ink);font-size:14.5px">{{ $customer->name }}</div>
                            <div style="font-size:12.5px;color:var(--ink-soft);margin-top:4px">Bergabung {{ $customer->join_date?->format('d M Y') ?? '-' }}</div>
                        </div>
                    </div>
                </td>
                <td style="padding:18px 20px">
                    <div style="font-weight:500;color:var(--ink);font-size:14px">{{ $customer->email }}</div>
                    <div style="font-size:12.5px;color:var(--ink-soft);margin-top:4px">{{ $customer->phone }}</div>
                </td>
                <td style="padding:18px 20px">
                    @if($customer->area)
                        <span class="badge badge-ghost" style="background:var(--primary-tint);color:var(--primary-dark)">{{ $customer->area }}</span>
                    @else
                        <span style="font-size:13px;color:var(--ink-faint)">-</span>
                    @endif
                </td>
                <td style="padding:18px 20px">
                    <div style="font-weight:600;color:var(--ink);font-size:14.5px">{{ $customer->package?->name ?? '-' }}</div>
                    <div style="font-size:12.5px;color:var(--ink-soft);margin-top:4px">{{ $customer->package ? 'Rp' . number_format($customer->package->price, 0, ',', '.') . '/bln' : '-' }}</div>
                </td>
                <td style="padding:18px 20px">
                    @if($customer->status == 'active')
                        <span class="badge badge-paid">Aktif</span>
                    @elseif($customer->status == 'isolated')
                        <span class="badge badge-late">Isolir</span>
                    @else
                        <span class="badge" style="background:var(--bg);color:var(--ink-soft);border:1px solid var(--line)">Suspend</span>
                    @endif
                </td>
                <td style="text-align:right;padding:18px 28px">
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:2px">
                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-ghost" style="padding:8px" title="Lihat">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-ghost" style="padding:8px" title="Edit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <button type="button" @click="deleteAction = '{{ route('customers.destroy', $customer->id) }}'; showDelete = true" class="btn btn-ghost" style="padding:8px;color:var(--red)" title="Hapus">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:60px 20px;text-align:center">
                    <x-empty-state title="Belum ada pelanggan" description="Belum ada data pelanggan. Tambah pelanggan baru untuk memulai.">
                        <x-slot:icon>
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-faint)"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </x-slot:icon>
                        <a href="{{ route('customers.create') }}" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                            Tambah Pelanggan Pertama
                        </a>
                    </x-empty-state>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($customers->hasPages())
<div style="margin-top:24px;display:flex;justify-content:center">
    {{ $customers->links() }}
</div>
@endif

<div x-show="showDelete" x-cloak class="modal-overlay" @click.self="showDelete = false" style="display:none">
    <div class="modal" @click.stop>
        <div class="modal-header">
            <h3 style="font-family:'Space Grotesk';font-size:18px;font-weight:700;color:var(--ink)">Hapus Pelanggan</h3>
            <p style="font-size:13px;color:var(--ink-faint);margin-top:4px">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--ink-soft);margin-bottom:20px">Yakin ingin menghapus pelanggan ini? Semua data terkait (tagihan, pembayaran) akan ikut terhapus.</p>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" @click="showDelete = false" class="btn-outline" style="padding:10px 20px">Batal</button>
                <form :action="deleteAction" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-save" style="width:auto;padding:10px 20px;margin-top:0;background:var(--red)">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
