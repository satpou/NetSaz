@extends('layouts.app')

@section('title', 'Edit Pelanggan')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">Edit Pelanggan</h1>
        <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">{{ $customer->name }}</p>
    </div>
</div>

<div class="panel" style="max-width:720px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('customers.update', $customer->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div style="margin-bottom:20px">
                <label class="form-label">Nama Lengkap <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="form-input" placeholder="Nama pelanggan">
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Email <span style="color:var(--red)">*</span></label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}" required class="form-input" placeholder="email@example.com">
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="form-input" placeholder="08xxxxxxxxxx">
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">No. KTP</label>
                <input type="text" name="ktp_id" value="{{ old('ktp_id', $customer->ktp_id) }}" class="form-input" placeholder="Nomor KTP">
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Alamat <span style="color:var(--red)">*</span></label>
                <textarea name="address" rows="3" required class="form-input" placeholder="Alamat lengkap">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                <div>
                    <label class="form-label">Area <span style="color:var(--red)">*</span></label>
                    <select name="area" required class="form-input">
                        <option value="">Pilih Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a }}" {{ old('area', $customer->area) == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex;align-items:flex-end">
                    <p style="font-size:12px;color:var(--ink-faint)">Area belum tersedia? <a href="{{ route('areas.create') }}" style="color:var(--primary)">Tambah area baru</a></p>
                </div>
            </div>

            <div style="margin-bottom:24px">
                <label class="form-label">Titik Koordinat Pemasangan</label>
                <p style="font-size:12px;color:var(--ink-faint);margin-bottom:10px">Geser pin ke lokasi pemasangan pelanggan</p>
                <div style="height:320px;border-radius:12px;overflow:hidden;border:1px solid var(--line);position:relative" id="map-container">
                    <div id="map" style="height:100%;width:100%"></div>
                </div>
                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $customer->latitude) }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $customer->longitude) }}">
                <div style="display:flex;gap:16px;margin-top:10px">
                    <span style="font-size:13px;color:var(--ink-soft)">Lat: <span id="lat-display">{{ old('latitude', $customer->latitude) ?? '-' }}</span></span>
                    <span style="font-size:13px;color:var(--ink-soft)">Lng: <span id="lng-display">{{ old('longitude', $customer->longitude) ?? '-' }}</span></span>
                </div>
            </div>

            @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
            @endpush

            @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var existingLat = '{{ old("latitude", $customer->latitude) }}';
                    var existingLng = '{{ old("longitude", $customer->longitude) }}';

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

                    if (existingLat && existingLng) {
                        var lat = parseFloat(existingLat);
                        var lng = parseFloat(existingLng);
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
                });
            </script>
            @endpush

            <div style="margin-bottom:20px">
                <label class="form-label">Paket Langganan <span style="color:var(--red)">*</span></label>
                <select name="package_id" required class="form-input">
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ old('package_id', $customer->package_id) == $package->id ? 'selected' : '' }}>
                            {{ $package->name }} - Rp{{ number_format($package->price, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Tanggal Bergabung <span style="color:var(--red)">*</span></label>
                <input type="date" name="join_date" value="{{ old('join_date', $customer->join_date->format('Y-m-d')) }}" required class="form-input">
            </div>

            <div style="margin-bottom:24px">
                <label class="form-label">Status <span style="color:var(--red)">*</span></label>
                <select name="status" required class="form-input">
                    <option value="active" {{ old('status', $customer->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="isolated" {{ old('status', $customer->status) == 'isolated' ? 'selected' : '' }}>Isolir</option>
                    <option value="suspended" {{ old('status', $customer->status) == 'suspended' ? 'selected' : '' }}>Suspend</option>
                </select>
            </div>

            <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding:14px 16px;background:var(--bg-alt);border-radius:10px">
                <div style="flex:0 0 auto">
                    <label class="form-label" style="margin-bottom:0">Hari Tagihan Bulanan</label>
                </div>
                <div style="flex:0 0 auto">
                    <input type="number" name="billing_cycle_day" value="{{ old('billing_cycle_day', $customer->billing_cycle_day ?? 1) }}" min="1" max="28" class="form-input" style="width:80px;text-align:center;font-family:JetBrains Mono">
                </div>
                <span style="font-size:12px;color:var(--ink-faint)">Tanggal dalam bulan (1-28) saat invoice otomatis di-generate.</span>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Update Pelanggan</button>
                <a href="{{ route('customers.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection