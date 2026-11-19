@extends('layouts.app')

@section('title', 'Area & Peta Jaringan')

@section('content')
<div x-data="{ showDelete: false, deleteAction: '', selectedArea: 'all' }">
<x-page-header title="Area & Jangkauan Jaringan" subtitle="{{ $areas->count() }} area terdaftar &bull; {{ $customersWithCoords->count() }} titik pelanggan terpetakan">
    <a href="{{ route('areas.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        Tambah Area
    </a>
</x-page-header>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- PANEL PETA SEBARAN PELANGGAN & AREA -->
<div class="panel reveal d1" style="padding:22px 24px;margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:12px">
        <div>
            <h3 style="font-size:16px;font-weight:700;color:var(--ink);display:flex;align-items:center;gap:8px">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                Peta Sebaran Pelanggan per Area
            </h3>
            <p style="font-size:12.5px;color:var(--ink-soft);margin:2px 0 0">Visualisasi sebaran rumah pelanggan dan konsentrasi jaringan ISP Anda.</p>
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12.5px;color:var(--ink-soft)">Filter Area:</span>
            <select id="area-filter" class="form-input" style="padding:6px 12px;font-size:13px;border-radius:8px">
                <option value="all">Semua Area ({{ $customersWithCoords->count() }} Pelanggan)</option>
                @foreach($areas as $area)
                    <option value="{{ $area->name }}">{{ $area->name }} ({{ $area->customers_count }} Pelanggan)</option>
                @endforeach
            </select>
        </div>
    </div>

    <div style="height:380px;border-radius:12px;overflow:hidden;border:1px solid var(--line);position:relative">
        <div id="area-map" style="height:100%;width:100%"></div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:12px;font-size:12px;color:var(--ink-soft);flex-wrap:wrap;gap:10px">
        <div style="display:flex;align-items:center;gap:16px">
            <span style="display:inline-flex;align-items:center;gap:6px">
                <span style="width:10px;height:10px;border-radius:50%;background:#10B981"></span> Pelanggan Aktif
            </span>
            <span style="display:inline-flex;align-items:center;gap:6px">
                <span style="width:10px;height:10px;border-radius:50%;background:#EF4444"></span> Isolir
            </span>
            <span style="display:inline-flex;align-items:center;gap:6px">
                <span style="width:10px;height:10px;border-radius:50%;background:#6B7280"></span> Suspend
            </span>
        </div>
        <div>
            Klik pada titik marker untuk melihat nama pelanggan, paket, dan tautan detail.
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px">
    @forelse($areas as $area)
    <div style="background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:24px">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
            <div style="width:48px;height:48px;border-radius:12px;background:var(--primary-tint);display:flex;align-items:center;justify-content:center;color:var(--primary-dark)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <span style="font-size:12px;font-weight:600;padding:4px 10px;border-radius:12px;background:var(--bg);border:1px solid var(--line);color:var(--primary)">
                {{ $area->customers_count }} Pelanggan
            </span>
        </div>

        <h3 style="font-size:18px;font-weight:600;color:var(--ink);margin-bottom:6px">{{ $area->name }}</h3>
        @if($area->description)
        <p style="font-size:14px;color:var(--ink-soft);margin-bottom:16px;line-height:1.5">{{ $area->description }}</p>
        @else
        <p style="font-size:14px;color:var(--ink-faint);margin-bottom:16px">Tidak ada deskripsi</p>
        @endif

        <div style="display:flex;gap:10px;padding-top:18px;border-top:1px solid var(--line)">
            <a href="{{ route('areas.edit', $area->id) }}" class="btn btn-ghost" style="flex:1;justify-content:center;color:var(--primary)">Edit</a>
            <button type="button" @click="deleteAction = '{{ route('areas.destroy', $area->id) }}'; showDelete = true" class="btn btn-ghost" style="flex:1;justify-content:center;color:var(--red)">Hapus</button>
        </div>
    </div>
    @empty
    <div style="grid-column:1/-1;display:flex;justify-content:center;padding:60px 20px">
        <x-empty-state title="Belum ada area" description="Buat area untuk mengelompokkan pelanggan berdasarkan lokasi.">
            <x-slot:icon>
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color:var(--ink-faint)"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </x-slot:icon>
            <a href="{{ route('areas.create') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                Buat Area Pertama
            </a>
        </x-empty-state>
    </div>
    @endforelse
