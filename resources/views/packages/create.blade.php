@extends('layouts.app')

@section('title', 'Tambah Paket')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">Tambah Paket</h1>
        <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">Buat paket langganan baru untuk pelanggan</p>
    </div>
</div>

<div class="panel" style="max-width:560px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('packages.store') }}" method="POST">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div>
                    <label class="form-label">Nama Paket <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Internet 10 Mbps">
                </div>
                <div>
                    <label class="form-label">Kecepatan <span style="color:var(--red)">*</span></label>
                    <input type="text" name="speed" value="{{ old('speed') }}" required class="form-input" placeholder="10 Mbps">
                </div>
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="3" class="form-input" placeholder="Deskripsi paket">{{ old('description') }}</textarea>
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Harga per Bulan (Rp) <span style="color:var(--red)">*</span></label>
                <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required class="form-input" placeholder="150000">
            </div>

            <div style="margin-bottom:24px">
                <label style="display:flex;align-items:center;gap:10px;font-size:14px;color:var(--ink-soft);cursor:pointer">
                    <input type="checkbox" name="is_taxable" value="1" {{ old('is_taxable') ? 'checked' : '' }}
                           style="width:18px;height:18px;border-radius:4px;border:1px solid var(--line);accent-color:var(--primary)">
                    <span>Harga sudah termasuk pajak</span>
                </label>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Simpan Paket</button>
                <a href="{{ route('packages.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection