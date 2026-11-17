@extends('layouts.app')

@section('title', 'Edit Subscription - ' . ($subscription->tenant?->name ?? ''))

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <a href="{{ route('admin.subscriptions.index') }}" style="color:inherit;text-decoration:none">Subscription</a> &gt;
    <b>Edit {{ $subscription->tenant?->name }}</b>
</div>

<x-page-header title="Edit Subscription ISP" subtitle="Ubah paket atau perpanjang masa aktif tenant">
</x-page-header>

@if(session('error'))
    <div style="background:var(--red-tint);border:1px solid var(--red);color:var(--red);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

<div class="panel" style="max-width:680px">
    <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
        @csrf
        @method('PUT')

        <div style="margin-bottom:18px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Tenant ISP</label>
            <input type="text" disabled value="{{ $subscription->tenant?->name }} ({{ $subscription->tenant?->slug }}.{{ config('app.tenant_domain') }})" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:14px">
        </div>

        <div style="margin-bottom:18px">
            <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Paket Langganan <span style="color:var(--red)">*</span></label>
            <select name="subscription_plan_id" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @foreach($plans as $p)
                    <option value="{{ $p->id }}" {{ old('subscription_plan_id', $subscription->subscription_plan_id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} - Rp{{ number_format($p->price, 0, ',', '.') }}/{{ $p->billing_period }} (Maks. {{ $p->max_active_customers ?: 'Unlimited' }} Pelanggan)
                    </option>
                @endforeach
            </select>
            @error('subscription_plan_id')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Status <span style="color:var(--red)">*</span></label>
                <select name="status" required class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                    <option value="active" {{ old('status', $subscription->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="trial" {{ old('status', $subscription->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                    <option value="expired" {{ old('status', $subscription->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ old('status', $subscription->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="form-label" style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;color:var(--ink)">Tanggal Berakhir</label>
                <input type="date" name="current_period_end" value="{{ old('current_period_end', $subscription->current_period_end ? $subscription->current_period_end->format('Y-m-d') : '') }}" class="form-input" style="width:100%;padding:10px 14px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:14px">
                @error('current_period_end')<div style="color:var(--red);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:24px">
            <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline" style="padding:10px 20px">Batal</a>
            <button type="submit" class="btn btn-primary" style="padding:10px 24px">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
