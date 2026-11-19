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

@section('title', 'Manajemen Staff & Tim')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Operasional</span> &gt; <b>Staff</b>
</div>

<x-page-header title="Manajemen Staff &amp; Teknisi" subtitle="Kelola hak akses akun operator billing, customer service, dan teknisi lapangan ISP Anda">
    <button onclick="document.getElementById('modal-add-staff').style.display='flex'" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Anggota Staff
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

<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Nama Anggota</th>
                    <th style="padding:14px 16px">Email Akun</th>
                    <th style="padding:14px 16px">Role / Hak Akses</th>
                    <th style="padding:14px 16px">Status Akun</th>
                    <th style="padding:14px 16px">Terdaftar</th>
                    <th style="padding:14px 20px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffMembers as $staff)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px">
                            <div style="font-weight:600;color:var(--ink)">{{ $staff->name }}</div>
                            @if($staff->id === auth()->id())
                                <span style="font-size:11px;color:var(--primary);font-weight:600">(Akun Anda)</span>
                            @endif
                        </td>
                        <td style="padding:14px 16px;color:var(--ink-soft)">
                            {{ $staff->email }}
                        </td>
                        <td style="padding:14px 16px">
                            @if($staff->role === 'admin' || $staff->role === 'tenant_admin' || $staff->role === 'super_admin')
                                <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Administrator</span>
                            @else
                                <span class="status-pill" style="padding:2px 8px;font-size:11px;background:var(--bg-alt);color:var(--ink)">Staff / Teknisi</span>
                            @endif
                        </td>
                        <td style="padding:14px 16px">
                            <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Aktif</span>
                        </td>
                        <td style="padding:14px 16px;font-size:12px;color:var(--ink-faint)">
                            {{ $staff->created_at->format('d M Y') }}
                        </td>
                        <td style="padding:14px 20px;text-align:right">
                            @if($staff->id !== auth()->id())
                                <form action="{{ route('operasional.staff.destroy', array_merge($ts ? ['tenant_slug' => $ts] : [], ['user' => $staff->id])) }}" method="POST" onsubmit="return confirm('Hapus akses akun staff ini?');" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline" style="padding:4px 10px;font-size:12px;color:#ef4444;border-color:rgba(239,68,68,0.3)">Hapus</button>
                                </form>
                            @else
                                <span style="font-size:12px;color:var(--ink-faint)">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada anggota staff tambahan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add Staff -->
<div id="modal-add-staff" class="modal-overlay" style="display:none;align-items:center;justify-content:center;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999">
    <div class="modal-card" style="background:var(--bg-card);border:1px solid var(--line);border-radius:14px;width:100%;max-width:480px;padding:24px">
        <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:12px">Tambah Anggota Staff / Teknisi</h3>
        <form action="{{ route('operasional.staff.store', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST">
            @csrf
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Nama Lengkap</label>
                <input type="text" name="name" class="form-input" placeholder="Contoh: Budi Santoso" required>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Email Login</label>
                <input type="email" name="email" class="form-input" placeholder="budi@domain.com" required>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Password Awal</label>
                <input type="password" name="password" class="form-input" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="form-field" style="margin-bottom:20px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Peran / Role</label>
                <select name="role" class="form-input" required>
                    <option value="staff">Staff / Teknisi Lapangan (Tiket &amp; Pelanggan)</option>
                    <option value="tenant_admin">Admin Operasional (Billing, Pelanggan &amp; Router)</option>
                </select>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="document.getElementById('modal-add-staff').style.display='none'" class="btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Daftarkan Staff</button>
            </div>
        </form>
    </div>
</div>
@endsection
