@extends('layouts.app')

@section('title', 'Tambah Pelanggan')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">Tambah Pelanggan Baru</h1>
        <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">Isi data pelanggan dengan lengkap</p>
    </div>
</div>

<div class="panel" style="max-width:720px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div>
                    <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Nama pelanggan">
                </div>
                <div>
                    <label class="form-label">Email <span style="color:var(--red)">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="email@example.com">
                </div>
                <div>
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label class="form-label">No. KTP</label>
                    <input type="text" name="ktp_id" value="{{ old('ktp_id') }}" class="form-input" placeholder="Nomor KTP">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div>
                    <label class="form-label">Upload Scan KTP <span style="color:var(--ink-faint);font-weight:400">(opsional)</span></label>
                    <input type="file" name="ktp_image" accept="image/jpeg,image/png" class="form-input" style="padding:10px 14px">
                    <p style="font-size:12px;color:var(--ink-faint);margin-top:6px">Format JPG/PNG, maksimal 2MB</p>
                </div>
                <div>
                    <label class="form-label">Foto Rumah Pelanggan <span style="color:var(--ink-faint);font-weight:400">(opsional)</span></label>
                    <input type="file" name="house_image" accept="image/jpeg,image/png,image/webp" class="form-input" style="padding:10px 14px">
                    <p style="font-size:12px;color:var(--ink-faint);margin-top:6px">Tampak depan rumah / tiang ODP (maks. 5MB)</p>
                </div>
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Alamat <span style="color:var(--red)">*</span></label>
                <textarea name="address" rows="3" required class="form-input" placeholder="Alamat lengkap">{{ old('address') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div>
                    <label class="form-label">Area <span style="color:var(--red)">*</span></label>
                    <select name="area" required class="form-input">
                        <option value="">Pilih Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a }}" {{ old('area') == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end">
                    <p style="font-size:12px;color:var(--ink-faint)">Area belum tersedia? <a href="{{ route('areas.create') }}" style="color:var(--primary)">Tambah area baru</a></p>
                </div>
            </div>

            <div style="margin-bottom:24px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                    <div>
                        <label class="form-label" style="margin-bottom:2px">Titik Koordinat Pemasangan</label>
                        <p style="font-size:12px;color:var(--ink-faint);margin:0">Klik peta atau geser pin ke lokasi rumah pelanggan</p>
                    </div>
                    <button type="button" id="btn-gps" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px;display:inline-flex;align-items:center;gap:6px">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                        <span>Gunakan Lokasi GPS Saya</span>
                    </button>
                </div>
                <div style="height:320px;border-radius:12px;overflow:hidden;border:1px solid var(--line);position:relative" id="map-container">
                    <div id="map" style="height:100%;width:100%"></div>
                </div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px">
                    <div style="display:flex;gap:16px">
                        <span style="font-size:13px;color:var(--ink-soft)">Lat: <span id="lat-display" class="mono">{{ old('latitude') ?? '-' }}</span></span>
                        <span style="font-size:13px;color:var(--ink-soft)">Lng: <span id="lng-display" class="mono">{{ old('longitude') ?? '-' }}</span></span>
                    </div>
                </div>
            </div>

            @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            @endpush

            @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var hasOldLat = '{{ old("latitude") }}';
                    var hasOldLng = '{{ old("longitude") }}';

                    var map = L.map('map');
                    var marker;

                    function initMap(lat, lng, zoom) {
                        map.setView([lat, lng], zoom);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);
                        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                        updateCoord(lat, lng);

                        marker.on('dragend', function (e) {
                            var pos = e.target.getLatLng();
                            updateCoord(pos.lat, pos.lng);
                        });
                        map.on('click', function (e) {
                            marker.setLatLng(e.latlng);
                            updateCoord(e.latlng.lat, e.latlng.lng);
                        });
                    }

                    function updateCoord(lat, lng) {
                        document.getElementById('latitude').value = lat.toFixed(7);
                        document.getElementById('longitude').value = lng.toFixed(7);
                        document.getElementById('lat-display').textContent = lat.toFixed(7);
                        document.getElementById('lng-display').textContent = lng.toFixed(7);
                    }

                    if (hasOldLat && hasOldLng) {
                        var lat = parseFloat(hasOldLat);
                        var lng = parseFloat(hasOldLng);
                        initMap(lat, lng, 15);
                    } else if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(function (pos) {
                            initMap(pos.coords.latitude, pos.coords.longitude, 15);
                        }, function () {
                            initMap(-7.7956, 110.3695, 13);
                        }, { enableHighAccuracy: true, timeout: 10000 });
                    } else {
                        initMap(-7.7956, 110.3695, 13);
                    }

                    document.getElementById('btn-gps')?.addEventListener('click', function () {
                        if (navigator.geolocation) {
                            var btn = this;
                            btn.disabled = true;
                            btn.innerHTML = 'Mencari sinyal GPS...';
                            navigator.geolocation.getCurrentPosition(function (pos) {
                                btn.disabled = false;
                                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg><span>Gunakan Lokasi GPS Saya</span>';
                                var lat = pos.coords.latitude;
                                var lng = pos.coords.longitude;
                                map.setView([lat, lng], 17);
                                if (marker) {
                                    marker.setLatLng([lat, lng]);
                                } else {
                                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                                    marker.on('dragend', function (e) {
                                        var p = e.target.getLatLng();
                                        updateCoord(p.lat, p.lng);
                                    });
                                }
                                updateCoord(lat, lng);
                            }, function (err) {
                                btn.disabled = false;
                                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg><span>Gunakan Lokasi GPS Saya</span>';
                                alert('Tidak dapat mengambil lokasi GPS: ' + err.message);
                            }, { enableHighAccuracy: true, timeout: 10000 });
                        } else {
                            alert('Browser Anda tidak mendukung geolokasi GPS.');
                        }
                    });
                });
            </script>
            @endpush

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px">
                <div>
                    <label class="form-label">Paket Langganan <span style="color:var(--red)">*</span></label>
                    <select name="package_id" required class="form-input">
                        <option value="">Pilih Paket</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} - Rp{{ number_format($package->price, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Tanggal Bergabung <span style="color:var(--red)">*</span></label>
                    <input type="date" name="join_date" value="{{ old('join_date', date('Y-m-d')) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Status Awal <span style="color:var(--red)">*</span></label>
                    <select name="status" required class="form-input">
                        <option value="active" selected>Aktif</option>
                        <option value="isolated">Isolir</option>
                        <option value="suspended">Suspend</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding:14px 16px;background:var(--bg-alt);border-radius:10px">
                <div style="flex:0 0 auto">
                    <label class="form-label" style="margin-bottom:0">Hari Tagihan Bulanan</label>
                </div>
                <div style="flex:0 0 auto">
                    <input type="number" name="billing_cycle_day" value="{{ old('billing_cycle_day', 1) }}" min="1" max="28" class="form-input" style="width:80px;text-align:center;font-family:JetBrains Mono">
                </div>
                <span style="font-size:12px;color:var(--ink-faint)">Tanggal dalam bulan (1-28) saat invoice otomatis di-generate. Default: 1.</span>
            </div>

            <div style="margin-bottom:24px;padding:16px;border:1px solid var(--line);border-radius:10px">
                <label class="form-label" style="margin-bottom:12px">Akun PPPoE <span style="color:var(--ink-faint);font-weight:400">(opsional — langsung diproses ke router)</span></label>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px">
                    <div>
                        <label class="form-label">Username</label>
                        <input type="text" name="pppoe_username" value="{{ old('pppoe_username') }}" class="form-input" placeholder="cth: cust001">
                    </div>
                    <div>
                        <label class="form-label">Password</label>
                        <input type="password" name="pppoe_password" value="{{ old('pppoe_password') }}" class="form-input" placeholder="Password PPPoE">
                    </div>
                    <div>
                        <label class="form-label">Service</label>
                        <select name="pppoe_service" class="form-input">
                            <option value="pppoe" {{ old('pppoe_service', 'pppoe') === 'pppoe' ? 'selected' : '' }}>pppoe</option>
                            <option value="pppoeoe" {{ old('pppoe_service') === 'pppoeoe' ? 'selected' : '' }}>pppoeoe</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection