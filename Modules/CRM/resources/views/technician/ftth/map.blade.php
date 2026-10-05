@extends('crm::technician.layouts.master')

@section('title', 'Mapa da Rede')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* No celular o mapa ocupa o que sobra da tela; no desktop ganha altura
       fixa. Sem isso o Leaflet renderiza 0px quando o container ainda nao
       tinha laid out no primeiro tick. */
    #map { height: calc(100vh - 220px); min-height: 420px; border-radius: 12px; z-index: 0; }
    @media (max-width: 768px) {
        #map { height: calc(100vh - 260px); min-height: 360px; }
    }

    .leaflet-popup-content { font-size: 13px; line-height: 1.5; margin: 12px 14px; }
    .leaflet-popup-content b { color: #1f2937; }
    .leaflet-container { font: inherit; }

    .marker-cto { width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }
    .marker-caixa { background: #22c55e; width: 14px; height: 14px; border-radius: 3px; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }

    /* Planejado (sem fibra) fica translucido: o tecnico precisa separar o que
       ja foi construido do que ainda e plano. Ativo entra com cor cheia e um
       anel pulsante. */
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

    /* Contador e legenda flutuando sobre o mapa: no celular o header encolhe
       e o mapa ganha a altura liberada. */
    .map-overlay {
        position: absolute; z-index: 500; top: 10px; left: 10px;
        background: rgba(255,255,255,.94); border: 1px solid #e5e7eb;
        border-radius: 8px; padding: 6px 10px; font-size: 11px; color: #4b5563;
        box-shadow: 0 1px 3px rgba(0,0,0,.1); pointer-events: none;
    }
</style>
@endpush

@section('content')
<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <div class="flex items-center gap-2">
            <a href="{{ route('technician.portal.ftth') }}" class="text-sm text-blue-600 hover:underline">&larr; Lista</a>
            <h2 class="text-xl font-bold text-gray-900">Mapa da Rede</h2>
        </div>
        <p class="text-sm text-gray-500 mt-1">CTOs e Caixas de Emenda da sua filial. Toque num marcador para ver os detalhes e o plano de fusao.</p>
    </div>

    <form method="GET" class="flex items-center gap-2 shrink-0">
        @if(request('city'))
            <input type="hidden" name="city" value="{{ request('city') }}">
        @endif
        <select name="project" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            <option value="">Todos os Projetos</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}" @selected($projectId === $project->id)>{{ $project->name }}</option>
            @endforeach
        </select>
    </form>
</div>

{{-- As URLs de detalhe moram em data-attributes, e nao inline no JS: o Blade
     escapa o atributo com `e()`, o popup passa a ler do DOM, e o HTML entregue
     fica verificavel em teste sem depender de como o `json_encode` escapa a
     barra. --}}
<div class="relative bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div id="map"
         data-cto-url="{{ route('technician.portal.ftth.ctos.show', ['cto' => '__ID__']) }}"
         data-caixa-url="{{ route('technician.portal.ftth.caixas.show', ['caixa' => '__ID__']) }}"
         data-data-url="{{ route('technician.portal.ftth.map-data') }}"></div>
    <div class="map-overlay" id="counter">carregando...</div>
    <div id="mapError" class="hidden absolute inset-0 bg-white/95 flex items-center justify-center p-6 text-center z-[600]">
        <div>
            <p class="text-sm font-medium text-gray-800">Nao foi possivel carregar os marcadores.</p>
            <p class="text-xs text-gray-500 mt-1" id="mapErrorDetail"></p>
        </div>
    </div>
</div>

<div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500">
    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-red-500 border border-white shadow"></span> CTO</span>
    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-green-500 border border-white shadow"></span> Caixa de Emenda</span>
    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-gray-400/30 border border-white"></span> Planejado (sem fibra)</span>
    <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-full bg-red-500 border-2 border-green-400/60"></span> Ativo</span>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') {
        var d = document.getElementById('mapErrorDetail');
        if (d) d.textContent = 'Biblioteca de mapa nao carregou. Verifique a conexao.';
        document.getElementById('mapError').classList.remove('hidden');
        document.getElementById('mapError').classList.add('flex');
        return;
    }

    var mapEl = document.getElementById('map');
    var map = L.map('map', { zoomControl: true }).setView([-4.3, -46.5], 12);
    var caixaUrl = mapEl.dataset.caixaUrl;
    var ctoUrl = mapEl.dataset.ctoUrl;

    var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap',
        maxZoom: 19
    });
    var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri',
        maxZoom: 19,
        maxNativeZoom: 17
    });
    osmLayer.addTo(map);
    L.control.layers({ 'Padrao': osmLayer, 'Satelite': satelliteLayer }, null, { position: 'topright' }).addTo(map);

    var ctoLayer = L.layerGroup().addTo(map);
    var caixaLayer = L.layerGroup().addTo(map);

    var CTO_FALLBACK_COLOR = '#ef4444';

    function statusLabel(status) {
        if (status === 'active') return '<b style="color:#16a34a">ativa</b>';
        if (status === 'maintenance') return '<b style="color:#b45309">em manuten&ccedil;&atilde;o</b>';
        return '<span style="color:#6b7280">planejada (sem fibra lancada)</span>';
    }

    function esc(value) {
        if (value === null || value === undefined) return '-';
        return String(value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Nome, rua e cidade sao texto livre-digitado por terceiros e entram no
    // popup como HTML do Leaflet. Sem escapar, um nome de CTO com as Tags abre
    // um script dentro do portal do tecnico.
    function popupHtml(item, color, url) {
        return '<div style="min-width:190px">'
            + '<b style="color:' + color + '">' + esc(item.code) + '</b><br>'
            + '<b>Nome:</b> ' + esc(item.name) + '<br>'
            + '<b>Rua:</b> ' + esc(item.street) + '<br>'
            + '<b>Cidade:</b> ' + esc(item.city) + '<br>'
            + '<b>Capacidade:</b> ' + esc(item.used) + '/' + esc(item.capacity) + '<br>'
            + '<b>Status:</b> ' + statusLabel(item.status) + '<br>'
            + '<a href="' + url + '" style="color:#2563eb">Ver detalhes</a>'
            + '</div>';
    }

    function makeIcon(baseClass, color, status, size) {
        var active = status === 'active';
        var radius = baseClass === 'marker-caixa' ? 'border-radius:3px;' : 'border-radius:50%;';
        var html = '<span style="display:block;width:' + size + 'px;height:' + size + 'px;'
            + radius + 'border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);background:' + color + '"></span>';

        return L.divIcon({
            className: baseClass + (active ? ' marker-active' : ' marker-dim'),
            html: html,
            iconSize: [size, size],
            iconAnchor: [size / 2, size / 2]
        });
    }

    function loadMapData() {
        var params = new URLSearchParams();
        @if($projectId)
            params.set('project', '{{ $projectId }}');
        @endif
        @if(request('city'))
            params.set('city', @json(request('city')));
        @endif
        var query = params.toString();

        fetch(mapEl.dataset.dataUrl + (query ? '?' + query : ''), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                ctoLayer.clearLayers();
                caixaLayer.clearLayers();

                var bounds = [];
                var ctoCount = 0;
                var caixaCount = 0;

                (data.caixas || []).forEach(function (c) {
                    if (!c.lat || !c.lng) return;
                    var icon = makeIcon('marker-caixa', '#22c55e', c.status, 14);
                    L.marker([c.lat, c.lng], { icon: icon })
                        .bindPopup(popupHtml(c, '#22c55e', caixaUrl.replace('__ID__', c.id)))
                        .addTo(caixaLayer);
                    bounds.push([c.lat, c.lng]);
                    caixaCount++;
                });

                (data.ctos || []).forEach(function (c) {
                    if (!c.lat || !c.lng) return;
                    var color = c.color || CTO_FALLBACK_COLOR;
                    var icon = makeIcon('marker-cto', color, c.status, 12);
                    L.marker([c.lat, c.lng], { icon: icon })
                        .bindPopup(popupHtml(c, color, ctoUrl.replace('__ID__', c.id)))
                        .addTo(ctoLayer);
                    bounds.push([c.lat, c.lng]);
                    ctoCount++;
                });

                var counter = document.getElementById('counter');
                if (caixaCount + ctoCount === 0) {
                    counter.textContent = 'Nenhum ponto georreferenciado';
                } else {
                    counter.textContent = caixaCount + ' Caixas | ' + ctoCount + ' CTOs';
                }

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [30, 30] });
                }
            })
            .catch(function (err) {
                var detail = document.getElementById('mapErrorDetail');
                if (detail) detail.textContent = String(err && err.message ? err.message : err);
                var box = document.getElementById('mapError');
                box.classList.remove('hidden');
                box.classList.add('flex');
            });
    }

    loadMapData();

    // A sidebar vira gaveta no mobile e o container muda de largura quando ela
    // fecha; sem invalidateSize o Leaflet mantem o tile size antigo e sobra
    // faixa cinza na borda.
    setTimeout(function () { map.invalidateSize(); }, 250);
    window.addEventListener('resize', function () { map.invalidateSize(); });
});
</script>
@endpush
