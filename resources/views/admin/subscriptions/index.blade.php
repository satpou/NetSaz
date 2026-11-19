@extends('layouts.app')

@section('title', 'Subscription ISP')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Subscription</b>
</div>

<x-page-header title="Subscription ISP" subtitle="{{ $counts['total'] }} langganan ({{ $counts['active'] }} aktif, {{ $counts['trial'] }} trial, {{ $counts['expired'] }} kedaluwarsa)">
    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Beri Langganan Baru
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

<!-- Filter Panel -->
<div class="panel" style="padding:20px;margin-bottom:24px">
    <form method="GET" action="{{ route('admin.subscriptions.index') }}" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px">
        <div style="flex:1;min-width:220px">
            <label class="form-label" style="font-size:12.5px;color:var(--ink-soft);margin-bottom:6px;display:block">Cari ISP</label>
            <input type="text" name="search" placeholder="Nama ISP, email..." value="{{ request('search') }}" class="form-input" autocomplete="off" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:13.5px">
        </div>
        <div style="flex-shrink:0;min-width:160px">
            <label class="form-label" style="font-size:12.5px;color:var(--ink-soft);margin-bottom:6px;display:block">Paket</label>
            <select name="plan_id" class="form-input" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:13.5px">
                <option value="">Semua Paket</option>
                @foreach($plans as $p)
                    <option value="{{ $p->id }}" {{ request('plan_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex-shrink:0;min-width:150px">
            <label class="form-label" style="font-size:12.5px;color:var(--ink-soft);margin-bottom:6px;display:block">Status</label>
            <select name="status" class="form-input" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:13.5px">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="btn btn-outline" style="padding:9px 18px;border-radius:8px;font-size:13.5px">Filter</button>
            @if(request()->hasAny(['search', 'plan_id', 'status']))
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline" style="padding:9px 18px;border-radius:8px;font-size:13.5px">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Table Panel -->
<div class="panel" style="overflow:hidden;padding:0">
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase;letter-spacing:.04em">
                    <th style="padding:16px 20px 14px 24px">Tenant ISP</th>
                    <th style="padding:16px 16px 14px">Paket Langganan</th>
                    <th style="padding:16px 16px 14px">Periode Aktif</th>
                    <th style="padding:16px 16px 14px">Status</th>
                    <th style="padding:16px 24px 14px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    <tr style="border-bottom:1px solid var(--line);transition:background .15s">
                        <td style="padding:16px 20px 16px 24px">
                            <div style="font-weight:600;color:var(--ink);font-size:14.5px">{{ $sub->tenant?->name ?? 'Unknown Tenant' }}</div>
                            <div style="font-size:12px;color:var(--ink-faint);margin-top:2px">{{ $sub->tenant?->email }}</div>
                        </td>
                        <td style="padding:16px 16px">
                            <div style="font-weight:700;color:var(--primary);font-size:14.5px">{{ $sub->plan?->name ?? 'Deleted Plan' }}</div>
                            <div style="font-size:12px;color:var(--ink-faint)">
                                Rp{{ number_format($sub->plan?->price ?? 0, 0, ',', '.') }}/{{ $sub->plan?->billing_period ?? 'bln' }}
                            </div>
                        </td>
                        <td style="padding:16px 16px">
                            <div style="font-size:13px;color:var(--ink)">
                                Berakhir: <b style="color:var(--ink)">{{ $sub->current_period_end ? $sub->current_period_end->format('d M Y') : 'Selamanya' }}</b>
                            </div>
                            <div style="font-size:11.5px;color:var(--ink-faint)">
                                Mulai: {{ $sub->started_at ? $sub->started_at->format('d M Y') : '-' }}
                            </div>
                        </td>
                        <td style="padding:16px 16px">
                            @if($sub->status === 'active')
                                <span class="status-pill ok" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Aktif</span>
                            @elseif($sub->status === 'trial')
                                <span class="status-pill warn" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:#f59e0b"></span>Trial</span>
                            @elseif($sub->status === 'expired')
                                <span class="status-pill danger" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Expired</span>
                            @else
                                <span class="status-pill" style="padding:3px 8px;font-size:11px;background:var(--bg-alt);color:var(--ink-soft)">Cancelled</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px 16px 16px;text-align:right">
                            <div style="display:inline-flex;align-items:center;gap:6px">
                                <!-- Quick Extend 1 Bulan -->
                                <form method="POST" action="{{ route('admin.subscriptions.extend', $sub) }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="months" value="1">
                                    <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:12px;color:var(--primary)" title="Perpanjang 1 Bulan">
                                        +1 Bln
                                    </button>
                                </form>
                                <a href="{{ route('admin.subscriptions.edit', $sub) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px">
                                    Edit
                                </a>
                                @if($sub->status !== 'cancelled')
                                    <form method="POST" action="{{ route('admin.subscriptions.destroy', $sub) }}" style="display:inline"
                                          data-confirm="Apakah Anda yakin ingin membatalkan langganan tenant '{{ $sub->tenant->name ?? '' }}'?"
                                          data-confirm-title="Batalkan Langganan"
                                          data-confirm-type="danger"
                                          data-confirm-text="Ya, Batalkan">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:12px;color:var(--red);border-color:var(--red-tint)">
                                            Batal
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:48px 24px;text-align:center;color:var(--ink-faint)">
                            Belum ada riwayat subscription.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subscriptions->hasPages())
        <div style="padding:16px 24px;border-top:1px solid var(--line)">
            {{ $subscriptions->links() }}
        </div>
    @endif
</div>
@endsection
