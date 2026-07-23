@extends('core::layouts.master')

@section('title', 'Mapa da Rede FTTH')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: calc(100vh - 220px); min-height: 500px; border-radius: 12px; z-index: 0; }
    .leaflet-popup-content { font-size: 13px; line-height: 1.5; }
    .leaflet-popup-content b { color: #1f2937; }
    .marker-cto { background: #ef4444; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }
    .marker-caixa { background: #22c55e; width: 14px; height: 14px; border-radius: 3px; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }
    .marker-inactive { opacity: 0.4; }
</style>
@endpush

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Mapa da Rede FTTH</h1>
        <p class="text-gray-500 text-sm">Visualizacao geografica de CTOs e Caixas de Emenda</p>
    </div>
    <div class="flex items-center gap-3">
        <select id="cityFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Todas Cidades</option>
            @foreach($cities as $city)
                <option value="{{ $city }}">{{ $city }}</option>
            @endforeach
        </select>
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-red-500 border border-white shadow"></span> CTO</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-green-500 border border-white shadow"></span> Caixa</span>
            <span id="counter" class="font-medium text-gray-700"></span>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div id="map"></div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    const map = L.map('map', { zoomControl: true }).setView([-4.3, -46.5], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    }).addTo(map);

    let ctoLayer = L.layerGroup().addTo(map);
    let caixaLayer = L.layerGroup().addTo(map);

    function statusColor(status) {
        return status === 'active' ? '#22c55e' : status === 'maintenance' ? '#eab308' : '#9ca3af';
    }

    function loadMapData() {
        const city = document.getElementById('cityFilter').value;
        const url = '{{ route("ftth.api.map-data") }}' + (city ? '?city=' + encodeURIComponent(city) : '');

        fetch(url)
            .then(r => r.json())
            .then(data => {
                ctoLayer.clearLayers();
                caixaLayer.clearLayers();

                let bounds = [];
                let ctoCount = 0;
                let caixaCount = 0;

                data.caixas.forEach(c => {
                    if (!c.lat || !c.lng) return;
                    const icon = L.divIcon({
                        className: 'marker-caixa' + (c.status !== 'active' ? ' marker-inactive' : ''),
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    });
                    const popup = `<div style="min-width:180px">
                        <b style="color:#16a34a">${c.code}</b><br>
                        <b>Nome:</b> ${c.name}<br>
                        <b>Rua:</b> ${c.street || '-'}<br>
                        <b>Cidade:</b> ${c.city || '-'}<br>
                        <b>Capacidade:</b> ${c.used}/${c.capacity}<br>
                        <b>Status:</b> ${c.status}<br>
                        <a href="/ftth/caixas/${c.id}" style="color:#2563eb">Ver detalhes</a>
                    </div>`;
                    L.marker([c.lat, c.lng], { icon }).bindPopup(popup).addTo(caixaLayer);
                    bounds.push([c.lat, c.lng]);
                    caixaCount++;
                });

                data.ctos.forEach(c => {
                    if (!c.lat || !c.lng) return;
                    const icon = L.divIcon({
                        className: 'marker-cto' + (c.status !== 'active' ? ' marker-inactive' : ''),
                        iconSize: [12, 12],
                        iconAnchor: [6, 6]
                    });
                    const popup = `<div style="min-width:180px">
                        <b style="color:#dc2626">${c.code}</b><br>
                        <b>Nome:</b> ${c.name}<br>
                        <b>Rua:</b> ${c.street || '-'}<br>
                        <b>Cidade:</b> ${c.city || '-'}<br>
                        <b>Capacidade:</b> ${c.used}/${c.capacity}<br>
                        <b>Status:</b> ${c.status}<br>
                        <a href="/ftth/ctos/${c.id}" style="color:#2563eb">Ver detalhes</a>
                    </div>`;
                    L.marker([c.lat, c.lng], { icon }).bindPopup(popup).addTo(ctoLayer);
                    bounds.push([c.lat, c.lng]);
                    ctoCount++;
                });

                document.getElementById('counter').textContent = caixaCount + ' Caixas | ' + ctoCount + ' CTOs';

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [30, 30] });
                }
            });
    }

    document.getElementById('cityFilter').addEventListener('change', loadMapData);
    loadMapData();
})();
</script>
@endpush
@endsection
