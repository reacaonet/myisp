@extends('infra::layouts.master')

@section('title', 'Mapa da Rede FTTH')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 600px; border-radius: 12px; z-index: 0; }
    .leaflet-popup-content { font-size: 13px; line-height: 1.5; }
    .leaflet-popup-content b { color: #1f2937; }
    .marker-cto { width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }
    .marker-caixa { background: #22c55e; width: 14px; height: 14px; border-radius: 3px; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }

    /* Nós ainda não lançados ficam translúcidos no mapa: são o plano, não a
       rede construída. Assim que o técnico ativa, o marcador entra com cor
       cheia e ganha um anel pulsante que destaca o que já foi construído. */
    .marker-dim { opacity: 0.3; }
    .marker-active { z-index: 500; }
    .marker-active::after {
        content: '';
        position: absolute;
        inset: -7px;
        border-radius: 50%;
        border: 2px solid rgba(34,197,94,.55);
        animation: marker-pulse 2s ease-out infinite;
    }
    .marker-caixa.marker-active { border-radius: 50% 50% 50% 3px; }
    .marker-caixa.marker-active::after { border-radius: 50% 50% 50% 3px; }
    @keyframes marker-pulse {
        0%   { transform: scale(.7); opacity: .9; }
        70%  { transform: scale(1.25); opacity: 0; }
        100% { transform: scale(1.25); opacity: 0; }
    }
</style>
@endpush

@section('content')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Mapa da Rede FTTH</h1>
        <p class="text-gray-500 text-sm">Visualizacao geografica de CTOs e Caixas de Emenda</p>
        <p class="text-gray-400 text-xs mt-1">Marcador translúcido = planejado, ainda sem fibra lançada. Marcador com cor cheia e anel = ativo.</p>
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
    const caixaUrl = '{{ route("infra.ftth.caixas.show", "__ID__") }}';
    const ctoUrl = '{{ route("infra.ftth.ctos.show", "__ID__") }}';

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    });
    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri',
        maxZoom: 19,
        maxNativeZoom: 17
    });
    const terrainLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data: &copy; OpenStreetMap, SRTM | Style: &copy; OpenTopoMap',
        maxZoom: 17,
        maxNativeZoom: 17
    });
    osmLayer.addTo(map);
    L.control.layers({
        'Padrao': osmLayer,
        'Satelite': satelliteLayer,
        'Terreno': terrainLayer
    }, null, { position: 'topright' }).addTo(map);

    let ctoLayer = L.layerGroup().addTo(map);
    let caixaLayer = L.layerGroup().addTo(map);

    function statusLabel(status) {
        if (status === 'active') return '<b style="color:#16a34a">ativa</b>';
        if (status === 'maintenance') return '<b style="color:#b45309">em manuten&ccedil;&atilde;o</b>';
        return '<span style="color:#6b7280">planejada (sem fibra lançada)</span>';
    }

    // Só a rede construida recebe cor cheia. planned e maintenance continuam
    // translucidos para o tecnico enxergar o que ainda falta.
    function makeIcon(baseClass, color, status, size) {
        const active = status === 'active';
        const html = '<span style="display:block;width:' + size + 'px;height:' + size + 'px;' +
            (baseClass === 'marker-caixa' ? 'border-radius:3px;' : 'border-radius:50%;') +
            'border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);background:' + color + '"></span>';

        return L.divIcon({
            className: baseClass + (active ? ' marker-active' : ' marker-dim'),
            html: html,
            iconSize: [size, size],
            iconAnchor: [size / 2, size / 2]
        });
    }

    const CTO_FALLBACK_COLOR = '#ef4444';

    function loadMapData() {
        const city = document.getElementById('cityFilter').value;
        const url = '{{ route("infra.ftth.api.map-data") }}' + (city ? '?city=' + encodeURIComponent(city) : '');

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
                    const icon = makeIcon('marker-caixa', '#22c55e', c.status, 14);
                    const popup = `<div style="min-width:180px">
                        <b style="color:#16a34a">${c.code}</b><br>
                        <b>Nome:</b> ${c.name}<br>
                        <b>Rua:</b> ${c.street || '-'}<br>
                        <b>Cidade:</b> ${c.city || '-'}<br>
                        <b>Capacidade:</b> ${c.used}/${c.capacity}<br>
                        <b>Status:</b> ${statusLabel(c.status)}<br>
                        <a href="${caixaUrl.replace('__ID__', c.id)}" style="color:#2563eb">Ver detalhes</a>
                    </div>`;
                    L.marker([c.lat, c.lng], { icon }).bindPopup(popup).addTo(caixaLayer);
                    bounds.push([c.lat, c.lng]);
                    caixaCount++;
                });

                data.ctos.forEach(c => {
                    if (!c.lat || !c.lng) return;
                    const icon = makeIcon('marker-cto', c.color || CTO_FALLBACK_COLOR, c.status, 12);
                    const popup = `<div style="min-width:180px">
                        <b style="color:${c.color || CTO_FALLBACK_COLOR}">${c.code}</b><br>
                        <b>Nome:</b> ${c.name}<br>
                        <b>Rua:</b> ${c.street || '-'}<br>
                        <b>Cidade:</b> ${c.city || '-'}<br>
                        <b>Capacidade:</b> ${c.used}/${c.capacity}<br>
                        <b>Status:</b> ${statusLabel(c.status)}<br>
                        <a href="${ctoUrl.replace('__ID__', c.id)}" style="color:#2563eb">Ver detalhes</a>
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
    setTimeout(function() { map.invalidateSize(); }, 200);
})();
</script>
@endpush
@endsection
