@extends('layouts.app')

@section('title', 'Beri Langganan Baru')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <a href="{{ route('admin.subscriptions.index') }}" style="color:inherit;text-decoration:none">Subscription</a> &gt;
    <b>Tambah Langganan</b>
</div>

<x-page-header title="Beri Langganan Tenant" subtitle="Aktifkan atau ubah paket langganan untuk tenant ISP">
</x-page-header>

<div class="panel" style="max-width:680px">
    <form method="POST" action="{{ route('admin.subscriptions.store') }}">
        @csrf

        <div style="margin-bottom:18px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Pilih Tenant ISP <span style="color:var(--red)">*</span></label>
            <select name="tenant_id" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <option value="">-- Pilih Tenant --</option>
                @foreach($tenants as $t)
                    <option value="{{ $t->id }}" {{ old('tenant_id', request('tenant_id')) == $t->id ? 'selected' : '' }}>
                        {{ $t->name }} ({{ $t->slug }}.{{ config('app.tenant_domain') }})
                    </option>
                @endforeach
            </select>
            @error('tenant_id')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="margin-bottom:18px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Pilih Paket Langganan <span style="color:var(--red)">*</span></label>
            <select name="subscription_plan_id" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                <option value="">-- Pilih Paket --</option>
                @foreach($plans as $p)
                    <option value="{{ $p->id }}" {{ old('subscription_plan_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} - Rp{{ number_format($p->price, 0, ',', '.') }}/{{ $p->billing_period }} (Maks. {{ $p->max_active_customers ?: 'Unlimited' }} Pelanggan)
                    </option>
                @endforeach
            </select>
            @error('subscription_plan_id')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Status Langganan <span style="color:var(--red)">*</span></label>
                <select name="status" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="trial" {{ old('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
                @error('status')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Durasi Langganan (Bulan) <span style="color:var(--red)">*</span></label>
                <input type="number" name="duration_months" value="{{ old('duration_months', 1) }}" min="1" max="36" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('duration_months')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px">
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline" style="padding:10px 20px">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Aktifkan Langganan</button>
        </div>
    </form>
</div>
@endsection
