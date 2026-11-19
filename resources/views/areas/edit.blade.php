@extends('layouts.app')

@section('title', 'Edit Area')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #area-map-picker {
        height: 320px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid var(--line);
        margin-top: 8px;
        z-index: 1;
    }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:16px">
    <div>
        <h1 style="font-size:26px;font-weight:700;color:var(--ink)">Edit Area</h1>
        <p style="font-size:14px;color:var(--ink-soft);margin-top:4px">{{ $area->name }}</p>
    </div>
</div>

<div class="panel" style="max-width:760px">
    <div class="panel-body">
        <x-errors />

        <form action="{{ route('areas.update', $area->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="margin-bottom:20px">
                <label class="form-label">Nama Area <span style="color:var(--red)">*</span></label>
                <input type="text" name="name" value="{{ old('name', $area->name) }}" required class="form-input" placeholder="Cth: Perumahan Indah">
            </div>

            <div style="margin-bottom:20px">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="2" class="form-input" placeholder="Keterangan area (opsional)">{{ old('description', $area->description) }}</textarea>
            </div>

            <!-- Interactive Map Picker for Area Center -->
            <div style="margin-bottom:24px">
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:6px">
                    <label class="form-label" style="margin-bottom:0">
                        Titik Pusat Area pada Peta
                    </label>
                    <button type="button" id="btn-gps" class="btn btn-outline" style="font-size:12px;padding:5px 12px;display:inline-flex;align-items:center;gap:6px">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
                        <span>Gunakan Lokasi GPS Saya</span>
                    </button>
                </div>
                <p style="font-size:12.5px;color:var(--ink-soft);margin-bottom:8px">
                    Klik pada peta atau geser pin untuk menentukan titik pusat area ini. Koordinat akan terisi secara otomatis.
                </p>

                <div id="area-map-picker"></div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:12px">
                    <div>
                        <label class="form-label" style="font-size:12px">Latitude</label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $area->latitude) }}" class="form-input mono" placeholder="-7.7956000" style="font-size:13px">
                    </div>
                    <div>
                        <label class="form-label" style="font-size:12px">Longitude</label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $area->longitude) }}" class="form-input mono" placeholder="110.3695000" style="font-size:13px">
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:12px">
                <button type="submit" class="btn btn-primary">Update Area</button>
                <a href="{{ route('areas.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var currentLat = {{ old('latitude', $area->latitude) ?? 'null' }};
        var currentLng = {{ old('longitude', $area->longitude) ?? 'null' }};

        var mapEl = document.getElementById('area-map-picker');
        if (!mapEl) return;

        var hasCoords = (currentLat !== null && currentLng !== null);
        var initialLat = hasCoords ? parseFloat(currentLat) : -7.7956;
        var initialLng = hasCoords ? parseFloat(currentLng) : 110.3695;

        var map = L.map('area-map-picker').setView([initialLat, initialLng], hasCoords ? 15 : 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

        function updateCoords(lat, lng) {
            document.getElementById('latitude').value = lat.toFixed(7);
            document.getElementById('longitude').value = lng.toFixed(7);
        }

        if (hasCoords) {
            updateCoords(initialLat, initialLng);
        }

        marker.on('dragend', function (e) {
            var pos = e.target.getLatLng();
            updateCoords(pos.lat, pos.lng);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng);
        });

        function locateUser(zoom = 15, isManual = false) {
            if (!navigator.geolocation) {
                if (isManual && window.NetSazModal) {
                    window.NetSazModal.alert({ title: 'GPS', message: 'Browser tidak mendukung geolokasi GPS.', type: 'warning' });
                }
                return;
            }

            var btn = document.getElementById('btn-gps');
            if (isManual && btn) {
                btn.disabled = true;
                btn.innerHTML = 'Mencari sinyal GPS...';
            }

            navigator.geolocation.getCurrentPosition(function (pos) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg><span>Gunakan Lokasi GPS Saya</span>';
                }

                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;

                map.setView([lat, lng], zoom);
                marker.setLatLng([lat, lng]);
                updateCoords(lat, lng);
            }, function (err) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg><span>Gunakan Lokasi GPS Saya</span>';
                }
                if (isManual && window.NetSazModal) {
                    window.NetSazModal.alert({
                        title: 'Gagal Mengambil Lokasi',
                        message: 'Tidak dapat mendeteksi lokasi GPS Anda: ' + err.message,
                        type: 'warning'
                    });
                }
            }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 60000 });
        }

        // AUTO-LOCATE if area did not have coordinates saved
        if (!hasCoords) {
            locateUser(15, false);
        }

        document.getElementById('btn-gps')?.addEventListener('click', function () {
            locateUser(16, true);
        });

        setTimeout(function () {
            map.invalidateSize();
        }, 300);
    });
</script>
@endpush
