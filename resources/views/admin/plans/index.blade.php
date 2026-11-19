@extends('layouts.app')

@section('title', 'Paket Langganan Platform')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Paket Langganan</b>
</div>

<x-page-header title="Paket Langganan Platform" subtitle="Atur paket harga, kuota pelanggan, dan fitur untuk ISP">
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Paket Baru
    </a>
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div style="background:var(--red-tint);border:1px solid var(--red);color:var(--red);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- Plans Grid / Table Panel -->
<div class="panel" style="overflow:hidden;padding:0">
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase;letter-spacing:.04em">
                    <th style="padding:16px 20px 14px 24px">Nama & Kode Paket</th>
                    <th style="padding:16px 16px 14px">Harga Langganan</th>
                    <th style="padding:16px 16px 14px;text-align:center">Maks Pelanggan</th>
                    <th style="padding:16px 16px 14px;text-align:center">Maks Router</th>
                    <th style="padding:16px 16px 14px;text-align:center">Maks Staff</th>
                    <th style="padding:16px 16px 14px;text-align:center">Tenant Aktif</th>
                    <th style="padding:16px 16px 14px">Status</th>
                    <th style="padding:16px 24px 14px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr style="border-bottom:1px solid var(--line);transition:background .15s">
                        <td style="padding:16px 20px 16px 24px">
                            <div style="font-weight:700;color:var(--ink);font-size:15px">{{ $plan->name }}</div>
                            <div style="font-size:12px;color:var(--ink-faint);margin-top:2px">
                                Kode: <code class="mono" style="color:var(--primary)">{{ $plan->code }}</code>
                            </div>
                        </td>
                        <td style="padding:16px 16px">
                            <div style="font-size:15px;font-weight:700;color:var(--primary)">
                                Rp{{ number_format($plan->price, 0, ',', '.') }}
                            </div>
                            <div style="font-size:11.5px;color:var(--ink-faint)">per {{ $plan->billing_period === 'monthly' ? 'bulan' : 'tahun' }}</div>
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            @if($plan->max_active_customers == 0)
                                <span style="color:var(--green);font-weight:600">Unlimited</span>
                            @else
                                <span class="mono" style="font-weight:600">{{ number_format($plan->max_active_customers) }}</span>
                            @endif
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            @if($plan->max_routers == 0)
                                <span style="color:var(--green);font-weight:600">Unlimited</span>
                            @else
                                <span class="mono" style="font-weight:600">{{ $plan->max_routers }}</span>
                            @endif
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            @if($plan->max_staff == 0)
                                <span style="color:var(--green);font-weight:600">Unlimited</span>
                            @else
                                <span class="mono" style="font-weight:600">{{ $plan->max_staff }}</span>
                            @endif
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            <span class="mono" style="font-weight:700;color:var(--primary)">{{ $plan->tenant_subscriptions_count }}</span>
                        </td>
                        <td style="padding:16px 16px">
                            @if($plan->is_active)
                                <span class="status-pill ok" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Aktif</span>
                            @else
                                <span class="status-pill danger" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Nonaktif</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px 16px 16px;text-align:right">
                            <div style="display:inline-flex;gap:6px">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px">Edit</a>
                                <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" style="display:inline"
                                      data-confirm="Hapus paket langganan '{{ $plan->name }}'?"
                                      data-confirm-title="Hapus Paket Langganan"
                                      data-confirm-type="danger"
                                      data-confirm-text="Ya, Hapus Paket">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:12.5px;color:var(--red);border-color:var(--red-tint)">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="padding:48px;text-align:center;color:var(--ink-faint)">
                            Belum ada paket langganan. Silakan klik "Tambah Paket Baru" atau jalankan seeder.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
