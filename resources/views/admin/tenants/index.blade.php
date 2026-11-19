@extends('layouts.app')

@section('title', 'Kelola Tenant ISP')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt; <b>Kelola Tenant</b>
</div>

<x-page-header title="Kelola Tenant ISP" subtitle="{{ $counts['total'] }} ISP terdaftar ({{ $counts['active'] }} aktif, {{ $counts['trial'] }} trial, {{ $counts['suspended'] }} suspend)">
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

<!-- Filter Tabs -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
    <a href="{{ route('admin.tenants.index') }}" class="btn {{ empty($status) ? 'btn-primary' : 'btn-outline' }}" style="padding:7px 16px;font-size:13px;border-radius:20px">
        Semua ({{ $counts['total'] }})
    </a>
    <a href="{{ route('admin.tenants.index', ['status' => 'active']) }}" class="btn {{ $status === 'active' ? 'btn-primary' : 'btn-outline' }}" style="padding:7px 16px;font-size:13px;border-radius:20px">
        Aktif ({{ $counts['active'] }})
    </a>
    <a href="{{ route('admin.tenants.index', ['status' => 'trial']) }}" class="btn {{ $status === 'trial' ? 'btn-primary' : 'btn-outline' }}" style="padding:7px 16px;font-size:13px;border-radius:20px">
        Trial ({{ $counts['trial'] }})
    </a>
    <a href="{{ route('admin.tenants.index', ['status' => 'suspended']) }}" class="btn {{ $status === 'suspended' ? 'btn-primary' : 'btn-outline' }}" style="padding:7px 16px;font-size:13px;border-radius:20px">
        Suspend ({{ $counts['suspended'] }})
    </a>
    <a href="{{ route('admin.tenants.index', ['status' => 'trashed']) }}" class="btn {{ $status === 'trashed' ? 'btn-primary' : 'btn-outline' }}" style="padding:7px 16px;font-size:13px;border-radius:20px;color:{{ $status === 'trashed' ? '#fff' : 'var(--red)' }};border-color:var(--red-tint)">
        Kotak Sampah ({{ $counts['trashed'] }})
    </a>
</div>

