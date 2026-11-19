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

@section('title', 'Manajemen OLT & ONU')

@section('content')
<div class="breadcrumb reveal">
    <a href="{{ route('tenant.dashboard', $ts ? ['tenant_slug' => $ts] : []) }}" style="color:inherit;text-decoration:none">Home</a> &gt; <span>Router</span> &gt; <b>OLT</b>
</div>

<x-page-header title="Manajemen OLT (Optical Line Terminal)" subtitle="Kelola perangkat OLT (Fiberhome, VSOL, Huawei, ZTE) dan provisioning modem ONT/ONU pelanggan">
    <button onclick="document.getElementById('modal-add-olt').style.display='flex'" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah OLT
    </button>
</x-page-header>

@if(session('success'))
    <div style="background:var(--green-tint);border:1px solid var(--green);color:var(--green);padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Total Perangkat OLT</div>
        <div style="font-size:28px;font-weight:700;color:var(--ink);margin-top:6px">{{ $devices->count() }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Unit</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Fiberhome, VSOL, Huawei, ZTE</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">Total ONU / ONT Terpasang</div>
        <div style="font-size:28px;font-weight:700;color:var(--primary);margin-top:6px">{{ $totalOnus }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Modem</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Terdaftar di port PON OLT</div>
    </div>
    <div class="card stat-card" style="padding:20px;background:var(--bg-card);border:1px solid var(--line);border-radius:12px">
        <div style="font-size:12px;font-weight:600;color:var(--ink-faint);text-transform:uppercase;letter-spacing:.05em">ONU Aktif / Online</div>
        <div style="font-size:28px;font-weight:700;color:var(--green);margin-top:6px">{{ $activeOnus }} <span style="font-size:14px;font-weight:400;color:var(--ink-faint)">Online</span></div>
        <div style="font-size:12px;color:var(--ink-soft);margin-top:6px">Optical signal &amp; link normal</div>
    </div>
</div>

<!-- OLT Devices List -->
<div class="panel" style="background:var(--bg-card);border:1px solid var(--line);border-radius:12px;overflow:hidden;margin-bottom:24px">
    <div style="padding:18px 24px;border-bottom:1px solid var(--line)">
        <h3 style="font-size:16px;font-weight:700;color:var(--ink);margin:0">Daftar OLT Terdaftar</h3>
    </div>
    <div style="overflow-x:auto">
        <table class="data-table" style="width:100%;border-collapse:collapse;text-align:left;font-size:13.5px">
            <thead>
                <tr style="border-bottom:1px solid var(--line);background:var(--bg-alt);color:var(--ink-soft);font-size:12px;text-transform:uppercase">
                    <th style="padding:14px 20px">Nama OLT</th>
                    <th style="padding:14px 16px">Merk &amp; Tipe</th>
                    <th style="padding:14px 16px">IP Management</th>
                    <th style="padding:14px 16px">Jumlah ONU</th>
                    <th style="padding:14px 16px">Status</th>
                    <th style="padding:14px 20px;text-align:right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $dev)
                    <tr style="border-bottom:1px solid var(--line)">
                        <td style="padding:14px 20px;font-weight:600;color:var(--ink)">{{ $dev->name }}</td>
                        <td style="padding:14px 16px;text-transform:uppercase;font-size:12px;font-weight:600;color:var(--primary)">
                            {{ $dev->brand }}
                        </td>
                        <td style="padding:14px 16px;font-family:'JetBrains Mono';font-size:13px;color:var(--ink-soft)">
                            {{ $dev->management_host }}:{{ $dev->ssh_port }}
                        </td>
                        <td style="padding:14px 16px">
                            <span style="font-weight:600;color:var(--ink)">{{ $dev->onus->count() }}</span> ONU
                        </td>
                        <td style="padding:14px 16px">
                            <span class="status-pill ok" style="padding:2px 8px;font-size:11px"><span class="dot" style="background:var(--primary)"></span>Online</span>
                        </td>
                        <td style="padding:14px 20px;text-align:right">
                            <button onclick="openAddOnuModal({{ $dev->id }}, '{{ $dev->name }}')" class="btn-outline" style="padding:5px 10px;font-size:12px">Register ONU</button>
                            <form action="{{ route('devices.olt.destroy', array_merge($ts ? ['tenant_slug' => $ts] : [], ['device' => $dev->id])) }}" method="POST" onsubmit="return confirm('Hapus OLT ini?');" style="display:inline;margin-left:6px">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline" style="padding:5px 10px;font-size:12px;color:#ef4444;border-color:rgba(239,68,68,0.3)">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:32px;text-align:center;color:var(--ink-faint)">
                            Belum ada perangkat OLT yang didaftarkan. Klik tombol "Tambah OLT" di atas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Add OLT -->
<div id="modal-add-olt" class="modal-overlay" style="display:none;align-items:center;justify-content:center;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999">
    <div class="modal-card" style="background:var(--bg-card);border:1px solid var(--line);border-radius:14px;width:100%;max-width:500px;padding:24px">
        <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:12px">Tambah Perangkat OLT Baru</h3>
        <form action="{{ route('devices.olt.store', $ts ? ['tenant_slug' => $ts] : []) }}" method="POST">
            @csrf
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Nama OLT</label>
                <input type="text" name="name" class="form-input" placeholder="Contoh: OLT-Sentral-GPON-01" required>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Merk / Brand OLT</label>
                <select name="brand" class="form-input" required>
                    <option value="fiberhome">Fiberhome (AN5516 / AN5116)</option>
                    <option value="vsol">VSOL (V1600D / EPON / GPON)</option>
                    <option value="huawei">Huawei (MA5608T / MA5683T)</option>
                    <option value="zte">ZTE (C300 / C320)</option>
                </select>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">IP Management OLT</label>
                <input type="text" name="management_host" class="form-input mono" placeholder="192.168.1.100 atau IP Public" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-field">
                    <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Username CLI/Telnet</label>
                    <input type="text" name="username" class="form-input" placeholder="admin">
                </div>
                <div class="form-field">
                    <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Password</label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••">
                </div>
            </div>
            <div class="form-field" style="margin-bottom:20px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Port Telnet / SSH</label>
                <input type="number" name="ssh_port" class="form-input mono" value="22" placeholder="22 atau 23">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="document.getElementById('modal-add-olt').style.display='none'" class="btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan OLT</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add ONU -->
<div id="modal-add-onu" class="modal-overlay" style="display:none;align-items:center;justify-content:center;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:9999">
    <div class="modal-card" style="background:var(--bg-card);border:1px solid var(--line);border-radius:14px;width:100%;max-width:500px;padding:24px">
        <h3 style="font-size:18px;font-weight:700;color:var(--ink);margin-bottom:4px">Register ONU Baru</h3>
        <p id="olt-target-name" style="font-size:13px;color:var(--primary);margin-bottom:16px"></p>
        <form id="form-add-onu" method="POST">
            @csrf
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Nama Pelanggan / Lokasi</label>
                <input type="text" name="name" class="form-input" placeholder="Contoh: Bpk. Ahmad (Rumah 12)" required>
            </div>
            <div class="form-field" style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">Serial Number (SN) / MAC</label>
                <input type="text" name="serial" class="form-input mono" placeholder="ZTEGC1234567 / FHTT123456" required>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div class="form-field">
                    <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">PON Port</label>
                    <input type="text" name="pon_id" class="form-input mono" placeholder="EPON0/1 atau 1/1/1" required>
                </div>
                <div class="form-field">
                    <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">ONU ID</label>
                    <input type="number" name="onu_id" class="form-input mono" placeholder="1 - 128" required>
                </div>
            </div>
            <div class="form-field" style="margin-bottom:20px">
                <label style="font-size:12px;font-weight:600;color:var(--ink-soft);display:block;margin-bottom:4px">VLAN ID Pelanggan</label>
                <input type="number" name="vlan" class="form-input mono" placeholder="100 - 4000">
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px">
                <button type="button" onclick="document.getElementById('modal-add-onu').style.display='none'" class="btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary">Registrasikan ONU</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddOnuModal(oltId, oltName) {
    document.getElementById('olt-target-name').innerText = 'Target OLT: ' + oltName;
    var form = document.getElementById('form-add-onu');
    form.action = "{{ url('devices/olt') }}/" + oltId + "/onu";
    document.getElementById('modal-add-onu').style.display = 'flex';
}
</script>
@endsection