</div>

<div x-show="showDelete" x-cloak class="modal-overlay" @click.self="showDelete = false" style="display:none">
    <div class="modal" @click.stop>
        <div class="modal-header">
            <h3 style="font-family:'Space Grotesk';font-size:18px;font-weight:700;color:var(--ink)">Hapus Area</h3>
            <p style="font-size:13px;color:var(--ink-faint);margin-top:4px">Tindakan ini tidak dapat dibatalkan.</p>
        </div>
        <div class="modal-body">
            <p style="font-size:14px;color:var(--ink-soft);margin-bottom:20px">Yakin ingin menghapus area ini?</p>
            <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" @click="showDelete = false" class="btn-outline" style="padding:10px 20px">Batal</button>
                <form :action="deleteAction" method="POST" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-save" style="width:auto;padding:10px 20px;margin-top:0;background:var(--red)">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .leaflet-popup-content-wrapper {
        background: var(--panel, #1e293b);
        color: var(--ink, #fff);
        border: 1px solid var(--line, rgba(255,255,255,0.1));
        border-radius: 8px;
    }
    .leaflet-popup-tip {
        background: var(--panel, #1e293b);
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var customers = @json($customersWithCoords);
        var mapEl = document.getElementById('area-map');
        if (!mapEl) return;

        var defaultLat = -7.7956;
        var defaultLng = 110.3695;
        var defaultZoom = 13;

        if (customers.length > 0) {
            defaultLat = parseFloat(customers[0].latitude);
            defaultLng = parseFloat(customers[0].longitude);
        }

        var map = L.map('area-map').setView([defaultLat, defaultLng], defaultZoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var markersGroup = L.featureGroup().addTo(map);

        function renderMarkers(selectedArea) {
            markersGroup.clearLayers();

            var filtered = (selectedArea === 'all')
                ? customers
                : customers.filter(c => c.area === selectedArea);

            filtered.forEach(function (c) {
                var lat = parseFloat(c.latitude);
                var lng = parseFloat(c.longitude);
                if (isNaN(lat) || isNaN(lng)) return;

                var color = '#10B981';
                if (c.status === 'isolated') color = '#EF4444';
                if (c.status === 'suspended') color = '#6B7280';

                var markerHtml = '<div style="width:16px;height:16px;border-radius:50%;background:' + color + ';border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,0.4)"></div>';
                var customIcon = L.divIcon({
                    html: markerHtml,
                    className: '',
                    iconSize: [16, 16],
                    iconAnchor: [8, 8]
                });

                var popupContent = '<div style="min-width:180px;font-size:12.5px">'
                    + '<div style="font-weight:700;font-size:13.5px;margin-bottom:4px">' + c.name + '</div>'
                    + '<div style="color:#94a3b8;margin-bottom:2px">Area: <b>' + (c.area || '-') + '</b></div>'
                    + '<div style="color:#94a3b8;margin-bottom:2px">Paket: <b>' + (c.package ? c.package.name : '-') + '</b></div>'
                    + '<div style="color:#94a3b8;margin-bottom:6px">Status: <span style="font-weight:600;color:' + color + '">' + c.status.toUpperCase() + '</span></div>'
                    + '<a href="/customers/' + c.id + '" style="display:inline-block;padding:4px 8px;background:#2563EB;color:#fff;border-radius:4px;text-decoration:none;font-size:11.5px">Lihat Detail Pelanggan →</a>'
                    + '</div>';

                var m = L.marker([lat, lng], { icon: customIcon }).bindPopup(popupContent);
                markersGroup.addLayer(m);
            });

            if (markersGroup.getLayers().length > 0) {
                map.fitBounds(markersGroup.getBounds().pad(0.15));
            }
        }

        renderMarkers('all');

        document.getElementById('area-filter')?.addEventListener('change', function () {
            renderMarkers(this.value);
        });
    });
</script>
@endpush
@endsection
