@extends('layouts.app')

@section('title', 'Tambah Paket Langganan')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <a href="{{ route('admin.plans.index') }}" style="color:inherit;text-decoration:none">Paket Langganan</a> &gt;
    <b>Tambah Baru</b>
</div>

<x-page-header title="Tambah Paket Langganan" subtitle="Buat paket harga baru untuk ditawarkan kepada ISP">
</x-page-header>

<div class="panel" style="max-width:760px">
    <form method="POST" action="{{ route('admin.plans.store') }}">
        @csrf

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Nama Paket <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Pro, Starter, Growth" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('name')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Kode Paket (Slug) <span style="color:var(--red)">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" placeholder="Contoh: pro, starter, growth" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('code')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Harga Langganan (Rp) <span style="color:var(--red)">*</span></label>
                <input type="number" name="price" value="{{ old('price', 0) }}" min="0" step="1000" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('price')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Periode Tagihan <span style="color:var(--red)">*</span></label>
                <select name="billing_period" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                    <option value="monthly" {{ old('billing_period') == 'monthly' ? 'selected' : '' }}>Bulanan (Monthly)</option>
                    <option value="yearly" {{ old('billing_period') == 'yearly' ? 'selected' : '' }}>Tahunan (Yearly)</option>
                </select>
                @error('billing_period')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Maks. Pelanggan</label>
                <input type="number" name="max_active_customers" value="{{ old('max_active_customers', 150) }}" min="0" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <span style="font-size:11.5px;color:var(--ink-faint)">Isi 0 untuk unlimited</span>
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Maks. Router</label>
                <input type="number" name="max_routers" value="{{ old('max_routers', 2) }}" min="0" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <span style="font-size:11.5px;color:var(--ink-faint)">Isi 0 untuk unlimited</span>
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Maks. Staff</label>
                <input type="number" name="max_staff" value="{{ old('max_staff', 3) }}" min="0" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <span style="font-size:11.5px;color:var(--ink-faint)">Isi 0 untuk unlimited</span>
            </div>
        </div>

        <!-- Features Checklist -->
        <div style="margin-bottom:20px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:8px;color:var(--ink)">Fitur yang Disertakan</label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;background:var(--bg-alt);padding:16px;border-radius:10px;border:1px solid var(--line)">
                @foreach($availableFeatures as $fKey => $fLabel)
                    <label style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--ink);cursor:pointer">
                        <input type="checkbox" name="features[]" value="{{ $fKey }}" {{ in_array($fKey, old('features', [])) ? 'checked' : '' }} style="accent-color:var(--primary);width:16px;height:16px">
                        <span>{{ $fLabel }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Urutan Tampilan (Sort Order)</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 1) }}" min="0" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            </div>

            <div style="display:flex;align-items:center;gap:10px;padding-top:26px">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }} style="accent-color:var(--primary);width:18px;height:18px">
                <label for="is_active" style="font-size:14px;font-weight:600;color:var(--ink);cursor:pointer">Aktifkan paket sekarang</label>
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="{{ route('admin.plans.index') }}" class="btn btn-outline" style="padding:10px 20px">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Simpan Paket</button>
        </div>
    </form>
</div>
@endsection
