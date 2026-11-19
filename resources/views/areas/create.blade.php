@extends('layouts.app')

@section('title', 'Tambah Area')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">Tambah Area</h1>
        <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">Buat area baru untuk mengelompokkan pelanggan</p>
    </div>
</div>

<div class="panel" style="max-width:560px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('areas.store') }}" method="POST">
            @csrf

            <div style="margin-bottom:20px">
                <label class="form-label">Nama Area <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Cth: Perumahan Indah, Griya Asri">
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="3" class="form-input" placeholder="Keterangan area (opsional)">{{ old('description') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
                <div>
                    <label class="form-label">Latitude Pusat Area <span style="color:var(--ink-faint);font-weight:400">(opsional)</span></label>
                    <input type="text" name="latitude" value="{{ old('latitude') }}" class="form-input mono" placeholder="Cth: -7.7956000">
                </div>
                <div>
                    <label class="form-label">Longitude Pusat Area <span style="color:var(--ink-faint);font-weight:400">(opsional)</span></label>
                    <input type="text" name="longitude" value="{{ old('longitude') }}" class="form-input mono" placeholder="Cth: 110.3695000">
                </div>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Simpan Area</button>
                <a href="{{ route('areas.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
