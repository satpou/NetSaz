@extends('layouts.app')

@section('title', 'Buat Tiket')

@section('content')
<x-page-header title="Buat Tiket" subtitle="Catat laporan kendala atau permintaan dukungan pelanggan" />

<div class="panel" style="max-width:640px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('tickets.store', ['tenant_slug' => request()->route('tenant_slug')]) }}" method="POST">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
                <div>
                    <label class="form-label">Pelanggan</label>
                    <select name="customer_id" class="form-input">
                        <option value="">-- Umum / Internal --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (string) old('customer_id') === (string) $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Ditugaskan ke</label>
                    <select name="assigned_to" class="form-input">
                        <option value="">-- Belum ditugaskan --</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}" {{ (string) old('assigned_to') === (string) $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label">Subjek <span style="color:var(--red)">*</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input" placeholder="Cth: Koneksi terputus / LOS merah">
            </div>

            <div style="margin-bottom:18px">
                <label class="form-label">Prioritas <span style="color:var(--red)">*</span></label>
                <select name="priority" required class="form-input">
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ old('priority', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>

            <div style="margin-bottom:24px">
                <label class="form-label">Deskripsi Masalah <span style="color:var(--red)">*</span></label>
                <textarea name="message" rows="4" required class="form-input" placeholder="Jelaskan detail kendala atau tindakan yang diperlukan">{{ old('message') }}</textarea>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Simpan Tiket</button>
                <a href="{{ route('tickets.index', ['tenant_slug' => request()->route('tenant_slug')]) }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
