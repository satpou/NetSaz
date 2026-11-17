@extends('layouts.app')

@section('title', 'Edit Mitra')

@section('content')
<x-page-header title="Edit Mitra" :subtitle="$partner->name" />

<div class="panel" style="max-width:640px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('partners.update', ['tenant_slug' => request()->route('tenant_slug'), 'partner' => $partner->id]) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom:18px">
                <label class="form-label">Nama Mitra <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" value="{{ old('name', $partner->name) }}" required class="form-input">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
                <div>
                    <label class="form-label">Nama Kontak (PIC)</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $partner->contact_person) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">No. Telepon / WhatsApp</label>
                    <input type="text" name="phone" value="{{ old('phone', $partner->phone) }}" class="form-input">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $partner->email) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Komisi (%)</label>
                    <input type="number" name="commission_rate" value="{{ old('commission_rate', $partner->commission_rate) }}" min="0" max="100" step="0.01" class="form-input">
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label">Status <span style="color:var(--red)">*</span></label>
                <select name="status" required class="form-input">
                    <option value="active" {{ old('status', $partner->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $partner->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div style="margin-bottom:24px">
                <label class="form-label">Catatan</label>
                <textarea name="notes" rows="3" class="form-input">{{ old('notes', $partner->notes) }}</textarea>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Update Mitra</button>
                <a href="{{ route('partners.index', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
