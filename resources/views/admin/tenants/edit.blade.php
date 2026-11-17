@extends('layouts.app')

@section('title', 'Edit Tenant - ' . $tenant->name)

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <a href="{{ route('admin.tenants.index') }}" style="color:inherit;text-decoration:none">Kelola Tenant</a> &gt;
    <b>Edit {{ $tenant->name }}</b>
</div>

<x-page-header title="Edit Tenant ISP" subtitle="Perbarui identitas, subdomain, dan status ISP">
</x-page-header>

<div class="panel" style="max-width:680px">
    <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
        @csrf
        @method('PUT')

        <div style="margin-bottom:18px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Nama ISP <span style="color:var(--red)">*</span></label>
            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
            @error('name')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:18px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Subdomain / Slug <span style="color:var(--red)">*</span></label>
            <div style="display:flex;align-items:center">
                <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}" required class="form-input" style="flex:1;padding:10px 14px;border-radius:8px 0 0 8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <span style="padding:10px 14px;background:var(--bg-alt);border:1px solid var(--line);border-left:none;border-radius:0 8px 8px 0;font-size:13px;color:var(--ink-soft)">.{{ config('app.tenant_domain') }}</span>
            </div>
            @error('slug')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Email Kontak <span style="color:var(--red)">*</span></label>
                <input type="email" name="email" value="{{ old('email', $tenant->email) }}" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('email')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Nomor WhatsApp</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $tenant->whatsapp_number) }}" placeholder="Contoh: 08123456789" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('whatsapp_number')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="margin-bottom:24px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Status Tenant <span style="color:var(--red)">*</span></label>
            <select name="status" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <option value="active" {{ old('status', $tenant->status) == 'active' ? 'selected' : '' }}>Aktif (Normal)</option>
                <option value="trial" {{ old('status', $tenant->status) == 'trial' ? 'selected' : '' }}>Trial (Masa Uji Coba)</option>
                <option value="suspended" {{ old('status', $tenant->status) == 'suspended' ? 'selected' : '' }}>Suspended (Ditangguhkan / Dibekukan)</option>
            </select>
            @error('status')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline" style="padding:10px 20px">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
