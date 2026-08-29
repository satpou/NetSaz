@extends('layouts.app')

@section('title', 'Paket Internet')

@section('content')
<div x-data="{ showDelete: false, deleteAction: '' }">
<x-page-header title="Paket Internet" subtitle="{{ $packages->count() }} paket tersedia">
    <a href="{{ route('packages.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Paket
    </a>
</x-page-header>

<div class="panel" style="padding:24px;margin-bottom:24px">
    <form method="GET" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:16px">
        <div style="flex:1;min-width:220px;max-width:400px">
            <label class="form-label">Cari Paket</label>
            <input type="text" name="search" placeholder="Nama paket..." value="{{ request('search') }}" class="form-input" autocomplete="off">
        </div>
        <div style="display:flex;gap:10px">
            <button type="submit" class="btn btn-outline">Filter</button>
            @if(request()->has('search'))
                <a href="{{ route('packages.index') }}" class="btn btn-outline">Reset</a>
            @endif
        </div>
    </form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px">
    @forelse($packages as $package)
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
            <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-tint);display:flex;align-items:center;justify-content:center;color:var(--primary-dark)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            @if($package->is_taxable)
            <span style="font-size:11px;font-weight:600;background:var(--amber-tint);color:var(--amber);padding:4px 10px;border-radius:100px">Pajak</span>
            @endif
        </div>

        <h3 style="font-size:18px;font-weight:600;color:var(--ink);margin-bottom:6px">{{ $package->name }}</h3>
        <p style="font-size:14px;color:var(--ink-soft);margin-bottom:16px;line-height:1.5">{{ $package->description }}</p>

        <div style="display:flex;align-items:baseline;gap:4px;margin-bottom:6px">
            <span style="font-size:28px;font-weight:700;color:var(--ink)">Rp{{ number_format($package->price, 0, ',', '.') }}</span>
            <span style="font-size:14px;color:var(--ink-soft)">/bulan</span>
        </div>

        <div style="font-size:14px;font-weight:500;color:var(--ink-soft);margin-bottom:20px">{{ $package->speed }}</div>

        <div style="display:flex;gap:10px;padding-top:18px;border-top:1px solid var(--line)">
            <a href="{{ route('packages.show', $package->id) }}" class="btn btn-ghost" style="flex:1;justify-content:center">Detail</a>
            <a href="{{ route('packages.edit', $package->id) }}" class="btn btn-ghost" style="flex:1;justify-content:center;color:var(--primary)">Edit</a>
            <button type="button" @click="deleteAction = '{{ route('packages.destroy', $package->id) }}'; showDelete = true" class="btn btn-ghost" style="flex:1;justify-content:center;color:var(--red)">Hapus</button>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;display:flex;justify-content:center;padding:60px 20px">
        <x-empty-state title="Belum ada paket internet" description="Buat paket internet pertama untuk mulai menawarkan layanan ke pelanggan.">
            <x-slot:icon>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-faint)"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </x-slot:icon>
            <a href="{{ route('packages.create') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                Buat Paket Pertama
            </a>
        </x-empty-state>
    </div>
    @endforelse
</div>

<div x-show="showDelete" x-cloak class="modal-overlay" @click.self="showDelete = false" style="display:none">
    <div class="modal" @click.stop>
        <div class="modal-header">
            <h3 style="font-family:'Space Grotesk';font-size:18px;font-weight:700;color:var(--ink)">Hapus Paket</h3>
            <p style="font-size:13px;color:var(--ink-faint);margin-top:4px">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--ink-soft);margin-bottom:20px">Yakin ingin menghapus paket ini?</p>
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
