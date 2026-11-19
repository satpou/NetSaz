@php
    $user = Auth::user();
    $host = request()->getHost();
    $tenantDomain = config('app.tenant_domain');
    $isTenantDomain = $host !== $tenantDomain && str_ends_with($host, '.'.$tenantDomain);
    $ts = $isTenantDomain ? str_replace('.'.$tenantDomain, '', $host) : ($user?->tenant?->slug ?? session('tenant_slug') ?? null);
    if ($ts) {
        \Illuminate\Support\Facades\URL::defaults(['tenant_slug' => $ts]);
    }
@endphp
@extends('layouts.app')

@section('title', 'Auto Isolir Tagihan')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Pelanggan &amp; Billing</span> &gt; <b>Auto Isolir</b>
</div>

<x-page-header title="Otomasi Isolir Pelanggan Menunggak" subtitle="Konfigurasi isolir otomatis MikroTik saat tagihan melewati batas masa tenggang (grace period)">
    <button onclick="document.getElementById('modal-config-isolir').style.display='flex'" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Pengaturan Auto Isolir
    </button>
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('warning'))
    <div style="background:var(--amber-tint);border:1px solid var(--amber);color:var(--amber);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ session('warning') }}</span>
    </div>
@endif

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Sistem Auto Isolir</div>
        <div style="display:flex;align-items:baseline;gap:8px;margin-top:6px">
            <span style="font-size:24px;font-weight:700;color:var(--ink)">{{ !empty($settings['auto_isolate_enabled']) ? 'Aktif' : 'Nonaktif' }}</span>
            <span class="status-pill {{ !empty($settings['auto_isolate_enabled']) ? 'ok' : 'danger' }}" style="padding:2px 8px;font-size:11px">
                <span class="dot" style="background:{{ !empty($settings['auto_isolate_enabled']) ? 'var(--primary)' : '#ef4444' }}"></span>
                {{ !empty($settings['auto_isolate_enabled']) ? 'Enabled' : 'Disabled' }}
            </span>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Grace Period: <b>{{ $settings['grace_period_days'] ?? 3 }} Hari</b> setelah jatuh tempo</div>
    </div>

    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Pelanggan Sedang Di-Isolir</div>
        <div style="font-size:28px;font-weight:700;color:#ef4444;margin-top:6px">
            {{ $isolatedCustomers->total() }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Pelanggan</span>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Profile MikroTik: <code class="mono">{{ $settings['isolir_profile_name'] ?? 'profile-isolir' }}</code></div>
    </div>

    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Invoice Lewat Jatuh Tempo</div>
        <div style="font-size:28px;font-weight:700;color:#f59e0b;margin-top:6px">
            {{ $overdueInvoices }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Invoice</span>
        </div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Memenuhi syarat isolir otomatis</div>
    </div>
</div>

<!-- Isolated Customers Table -->
<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="padding:18px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
        <div>
            <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0">Daftar Pelanggan Terisolir</h3>
            <p style="font-size:13px;color:var(--ink-soft);margin:3px 0 0">Pelanggan dengan akses internet terisolir ke portal tagihan</p>
        </div>
        <form action="{{ route('billing.auto-isolir', $ts ? ['tenant_slug' => $ts] : []) }}" method="GET" style="display:flex;gap:8px">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode..." class="form-input" style="padding:6px 12px;font-size:13px;width:200px">
            <button type="submit" class="btn btn-outline" style="padding:6px 12px">Cari</button>
        </form>
    </div>

    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Pelanggan</th>
                    <th style="padding:14px 16px">Username PPPoE</th>
                    <th style="padding:14px 16px">Area / Wilayah</th>
                    <th style="padding:14px 16px">Paket</th>
                    <th style="padding:14px 16px">Status</th>
                    <th style="padding:14px 20px;text-align:right">Aksi Buka Isolir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($isolatedCustomers as $cust)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px">
                            <div style="font-weight:600;color:var(--ink)">{{ $cust->name }}</div>
                            <div style="font-size:12px;color:var(--ink-faint)">{{ $cust->customer_code }}</div>
                        </td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:13px;color:var(--primary)">
                            {{ $cust->pppoe_username ?? '-' }}
                        </td>
                        <td style="padding:14px 16px;font-size:13px;color:var(--ink-soft)">
                            {{ $cust->area?->name ?? 'Semua Area' }}
                        </td>
                        <td style="padding:14px 16px;font-size:13px;color:var(--ink-soft)">
                            {{ $cust->package?->name ?? '-' }}
                        </td>
                        <td style="padding:14px 16px">
                            <span class="status-pill danger" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:#ef4444"></span>Terisolir</span>
                        </td>
                        <td style="padding:14px 20px;text-align:right">
                            <form action="{{ route('billing.auto-isolir.toggle', array_merge($ts ? ['tenant_slug' => $ts] : [], ['customer' => $cust->id])) }}" method="POST" onsubmit="return confirm('Buka isolir untuk pelanggan {{ $cust->name }}? Internet akan langsung aktif kembali.');" style="display:inline">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="padding:5px 12px;font-size:12px">Buka Isolir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Tidak ada pelanggan yang sedang terisolir saat ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($isolatedCustomers->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--line)">
            {{ $isolatedCustomers->withQueryString()->links() }}
        </div>
    @endif
</div>

<!-- Modal Config Auto Isolir -->
<div id="modal-config-isolir" class="modal-overlay" style="display:none;align-items:center;justify-content:center;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999">
    <div class="modal-card" style="background:var(--bg-card);border:1px solid var(--line);border-radius:14px;width:100%;max-width:520px;padding:24px">
        <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:12px">Konfigurasi Sistem Auto-Isolir</h3>
        <form action="{{ route('billing.auto-isolir.settings', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom:18px;display:flex;align-items:center;gap:10px">
                <input type="checkbox" name="auto_isolate_enabled" id="auto_isolate_enabled" value="1" {{ !empty($settings['auto_isolate_enabled']) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--primary)">
                <label for="auto_isolate_enabled" style="font-size:14px;font-weight:600;color:var(--ink);cursor:pointer">Aktifkan Auto-Isolir Otomatis (Cron Schedule)</label>
            </div>

            <div class="form-field" style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Grace Period / Masa Tenggang (Hari)</label>
                <input type="number" name="grace_period_days" class="form-input" value="{{ $settings['grace_period_days'] ?? 3 }}" min="0" max="30" required>
                <small style="font-size:11.5px;color:var(--ink-faint)">Berapa hari setelah tanggal jatuh tempo pelanggan akan di-isolir jika belum membayar.</small>
            </div>

            <div class="form-field" style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Nama Profile PPP Isolir di MikroTik</label>
                <input type="text" name="isolir_profile_name" class="form-input mono" value="{{ $settings['isolir_profile_name'] ?? 'profile-isolir' }}" required>
                <small style="font-size:11.5px;color:var(--ink-faint)">Pastikan profile ini sudah dibuat di router MikroTik (/ppp profile).</small>
            </div>

            <div class="form-field" style="margin-bottom:16px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Nama Address List Isolir Firewall</label>
                <input type="text" name="isolir_address_list" class="form-input mono" value="{{ $settings['isolir_address_list'] ?? 'ISOLIR_LIST' }}">
            </div>

            <div style="margin-bottom:24px;display:flex;align-items:center;gap:10px">
                <input type="checkbox" name="send_wa_notice" id="send_wa_notice" value="1" {{ !empty($settings['isolir_send_wa_notice']) ? 'checked' : '' }} style="width:18px;height:18px;accent-color:var(--primary)">
                <label for="send_wa_notice" style="font-size:13.5px;color:var(--ink);cursor:pointer">Kirim pesan WhatsApp otomatis ke pelanggan saat di-isolir</label>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="document.getElementById('modal-config-isolir').style.display='none'" class="btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>
@endsection
