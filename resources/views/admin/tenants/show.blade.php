@extends('layouts.app')

@section('title', 'Detail Tenant - ' . $tenant->name)

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('dashboard.app') }}" style="color:inherit;text-decoration:none">Home</a> &gt;
    <a href="{{ route('admin.tenants.index') }}" style="color:inherit;text-decoration:none">Kelola Tenant</a> &gt;
    <b>{{ $tenant->name }}</b>
</div>

<x-page-header title="{{ $tenant->name }}" subtitle="Detail operasional & status langganan tenant">
    <div style="display:flex;gap:10px">
        <a href="https://{{ $tenant->slug }}.{{ config('app.tenant_domain') }}" target="_blank" class="btn btn-outline">
            Buka Web Tenant ↗
        </a>
        <a href="{{ route('admin.tenants.edit', $tenant) }}" class="btn btn-primary">
            Edit Tenant
        </a>
        <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" style="display:inline"
              data-confirm="Apakah Anda yakin ingin menghapus tenant '{{ $tenant->name }}'? Tenant akan dipindahkan ke kotak sampah (Trash)."
              data-confirm-title="Pindahkan ke Kotak Sampah"
              data-confirm-type="danger"
              data-confirm-text="Ya, Pindahkan">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline" style="color:var(--red);border-color:var(--red-tint)">
                Hapus Tenant
            </button>
        </form>
    </div>
</x-page-header>

<!-- Stat cards for this tenant -->
<div class="stat-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px">
    <div class="stat-card">
        <div class="stat-top"><span class="stat-label">Total Pelanggan</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/></svg></span></div>
        <div class="stat-value">{{ $tenant->customers_count }}</div>
        <div class="stat-desc">Pelanggan terdaftar</div>
    </div>
    <div class="stat-card">
        <div class="stat-top"><span class="stat-label">Total Pengguna</span><span class="stat-icon green"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg></span></div>
        <div class="stat-value">{{ $tenant->users->count() }}</div>
        <div class="stat-desc">Admin & Staff internal</div>
    </div>
    <div class="stat-card">
        <div class="stat-top"><span class="stat-label">Paket Layanan</span><span class="stat-icon blue"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></span></div>
        <div class="stat-value">{{ $tenant->packages->count() }}</div>
        <div class="stat-desc">Paket internet aktif</div>
    </div>
    <div class="stat-card">
        <div class="stat-top"><span class="stat-label">Total Tagihan</span><span class="stat-icon amber"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></span></div>
        <div class="stat-value">{{ $tenant->invoices_count }}</div>
        <div class="stat-desc">Invoice diterbitkan</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;margin-bottom:24px">
    <!-- Info Tenant -->
    <div class="panel">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--ink)">Informasi Profil ISP</h3>
        <table style="width:100%;font-size:13.5px">
            <tr style="border-bottom:1px solid var(--line)">
                <td style="padding:10px 0;color:var(--ink-soft);width:160px">Nama ISP</td>
                <td style="padding:10px 0;font-weight:600;color:var(--ink)">{{ $tenant->name }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--line)">
                <td style="padding:10px 0;color:var(--ink-soft)">Subdomain / Slug</td>
                <td style="padding:10px 0"><code class="mono" style="color:var(--primary)">{{ $tenant->slug }}.{{ config('app.tenant_domain') }}</code></td>
            </tr>
            <tr style="border-bottom:1px solid var(--line)">
                <td style="padding:10px 0;color:var(--ink-soft)">Email Kontak</td>
                <td style="padding:10px 0;color:var(--ink)">{{ $tenant->email }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--line)">
                <td style="padding:10px 0;color:var(--ink-soft)">WhatsApp</td>
                <td style="padding:10px 0;color:var(--ink)">{{ $tenant->whatsapp_number ?? '-' }}</td>
            </tr>
            <tr style="border-bottom:1px solid var(--line)">
                <td style="padding:10px 0;color:var(--ink-soft)">Status Operasional</td>
                <td style="padding:10px 0">
                    @if($tenant->status === 'active')
                        <span class="status-pill ok" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Aktif</span>
                    @elseif($tenant->status === 'trial')
                        <span class="status-pill warn" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:#f59e0b"></span>Trial</span>
                    @else
                        <span class="status-pill danger" style="padding:3px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Suspend</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding:10px 0;color:var(--ink-soft)">Tanggal Bergabung</td>
                <td style="padding:10px 0;color:var(--ink)">{{ $tenant->created_at->translatedFormat('d F Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <!-- Info Subscription -->
    <div class="panel">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--ink)">Status Langganan Platform</h3>
        @if($tenant->activeSubscription && $tenant->activeSubscription->plan)
            @php $sub = $tenant->activeSubscription; @endphp
            <div style="background:var(--panel-soft);border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:16px">
                <div style="font-size:18px;font-weight:700;color:var(--primary)">{{ $sub->plan->name }}</div>
                <div style="font-size:13px;color:var(--ink-soft);margin-top:2px">Rp{{ number_format($sub->plan->price, 0, ',', '.') }} / {{ $sub->plan->billing_period }}</div>
                <div style="margin-top:12px;font-size:12.5px;color:var(--ink-faint)">
                    <div>Mulai: {{ $sub->started_at ? $sub->started_at->format('d M Y') : '-' }}</div>
                    <div>Berakhir: <b style="color:var(--ink)">{{ $sub->current_period_end ? $sub->current_period_end->format('d M Y') : 'Unlimited' }}</b></div>
                </div>
            </div>
        @else
            <div style="padding:24px;text-align:center;color:var(--ink-faint);background:var(--panel-soft);border-radius:10px;margin-bottom:16px">
                Belum ada paket langganan aktif.
            </div>
        @endif

        <a href="{{ route('admin.subscriptions.create', ['tenant_id' => $tenant->id]) }}" class="btn btn-outline" style="width:100%;text-align:center;justify-content:center">
            + Beri Paket Langganan Baru
        </a>
    </div>
</div>

<!-- Users List Panel -->
<div class="panel" style="margin-bottom:24px">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;color:var(--ink)">Akun Pengguna ISP ({{ $tenant->users->count() }})</h3>
    <table class="data-table" style="width:100%;border-collapse:collapse;font-size:13.5px">
        <thead>
            <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                <th style="padding:12px 16px">Nama</th>
                <th style="padding:12px 16px">Email</th>
                <th style="padding:12px 16px">Role</th>
                <th style="padding:12px 16px">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tenant->users as $u)
                <tr style="border-bottom:1px solid var(--line)">
                    <td style="padding:12px 16px;font-weight:600;color:var(--ink)">{{ $u->name }}</td>
                    <td style="padding:12px 16px;color:var(--ink-soft)">{{ $u->email }}</td>
                    <td style="padding:12px 16px">
                        <span style="font-size:12px;padding:3px 8px;border-radius:6px;background:var(--bg-alt);color:var(--primary);font-weight:600">
                            {{ ucfirst(str_replace('_', ' ', $u->role)) }}
                        </span>
                    </td>
                    <td style="padding:12px 16px">
                        @if($u->is_active)
                            <span style="color:var(--green)">● Aktif</span>
                        @else
                            <span style="color:var(--red)">● Nonaktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="padding:20px;text-align:center;color:var(--ink-faint)">Belum ada user</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
