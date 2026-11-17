@extends('layouts.app')

@section('title', 'Tambah Mitra')

@section('content')
<x-page-header title="Tambah Mitra" subtitle="Daftarkan mitra reseller atau agen baru" />

<div class="panel" style="max-width:640px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('partners.store', ['tenant_slug' => request()->route('tenant_slug')]) }}" method="POST">
            @csrf

            <div style="margin-bottom:18px">
                <label class="form-label">Nama Mitra <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Cth: Toko Elektronik Jaya">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
                <div>
                    <label class="form-label">Nama Kontak (PIC)</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-input" placeholder="Nama penanggung jawab">
                </div>
                <div>
                    <label class="form-label">No. Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="mitra@example.com">
                </div>
                <div>
                    <label class="form-label">Komisi (%)</label>
                    <input type="number" name="commission_rate" value="{{ old('commission_rate', 0) }}" min="0" max="100" step="0.01" class="form-input">
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label">Status <span style="color:var(--red)">*</span></label>
                <select name="status" required class="form-input">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div style="margin-bottom:24px">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="3" class="form-input" placeholder="Catatan wilayah atau kerja sama (opsional)">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Simpan Mitra</button>
                <a href="{{ route('partners.index', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
