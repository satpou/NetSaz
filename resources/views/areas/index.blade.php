@extends('layouts.app')

@section('title', 'Area')

@section('content')
<div x-data="{ showDelete: false, deleteAction: '' }">
<x-page-header title="Area" subtitle="{{ $areas->count() }} area terdaftar">
    <a href="{{ route('areas.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Area
    </a>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
    @forelse($areas as $area)
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
            <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-tint);display:flex;align-items:center;justify-content:center;color:var(--primary-dark)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
        </div>

        <h3 style="font-size:18px;font-weight:600;color:var(--ink);margin-bottom:6px">{{ $area->name }}</h3>
        @if($area->description)
        <p style="font-size:14px;color:var(--ink-soft);margin-bottom:16px;line-height:1.5">{{ $area->description }}</p>
        @else
        <p style="font-size:14px;color:var(--ink-faint);margin-bottom:16px">Tidak ada deskripsi</p>
        @endif

        <div style="display:flex;gap:10px;padding-top:18px;border-top:1px solid var(--line)">
            <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-ghost" style="flex:1;justify-content:center;color:var(--primary)">Edit</a>
            <button type="button" @click="deleteAction = '{{ route('areas.destroy', $area->id) }}'; showDelete = true" class="btn btn-ghost" style="flex:1;justify-content:center;color:var(--red)">Hapus</button>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;display:flex;justify-content:center;padding:60px 20px">
        <x-empty-state title="Belum ada area" description="Buat area untuk mengelompokkan pelanggan berdasarkan lokasi.">
            <x-slot:icon>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-faint)"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </x-slot:icon>
            <a href="{{ route('areas.create') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                Buat Area Pertama
            </a>
        </x-empty-state>
    </div>
    @endforelse
</div>

<div x-show="showDelete" x-cloak class="modal-overlay" @click.self="showDelete = false" style="display:none">
    <div class="modal" @click.stop>
        <div class="modal-header">
            <h3 style="font-family:'Space Grotesk';font-size:18px;font-weight:700;color:var(--ink)">Hapus Area</h3>
            <p style="font-size:13px;color:var(--ink-faint);margin-top:4px">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--ink-soft);margin-bottom:20px">Yakin ingin menghapus area ini?</p>
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