<!-- Filter Panel -->
<div class="panel" style="padding:20px;margin-bottom:24px">
    <form method="GET" action="{{ route('admin.tenants.index') }}" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:14px">
        <div style="flex:1;min-width:240px">
            <label class="form-label" style="font-size:12.5px;color:var(--ink-soft);margin-bottom:6px;display:block">Cari Tenant</label>
            <input type="text" name="search" placeholder="Nama ISP, slug, email, atau no. WA..." value="{{ request('search') }}" class="form-input" autocomplete="off" style="width:100%;padding:9px 13px;border-radius:8px;border:1px solid var(--line);background:var(--bg);color:var(--ink);font-size:13.5px">
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="btn btn-outline" style="padding:9px 18px;border-radius:8px;font-size:13.5px">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline" style="padding:9px 18px;border-radius:8px;font-size:13.5px">Reset</a>
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
                    <th style="padding:16px 20px 14px 24px">Nama ISP & Subdomain</th>
                    <th style="padding:16px 16px 14px">Kontak</th>
                    <th style="padding:16px 16px 14px;text-align:center">Pelanggan</th>
                    <th style="padding:16px 16px 14px;text-align:center">Staff</th>
                    <th style="padding:16px 16px 14px">Paket Aktif</th>
                    <th style="padding:16px 16px 14px">Status</th>
                    <th style="padding:16px 24px 14px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $tenant)
                    <tr style="border-bottom:1px solid var(--line);transition:background .15s">
                        <td style="padding:16px 20px 16px 24px">
                            <div style="font-weight:600;color:var(--ink);font-size:14.5px">{{ $tenant->name }}</div>
                            <div style="font-size:12px;color:var(--ink-faint);margin-top:2px">
                                <a href="https://{{ $tenant->slug }}.{{ config('app.tenant_domain') }}" target="_blank" style="color:var(--primary);text-decoration:none">
                                    {{ $tenant->slug }}.{{ config('app.tenant_domain') }} ↗
                                </a>
                            </div>
                        </td>
                        <td style="padding:16px 16px">
                            <div style="color:var(--ink)">{{ $tenant->email }}</div>
                            <div style="font-size:12px;color:var(--ink-faint)">{{ $tenant->whatsapp_number ?? '-' }}</div>
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            <span class="mono" style="font-weight:600;font-size:14px">{{ $tenant->customers_count }}</span>
                        </td>
                        <td style="padding:16px 16px;text-align:center">
                            <span class="mono" style="font-weight:600;font-size:14px">{{ $tenant->users_count }}</span>
                        </td>
                        <td style="padding:16px 16px">
                            @if($tenant->activeSubscription && $tenant->activeSubscription->plan)
                                <span style="font-weight:600;color:var(--primary)">{{ $tenant->activeSubscription->plan->name }}</span>
                                <div style="font-size:11.5px;color:var(--ink-faint)">Rp{{ number_format($tenant->activeSubscription->plan->price, 0, ',', '.') }}/bln</div>
                            @else
                                <span style="color:var(--ink-faint);font-style:italic">Tidak ada paket</span>
                            @endif
                        </td>
                        <td style="padding:16px 16px">
                            @if($tenant->trashed())
                                <span class="status-pill danger" style="padding:4px 10px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Terhapus</span>
                            @elseif($tenant->status === 'active')
                                <span class="status-pill ok" style="padding:4px 10px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Aktif</span>
                            @elseif($tenant->status === 'trial')
                                <span class="status-pill warn" style="padding:4px 10px;font-size:11px"><span class="dot" style="background:#f59e0b"></span>Trial</span>
                            @else
                                <span class="status-pill danger" style="padding:4px 10px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Suspend</span>
                            @endif
                        </td>
                        <td style="padding:16px 24px 16px 16px;text-align:right">
                            <div style="display:inline-flex;align-items:center;gap:6px">
                                @if($tenant->trashed())
                                    <form method="POST" action="{{ route('admin.tenants.restore', $tenant->id) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:12px;color:var(--green);border-color:var(--green)">
                                            Pulihkan
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.tenants.force-delete', $tenant->id) }}" style="display:inline"
                                          data-confirm="Hapus permanen tenant '{{ $tenant->name }}'? Seluruh data akan dihapus selamanya!"
                                          data-confirm-title="Hapus Tenant Permanen"
                                          data-confirm-type="danger"
                                          data-confirm-text="Ya, Hapus Selamanya">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:12px;color:var(--red);border-color:var(--red-tint)">
                                            Hapus Permanen
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.tenants.show', $tenant) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px" title="Detail Tenant">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px" title="Edit Tenant">
                                        Edit
                                    </a>
                                    <!-- Toggle Status Form -->
                                    <form method="POST" action="{{ route('admin.tenants.toggle-status', $tenant) }}" style="display:inline"
                                          data-confirm="Ubah status tenant '{{ $tenant->name }}' menjadi {{ $tenant->status === 'active' ? 'SUSPENDED' : 'ACTIVE' }}?"
                                          data-confirm-title="Konfirmasi Status Tenant"
                                          data-confirm-type="warning"
                                          data-confirm-text="Ya, Ubah Status">
                                        @csrf
                                        @if($tenant->status === 'active')
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:12px;color:var(--red);border-color:var(--red-tint)" title="Suspend ISP">
                                                Suspend
                                            </button>
                                        @else
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:12px;color:var(--primary);border-color:var(--primary-tint)" title="Aktifkan ISP">
                                                Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                    <!-- Delete Tenant Form -->
                                    <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" style="display:inline"
                                          data-confirm="Apakah Anda yakin ingin menghapus tenant '{{ $tenant->name }}'? Tenant akan dipindahkan ke kotak sampah (Trash)."
                                          data-confirm-title="Pindahkan ke Kotak Sampah"
                                          data-confirm-type="danger"
                                          data-confirm-text="Ya, Pindahkan">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline" style="padding:6px 10px;font-size:12px;color:var(--red);border-color:var(--red-tint)" title="Hapus Tenant">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:48px 24px;text-align:center;color:var(--ink-faint)">
                            Belum ada tenant ISP yang sesuai pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
        <div style="padding:16px 24px;border-top:1px solid var(--line)">
            {{ $tenants->links() }}
        </div>
    @endif
</div>
@endsection
