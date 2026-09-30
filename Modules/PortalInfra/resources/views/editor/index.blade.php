@extends('infra::layouts.master')

@section('title', 'Editor de Rede FTTH')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { width: 100%; height: calc(100vh - 300px); min-height: 540px; border-radius: 12px; z-index: 0; }
    .map-wrap { position: relative; width: 100%; }
    .map-wrap .leaflet-container { width: 100%; height: 100%; }
    .map-wrap .leaflet-pane, .map-wrap .leaflet-top, .map-wrap .leaflet-bottom { z-index: 800; }
    .leaflet-popup-content { font-size: 13px; line-height: 1.5; }
    .leaflet-popup-content b { color: #1f2937; }
    .fiber-lbl { display: block; font-size: 10px; font-weight: 700; text-transform: uppercase; color: #6b7280; margin-top: 6px; margin-bottom: 2px; letter-spacing: .04em; }
    .fiber-inp { width: 100%; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 6px; padding: 4px 8px; font-size: 12px; }
    .marker-caixa { background: #22c55e; width: 15px; height: 15px; border-radius: 3px; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }
    .popup-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .popup-actions button { border: 0; border-radius: 6px; padding: 4px 10px; font-size: 12px; cursor: pointer; color: #fff; }
    .act-edit { background: #2563eb; }
    .act-connect { background: #f59e0b; }
    .act-split { background: #7c3aed; }
    .act-delete { background: #dc2626; }
    .popup-meta { margin-top: 6px; font-size: 12px; color: #6b7280; }
    .popup-meta b { color: #374151; }
    .toolbar { position: absolute; top: 10px; left: 50%; transform: translateX(-50%); z-index: 500; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.15); display: flex; gap: 4px; padding: 6px 8px; }
    .toolbar button { padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all .15s; }
    .toolbar button.active { background: #2563eb; color: #fff; }
    .toolbar button:not(.active) { background: #f3f4f6; color: #374151; }
    .toolbar button:hover:not(.active) { background: #e5e7eb; }
    .toolbar button:disabled { opacity: .4; cursor: not-allowed; }
    #fiberInfo { position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); z-index: 500; background: rgba(17,24,39,.9); color: #fff; border-radius: 8px; padding: 8px 16px; font-size: 13px; display: none; }
    #fiberInfo .btn-group { display: inline-flex; gap: 6px; margin-left: 10px; }
    #fiberInfo button { background: #2563eb; color: #fff; border: none; border-radius: 6px; padding: 4px 10px; font-size: 12px; cursor: pointer; }
    #fiberInfo button.danger { background: #dc2626; }
    #fiberInfo button:disabled { opacity: .4; cursor: not-allowed; }
    .toast { position: fixed; bottom: 20px; right: 20px; z-index: 2000; background: #111827; color: #fff; padding: 10px 18px; border-radius: 8px; font-size: 13px; box-shadow: 0 4px 12px rgba(0,0,0,.2); opacity: 0; transition: opacity .3s; pointer-events: none; }
    .toast.show { opacity: 1; }
    .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; color: #4b5563; }
    .legend-swatch { width: 12px; height: 12px; border: 1px solid rgba(0,0,0,.15); display: inline-block; }
</style>
@endpush

@section('content')
<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Editor de Rede FTTH</h1>
        <p class="text-gray-500 text-sm">Clique numa CEO ou CTO para editar, conectar ou colocar um splitter dentro dela. Desenhe o traçado da fibra lançada.</p>
    </div>
    <div class="flex items-center gap-3">
        <select id="citySelect" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @forelse($cities as $city)
                <option value="{{ $city }}" {{ $selected === $city ? 'selected' : '' }}>{{ $city }}</option>
            @empty
                <option value="">Nenhuma cidade</option>
            @endforelse
        </select>
        <div class="flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-red-500 border border-white shadow"></span> CTO</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-green-500 border border-white shadow"></span> Caixa</span>
        </div>
    </div>
</div>

<div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs font-semibold uppercase text-gray-500 mb-2">CTOs da Rede</p>
        <p class="text-2xl font-bold text-red-600" id="countCto">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Caixas de Emenda</p>
        <p class="text-2xl font-bold text-green-600" id="countCaixa">0</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Fibra Lançada</p>
        <p class="text-lg font-bold text-blue-600" id="totalFiber">0 m</p>
    </div>
</div>

<div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
        <h2 class="text-sm font-bold text-gray-700 uppercase">Relatório da Rede</h2>
        <div class="flex items-center gap-2">
            <button id="btnReloadReport" class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-xs font-semibold hover:bg-gray-200">Atualizar</button>
            <button id="btnValidate" class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg text-xs font-semibold hover:bg-amber-200">Validar topologia</button>
            <a id="btnExportKml" href="#" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700">Exportar KML editado</a>
            <a id="btnExportCsv" href="#" class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700">Exportar CSV</a>
        </div>
    </div>
    <div id="reportBody" class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
        <div class="text-gray-400">Carregando relatório...</div>
    </div>
</div>

<div class="mt-3 hidden rounded-lg border border-amber-300 bg-amber-50 p-3 text-xs text-amber-900" id="orphanSplitters"></div>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
    <div id="map" class="w-full"></div>
    <div class="toolbar">
        <button id="btnMove" class="active">Mover</button>
        <button id="btnFiber">Desenhar Fibra</button>
        <button id="btnAddSplitter" title="Abra o popup de uma CEO ou CTO e escolha Adicionar splitter">Adicionar Splitter</button>
        <button id="btnConnect">Conectar</button>
        <button id="btnCancel">Cancelar</button>
    </div>
    <div id="fiberInfo"></div>
</div>

<div id="connectModal" class="fixed inset-0 z-[1500] hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Nova Conexão</h3>
            <button id="btnCloseModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Origem</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <select id="srcType" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                        <option value="">Tipo...</option>
                        <option value="splitter">Splitter</option>
                        <option value="caixa">Caixa de Emenda</option>
                        <option value="cto">CTO</option>
                        <option value="olt">OLT</option>
                        <option value="ponto">Ponto Final</option>
                    </select>
                    <select id="srcElement" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full"></select>
                </div>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    <select id="srcPort" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                        <option value="">Porta...</option>
                    </select>
                    <span class="text-xs text-gray-400 self-center">Porta 0 = entrada do splitter</span>
                </div>
            </div>
            <div class="text-center text-gray-300 font-bold">▼</div>
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Fibra lançada (opcional)</label>
                <select id="fiberCnx" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full mt-1">
                    <option value="">Sem fibra</option>
                </select>
            </div>
            <div class="text-center text-gray-300 font-bold">▼</div>
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Destino</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <select id="dstType" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                        <option value="">Tipo...</option>
                        <option value="splitter">Splitter</option>
                        <option value="caixa">Caixa de Emenda</option>
                        <option value="cto">CTO</option>
                        <option value="ponto">Ponto Final</option>
                    </select>
                    <select id="dstElement" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full"></select>
                </div>
                <select id="dstPort" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full mt-2">
                    <option value="">Porta...</option>
                </select>
            </div>
            <div class="flex gap-2 pt-2">
                <button id="btnSaveConnection" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-semibold hover:bg-blue-700">Salvar Conexão</button>
                <button id="btnCloseModal2" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div id="elementModal" class="fixed inset-0 z-[1500] hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900" id="elementModalTitle">Editar</h3>
            <button id="btnCloseElementModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Nome</label>
                <input id="elName" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Código</label>
                    <input id="elCode" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Capacidade</label>
                    <input id="elCapacity" type="number" min="1" max="1024" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Status</label>
                <select id="elStatus" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                    <option value="active">Ativa (fibra lançada)</option>
                    <option value="inactive">Inativa (projeto, sem fibra lançada)</option>
                </select>
            </div>
            <div id="elColorRow">
                <label class="text-xs font-semibold uppercase text-gray-500">Cor da CTO</label>
                <input id="elColor" type="color" value="#ef4444" class="w-12 h-8 border border-gray-300 rounded-lg">
                <span class="text-xs text-gray-500 ml-2">Use a cor para identificar a CTO no mapa.</span>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Rua</label>
                <input id="elStreet" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
            </div>
            <div class="flex gap-2 pt-2">
                <button id="btnSaveElement" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-semibold hover:bg-blue-700">Salvar</button>
                <button id="btnCancelElement" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div id="splitterModal" class="fixed inset-0 z-[1500] hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-900">Editar Splitter</h3>
            <button id="btnCloseSplitterModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Nome</label>
                <input id="spName" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Código</label>
                    <input id="spCode" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                </div>
                <div>
                    <label class="text-xs font-semibold uppercase text-gray-500">Saídas</label>
                    <select id="spOutputs" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full">
                        <option value="8">1x8</option>
                        <option value="16">1x16</option>
                        <option value="32">1x32</option>
                        <option value="64">1x64</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold uppercase text-gray-500">Progenitor</label>
                <select id="spParent" class="px-2 py-2 border border-gray-300 rounded-lg text-sm w-full"></select>
                <p class="text-xs text-gray-500 mt-1">O splitter pertence à CEO ou CTO escolhida.</p>
            </div>
            <div class="flex gap-2 pt-2">
                <button id="btnSaveSplitter" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-semibold hover:bg-blue-700">Salvar</button>
                <button id="btnCancelSplitter" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    const dataUrl = '{{ route("infra.ftth.editor.data") }}';
    const moveUrl = '{{ route("infra.ftth.editor.move", ["type" => "__TYPE__", "id" => "__ID__"]) }}';
    const fiberStoreUrl = '{{ route("infra.ftth.editor.fibers.store") }}';
    const fiberUpdateUrl = '{{ route("infra.ftth.editor.fibers.update", ["id" => "__ID__"]) }}';
    const fiberDeleteUrl = '{{ route("infra.ftth.editor.fibers.destroy", ["id" => "__ID__"]) }}';
    const splitterStoreUrl = '{{ route("infra.ftth.editor.splitters.store") }}';
    const splitterUpdateUrl = '{{ route("infra.ftth.editor.splitters.update", ["id" => "__ID__"]) }}';
    const splitterDeleteUrl = '{{ route("infra.ftth.editor.splitters.destroy", ["id" => "__ID__"]) }}';
    const elementUpdateUrl = '{{ route("infra.ftth.editor.elements.update", ["type" => "__TYPE__", "id" => "__ID__"]) }}';
    const connectionStoreUrl = '{{ route("infra.ftth.editor.connections.store") }}';
    const connectionDeleteUrl = '{{ route("infra.ftth.editor.connections.destroy", ["id" => "__ID__"]) }}';
    const reportUrl = '{{ route("infra.ftth.editor.report", ["city" => "__CITY__"]) }}';
    const validateUrl = '{{ route("infra.ftth.editor.validate", ["city" => "__CITY__"]) }}';
    const exportKmlUrl = '{{ route("infra.ftth.editor.export.kml", ["city" => "__CITY__"]) }}';
    const exportCsvUrl = '{{ route("infra.ftth.editor.export.csv", ["city" => "__CITY__"]) }}';

    const map = L.map('map', { zoomControl: true }).setView([-4.3, -46.5], 12);

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19
    });
    const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri', maxZoom: 19, maxNativeZoom: 17
    });
    const terrainLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data: &copy; OpenStreetMap, SRTM | Style: &copy; OpenTopoMap', maxZoom: 17, maxNativeZoom: 17
    });
    osmLayer.addTo(map);
    L.control.layers({
        'Padrao': osmLayer,
        'Satelite': satelliteLayer,
        'Terreno': terrainLayer
    }, null, { position: 'topright' }).addTo(map);

    let mode = 'move';
    let currentCity = null;
    let ctoLayer = L.layerGroup().addTo(map);
    let caixaLayer = L.layerGroup().addTo(map);
    let fiberLayer = L.layerGroup().addTo(map);
    let connectionLayer = L.layerGroup().addTo(map);

    let ctos = [];
    let caixas = [];
    let splitters = [];
    let fibers = [];

    // Desenho de fibra
    let drawingPoints = [];
    let drawingPolyline = null;
    let drawingDistance = 0;
    const drawingPath = [];

    const caixaIcon = L.divIcon({ className: 'marker-caixa', iconSize: [15, 15], iconAnchor: [8, 8] });

    // O editor e a ferramenta de construcao: aqui tudo aparece com cor cheia.
    // Quem ainda nao lancou fibra continua editavel, mas no mapa de rede ele
    // fica translucido. A CTO e o unico elemento com cor propria: cada CTO nasce
    // com a cor que o tecnico escolheu para separar visualmente as CTOs da
    // mesma CEO.
    function ctoIconFor(color) {
        return L.divIcon({
            className: 'marker-cto-dyn',
            html: '<span style="display:block;width:13px;height:13px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);background:' + escHtml(color || '#ef4444') + '"></span>',
            iconSize: [13, 13],
            iconAnchor: [7, 7]
        });
    }

    let editingElement = null;
    let editingSplitter = null;

    function showToast(msg) {
        const el = document.getElementById('toast');
        el.textContent = msg;
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 2500);
    }

    function haversine(a, b) {
        const R = 6371000;
        const dLat = (b.lat - a.lat) * Math.PI / 180;
        const dLng = (b.lng - a.lng) * Math.PI / 180;
        const s = Math.sin(dLat / 2) ** 2 + Math.cos(a.lat * Math.PI / 180) * Math.cos(b.lat * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(s), Math.sqrt(1 - s));
    }

    function setMode(m) {
        mode = m;
        document.querySelectorAll('.toolbar button').forEach(b => b.classList.remove('active'));
        document.getElementById('btn' + m.charAt(0).toUpperCase() + m.slice(1)).classList.add('active');
        document.getElementById('fiberInfo').style.display = 'none';

        if (m !== 'fiber' && drawingPolyline) {
            drawingPolyline.remove();
            drawingPolyline = null;
            drawingPoints = [];
            drawingDistance = 0;
        }
    }

    function fmtMeters(m) {
        if (m >= 1000) return (m / 1000).toFixed(2) + ' km';
        return Math.round(m) + ' m';
    }

    function renderFiber(d) {
        const pts = (d.geometry || []).map(p => [Number(p.lat ?? p[0]), Number(p.lng ?? p[1])]);
        if (pts.length < 2) return;
        const color = d.type === 'tronco' ? '#dc2626' : d.type === 'distribuicao' ? '#2563eb' : '#d97706';
        L.polyline(pts, { color: color, weight: 3, opacity: 0.85 }).addTo(fiberLayer)
            .bindPopup(
                '<div style="min-width:200px">' +
                '<p style="margin:0 0 6px"><b>' + (d.name || 'Fibra ' + d.type) + '</b> • ' + fmtMeters(d.length_meters) + '</p>' +
                '<label class="fiber-lbl">Nome</label><input class="fiber-inp" id="f-name" value="' + escHtml(d.name || '') + '">' +
                '<label class="fiber-lbl">Tipo</label><select class="fiber-inp" id="f-type"><option value="tronco"' + (d.type === 'tronco' ? ' selected' : '') + '>Tronco</option><option value="distribuicao"' + (d.type === 'distribuicao' ? ' selected' : '') + '>Distribuição</option><option value="drop"' + (d.type === 'drop' ? ' selected' : '') + '>Drop</option></select>' +
                '<div style="display:flex;gap:8px;margin-top:6px">' +
                '<span style="flex:1"><label class="fiber-lbl">Qtde fibras</label><input class="fiber-inp" id="f-count" value="' + escHtml(d.fiber_count || '') + '"></span>' +
                '<span style="flex:1"><label class="fiber-lbl">Cor do tubo</label><input class="fiber-inp" id="f-tube" value="' + escHtml(d.tube_color || '') + '"></span>' +
                '</div>' +
                '<button class="fiber-save" data-id="' + d.id + '" style="width:100%;margin-top:8px;background:#2563eb;color:#fff;border:0;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer">Salvar</button>' +
                '<button class="btn-delete-fiber" data-id="' + d.id + '" style="width:100%;margin-top:4px;background:#dc2626;color:#fff;border:0;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer">Excluir fibra</button>' +
                '</div>'
            );
    }

    function escHtml(s) {
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function findPos(type, id) {
        if (!id) return null;
        const list = type === 'cto' ? ctos : type === 'caixa' ? caixas : type === 'splitter' ? splitters : [];
        const el = list.find(e => e.id === Number(id));
        if (!el) return null;

        // Splitter nao tem marcador proprio, entao o cabo que sai dele e
        // desenhado a partir da CEO ou CTO que o contém.
        if (type === 'splitter' && el.parent_type) {
            const parent = findElement(el.parent_type, el.parent_id);
            if (parent) return [parent.lat, parent.lng];
        }

        return [el.lat, el.lng];
    }

    // Splitter e componente interno: nao existe marcador solto no mapa. Ele
    // aparece listado dentro do popup da CEO ou CTO que o contém, que e onde
    // ele e editado e excluido.
    function childSplitters(parentType, parentId) {
        const items = splitters.filter(s => s.parent_type === parentType && Number(s.parent_id) === Number(parentId));
        if (items.length === 0) return '';

        return '<div class="popup-meta" style="margin-top:8px"><b>Splitters (' + items.length + ')</b>' +
            items.map(s =>
                '<div style="display:flex;align-items:center;gap:6px;margin-top:4px">' +
                '<span style="flex:1;color:#7c3aed">' + escHtml(s.code || s.name) + ' — 1x' + escHtml(String(s.output_ports || 0)) + '</span>' +
                '<button class="act-edit" data-action="splitter-edit" data-id="' + s.id + '">Editar</button>' +
                '<button class="act-delete" data-action="splitter-delete" data-id="' + s.id + '">Excluir</button>' +
                '</div>'
            ).join('') +
            '</div>';
    }

    // Popups: a CEO e a CTO sao o ponto de partida da obra. Clicar nelas abre
    // editar, conectar e adicionar splitter dentro delas.
    function elementActions(type, id) {
        return '<div class="popup-actions">' +
            '<button class="act-edit" data-action="edit" data-type="' + type + '" data-id="' + id + '">Editar</button>' +
            '<button class="act-connect" data-action="connect" data-type="' + type + '" data-id="' + id + '">Conectar</button>' +
            '<button class="act-split" data-action="splitter" data-type="' + type + '" data-id="' + id + '">Adicionar splitter</button>' +
            '</div>';
    }

    function ctoPopup(c) {
        return '<div style="min-width:210px">' +
            '<b style="color:#dc2626">' + escHtml(c.code) + '</b> — CTO<br>' +
            escHtml(c.name) + '<br>' + escHtml(c.street || '') + '<br>' +
            '<div class="popup-meta">Portas: <b>' + c.used + '/' + c.capacity + '</b> • Splitters: <b>' + (c.splitter_count || 0) + '</b><br>' +
            'Status: <b>' + (c.status === 'active' ? 'ativa' : 'inativa (sem fibra lançada)') + '</b><br>' +
            'Cor: <span style="display:inline-block;width:10px;height:10px;border-radius:50%;vertical-align:middle;border:1px solid #d1d5db;background:' + escHtml(c.color || '#ef4444') + '"></span></div>' +
            childSplitters('cto', c.id) +
            elementActions('cto', c.id) +
            '</div>';
    }

    function caixaPopup(c) {
        return '<div style="min-width:210px">' +
            '<b style="color:#16a34a">' + escHtml(c.code) + '</b> — CEO / Caixa de Emenda<br>' +
            escHtml(c.name) + '<br>' + escHtml(c.street || '') + '<br>' +
            '<div class="popup-meta">CTOs: <b>' + (c.cto_count || 0) + '</b> • Splitters: <b>' + (c.splitter_count || 0) + '</b><br>' +
            'Status: <b>' + (c.status === 'active' ? 'ativa' : 'inativa (sem fibra lançada)') + '</b></div>' +
            childSplitters('caixa', c.id) +
            elementActions('caixa', c.id) +
            '</div>';
    }

    // Splitter sem progenitor nao entra em nenhum popup. Como ele tambem nao tem
// marcador, ficaria invisivel: o banner mantem esses splitters legiveis ate
// o tecnico realocar o progenitor pelo modal de edicao.
function renderOrphanSplitters() {
const box = document.getElementById('orphanSplitters');
if (!box) return;

const orphans = splitters.filter(s => !s.parent_type || !s.parent_id);
if (orphans.length === 0) {
box.classList.add('hidden');
box.innerHTML = '';
return;
}

box.classList.remove('hidden');
box.innerHTML = '<b>' + orphans.length + ' splitter(s) sem CEO/CTO de origem.</b> ' +
'Como splitters sao componentes internos, eles nao aparecem no mapa. Defina a origem de cada um: ' +
orphans.map(s =>
'<span style="display:inline-flex;align-items:center;gap:6px;margin:4px 6px 0 0;background:#fff;border:1px solid #fcd34d;border-radius:6px;padding:3px 8px">' +
'<b>' + escHtml(s.code || s.name) + '</b>' +
'<button class="act-edit" data-action="splitter-edit" data-id="' + s.id + '">Definir origem</button>' +
'</span>'
).join('');
}

function renderConnection(cnx) {
        const start = findPos(cnx.source_type, cnx.source_id);
        const end = findPos(cnx.target_type, cnx.target_id);
        if (!start) return;

        let color = '#f97316';
        let label = (cnx.source_code || cnx.source_type) + ' p' + (cnx.source_port ?? '-');
        label += ' → ' + (cnx.target_code || cnx.target_type);
        if (cnx.target_port !== null && cnx.target_port !== undefined) label += ' p' + cnx.target_port;

        const opts = { color: color, weight: 2, dashArray: '4 6', opacity: 0.8 };
        let line;
        if (end) {
            line = L.polyline([start, end], opts).addTo(connectionLayer);
        } else {
            line = L.polyline([start, [start[0] + 0.0005, start[1]]], opts).addTo(connectionLayer);
        }
        line.bindPopup('<div><b>Conexão</b><br>' + label + '<br><button class="btn-delete-connection" data-id="' + cnx.id + '" style="margin-top:6px;background:#dc2626;color:#fff;border:0;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer">Remover</button></div>');
    }

    function loadData() {
        const select = document.getElementById('citySelect');
        const city = select.value;
        if (!city) {
            showToast('Nenhuma cidade disponível.');
            return;
        }

        fetch(dataUrl + '?cidade=' + encodeURIComponent(city))
            .then(r => r.json())
            .then(data => {
                if (!data.city) return;
                currentCity = data.city;
                ctos = data.ctos || [];
                caixas = data.caixas || [];
                splitters = data.splitters || [];
                fibers = data.fibers || [];
                ctoLayer.clearLayers();
                caixaLayer.clearLayers();
                fiberLayer.clearLayers();
                connectionLayer.clearLayers();

                (data.fibers || []).forEach(renderFiber);

                let bounds = [];
                (data.ctos || []).forEach(c => {
                    const marker = L.marker([c.lat, c.lng], { icon: ctoIconFor(c.color), draggable: true }).addTo(ctoLayer);
                    marker.on('dragend', e => saveMove('cto', c.id, e.target.getLatLng()));
                    marker.bindPopup(ctoPopup(c));
                    bounds.push([c.lat, c.lng]);
                });

                (data.caixas || []).forEach(c => {
                    const marker = L.marker([c.lat, c.lng], { icon: caixaIcon, draggable: true }).addTo(caixaLayer);
                    marker.on('dragend', e => saveMove('caixa', c.id, e.target.getLatLng()));
                    marker.bindPopup(caixaPopup(c));
                    bounds.push([c.lat, c.lng]);
                });

                document.getElementById('countCto').textContent = data.ctos.length;
                renderOrphanSplitters();
                document.getElementById('countCaixa').textContent = data.caixas.length;
                const totalFiber = (data.fibers || []).reduce((s, f) => s + (f.length_meters || 0), 0);
                document.getElementById('totalFiber').textContent = fmtMeters(totalFiber);

                (data.connections || []).forEach(renderConnection);

                // Preenche selects do modal de conexão
                const fiberSelect = document.getElementById('fiberCnx');
                const prevFiber = fiberSelect.value;
                fiberSelect.innerHTML = '<option value="">Sem fibra</option>' + fibers.map(f =>
                    '<option value="' + f.id + '">' + (f.name || ('Fibra ' + f.type)) + ' (' + fmtMeters(f.length_meters) + ')</option>'
                ).join('');
                if (prevFiber) fiberSelect.value = prevFiber;

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
                }
                loadReport();
            });
    }

    function loadReport() {
        const city = document.getElementById('citySelect').value;
        if (!city) return;
        const body = document.getElementById('reportBody');
        body.innerHTML = '<div class="text-gray-400 col-span-full">Carregando relatório...</div>';

        fetch(reportUrl.replace('__CITY__', encodeURIComponent(city)))
            .then(r => r.json())
            .then(d => {
                const s = d.stats;
                const c = d.capacity;
                const byType = (d.fiber_by_type && Object.keys(d.fiber_by_type).length)
                    ? Object.entries(d.fiber_by_type).map(([k, v]) => '<span class="text-gray-600">' + k + ': ' + v.count + ' fibra(s), ' + fmtMeters(v.length_meters) + '</span>').join('<br>')
                    : '<span class="text-gray-400">Nenhuma fibra lançada</span>';

                body.innerHTML =
                    statCard('CTOs', s.ctos + ' <span class="text-gray-400 text-xs">de ' + s.cto_capacity_total + ' portas</span>') +
                    statCard('Ocupação CTO', c.cto_used_pct + '%') +
                    statCard('Caixas', s.caixas) +
                    statCard('Splitters', s.splitters + ' <span class="text-gray-400 text-xs">' + s.splitter_output_used + '/' + s.splitter_output_total + ' saídas</span>') +
                    statCard('Fibra lançada', fmtMeters(s.fiber_total_meters)) +
                    statCard('Conexões', s.connections) +
                    '<div class="bg-gray-50 rounded-lg p-3"><p class="text-xs font-semibold uppercase text-gray-500 mb-1">Fibra por tipo</p>' + byType + '</div>';
            })
            .catch(() => { body.innerHTML = '<div class="text-red-500 col-span-full">Falha ao carregar relatório.</div>'; });
    }

    function statCard(label, value) {
        return '<div class="bg-gray-50 rounded-lg p-3"><p class="text-xs font-semibold uppercase text-gray-500">' + label + '</p><p class="text-xl font-bold text-gray-800">' + value + '</p></div>';
    }

    document.getElementById('btnReloadReport').addEventListener('click', loadReport);
    document.getElementById('btnExportKml').addEventListener('click', e => {
        const city = document.getElementById('citySelect').value;
        if (city) window.location.href = exportKmlUrl.replace('__CITY__', encodeURIComponent(city));
    });

    document.getElementById('btnExportCsv').addEventListener('click', e => {
        const city = document.getElementById('citySelect').value;
        if (city) window.location.href = exportCsvUrl.replace('__CITY__', encodeURIComponent(city));
    });

    document.getElementById('btnValidate').addEventListener('click', () => {
        const city = document.getElementById('citySelect').value;
        if (!city) return;
        fetch(validateUrl.replace('__CITY__', encodeURIComponent(city)))
            .then(r => r.json())
            .then(d => {
                if (d.issues.length === 0) {
                    showToast('Topologia válida: nenhum problema encontrado.');
                    return;
                }
                const msgs = d.issues.map(i => '• (' + i.level + ') ' + i.message).join('\n');
                alert('Problemas encontrados (' + d.issues.length + '):\n\n' + msgs);
            })
            .catch(() => showToast('Falha ao validar.'));
    });

    function saveMove(type, id, latlng) {
        fetch(moveUrl.replace('__TYPE__', type).replace('__ID__', id), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ lat: latlng.lat, lng: latlng.lng })
        })
            .then(r => { if (!r.ok) throw new Error('Erro ' + r.status); return r.json(); })
            .then(() => showToast('Posição atualizada.'))
            .catch(e => { showToast('Falha: ' + e.message); loadData(); });
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete-fiber')) {
            if (!confirm('Excluir esta fibra lançada?')) return;
            fetch(fiberDeleteUrl.replace('__ID__', e.target.dataset.id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(() => { showToast('Fibra excluída.'); loadData(); })
                .catch(() => showToast('Falha ao excluir.'));
        }
        if (e.target.classList.contains('btn-delete-connection')) {
            if (!confirm('Remover esta conexão?')) return;
            fetch(connectionDeleteUrl.replace('__ID__', e.target.dataset.id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }).then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(() => { showToast('Conexão removida.'); loadData(); })
                .catch(() => showToast('Falha ao remover.'));
        }
        if (e.target.classList.contains('fiber-save')) {
            const popupEl = e.target.closest('.leaflet-popup-content');
            const id = e.target.dataset.id;
            const name = popupEl.querySelector('#f-name').value;
            const type = popupEl.querySelector('#f-type').value;
            const count = popupEl.querySelector('#f-count').value;
            const tube = popupEl.querySelector('#f-tube').value;
            fetch(fiberUpdateUrl.replace('__ID__', id), {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name: name, type: type, fiber_count: count, tube_color: tube })
            }).then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(() => { showToast('Fibra atualizada.'); loadData(); })
                .catch(() => showToast('Falha ao salvar.'));
        }
    });

    // Modal de conexão
    function openConnectModal() {
        const modal = document.getElementById('connectModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        fillElementSelect('srcElement', document.getElementById('srcType').value);
        fillElementSelect('dstElement', document.getElementById('dstType').value);
    }

    function closeConnectModal() {
        const modal = document.getElementById('connectModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function fillElementSelect(selectId, type) {
        const sel = document.getElementById(selectId);
        sel.innerHTML = '<option value="">Elemento...</option>';
        let items = [];
        if (type === 'splitter') items = splitters.map(e => ({ v: e.id, l: (e.code || e.name) + ' (' + e.ratio + ')' }));
        else if (type === 'caixa') items = caixas.map(e => ({ v: e.id, l: e.code + ' - ' + e.name }));
        else if (type === 'cto') items = ctos.map(e => ({ v: e.id, l: e.code + ' - ' + e.name }));
        items.forEach(i => {
            const o = document.createElement('option');
            o.value = i.v;
            o.textContent = i.l;
            sel.appendChild(o);
        });
    }

    function fillPortSelect(selectId, type, elementId) {
        const sel = document.getElementById(selectId);
        const options = [['', 'Porta...']];
        if (type === 'splitter') {
            const sp = splitters.find(e => e.id === Number(elementId));
            const outputs = sp ? sp.output_ports : 16;
            options.push(['0', '0 (entrada)']);
            for (let i = 1; i <= outputs; i++) options.push([String(i), String(i)]);
        } else if (type === 'caixa' || type === 'cto') {
            const item = (type === 'caixa' ? caixas : ctos).find(e => e.id === Number(elementId));
            for (let i = 1; i <= (item ? (item.capacity || 8) : 8); i++) options.push([String(i), String(i)]);
        } else {
            options.push(['0', '0']);
        }
        sel.innerHTML = options.map(o => '<option value="' + o[0] + '">' + o[1] + '</option>').join('');
    }

    document.getElementById('srcType').addEventListener('change', e => {
        fillElementSelect('srcElement', e.target.value);
    });
    document.getElementById('dstType').addEventListener('change', e => {
        fillElementSelect('dstElement', e.target.value);
    });
    document.getElementById('srcElement').addEventListener('change', e => {
        fillPortSelect('srcPort', document.getElementById('srcType').value, e.target.value);
    });
    document.getElementById('dstElement').addEventListener('change', e => {
        fillPortSelect('dstPort', document.getElementById('dstType').value, e.target.value);
    });

    document.getElementById('btnConnect').addEventListener('click', () => {
        setMode('move');
        openConnectModal();
    });
    document.getElementById('btnCloseModal').addEventListener('click', closeConnectModal);
    document.getElementById('btnCloseModal2').addEventListener('click', closeConnectModal);

    document.getElementById('btnSaveConnection').addEventListener('click', () => {
        const source_type = document.getElementById('srcType').value;
        const source_id = document.getElementById('srcElement').value;
        const source_port = document.getElementById('srcPort').value;
        const fiber_link_id = document.getElementById('fiberCnx').value;
        const target_type = document.getElementById('dstType').value;
        const target_id = document.getElementById('dstElement').value;
        const target_port = document.getElementById('dstPort').value;

        if (!source_type || !target_type) { showToast('Selecione origem e destino.'); return; }

        fetch(connectionStoreUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                city: currentCity,
                source_type: source_type,
                source_id: source_id ? Number(source_id) : null,
                source_port: source_port !== '' ? Number(source_port) : null,
                fiber_link_id: fiber_link_id ? Number(fiber_link_id) : null,
                target_type: target_type,
                target_id: target_id ? Number(target_id) : null,
                target_port: target_port !== '' ? Number(target_port) : null
            })
        })
            .then(r => Promise.all([r.ok, r.json()]))
            .then(([ok, json]) => {
                if (!ok) { throw new Error(json.message || 'Erro ao criar conexão.'); }
                showToast(json.message);
                closeConnectModal();
                loadData();
            })
            .catch(err => showToast('Erro: ' + err.message));
    });

    document.getElementById('citySelect').addEventListener('change', loadData);
    document.getElementById('btnMove').addEventListener('click', () => setMode('move'));
    document.getElementById('btnFiber').addEventListener('click', () => setMode('fiber'));
    document.getElementById('btnCancel').addEventListener('click', () => setMode('move'));

    document.getElementById('btnAddSplitter').addEventListener('click', () => {
        showToast('Abra o popup de uma CEO ou CTO e escolha "Adicionar splitter".');
    });

    // Ações dos popups. O Leaflet bloqueia a propagação do clique dentro do
    // popup, então este listener não compete com o desenho de fibra.
    document.getElementById('map').addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;

        const action = btn.dataset.action;
        const id = Number(btn.dataset.id);
        const type = btn.dataset.type;

        if (action === 'edit') {
            openElementModal(type, id);
        } else if (action === 'connect') {
            openConnectModal();
            const side = type === 'splitter' ? 'srcType' : 'dstType';
            document.getElementById(side).value = type;
            document.getElementById(side).dispatchEvent(new Event('change'));
            document.getElementById(side === 'srcType' ? 'srcElement' : 'dstElement').value = id;
        } else if (action === 'splitter') {
            startAddSplitter(type, id);
        } else if (action === 'splitter-edit') {
            openSplitterModal(id);
        } else if (action === 'splitter-delete') {
            if (!confirm('Excluir este splitter?')) return;
            fetch(splitterDeleteUrl.replace('__ID__', id), {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(r => r.json())
                .then(json => { showToast(json.message || 'Splitter removido.'); loadData(); })
                .catch(err => showToast('Erro: ' + err.message));
        }
    });

    // O banner de splitters orfaos vive fora do mapa, entao precisa do seu
    // proprio listener para os botoes de edicao.
    document.getElementById('orphanSplitters').addEventListener('click', function (e) {
        const btn = e.target.closest('button[data-action]');
        if (!btn) return;
        openSplitterModal(Number(btn.dataset.id));
    });

    function findElement(type, id) {
        const list = type === 'cto' ? ctos : caixas;
        return list.find(e => e.id === Number(id)) || null;
    }

    // Splitter sempre nasce dentro de uma CEO ou CTO. O progenitor vem do
    // popup; o ponto no mapa so define onde ele fica no mapa.
    function startAddSplitter(type, id) {
        const parent = findElement(type, id);
        if (!parent) { showToast('Escolha uma CEO ou CTO do mapa.'); return; }

        const outputs = parseInt(prompt('Splitter dentro de ' + (parent.code || parent.name) + '.\nSaídas (8, 16, 32 ou 64):', '8'), 10);
        if (![8, 16, 32, 64].includes(outputs)) { showToast('Use 8, 16, 32 ou 64.'); return; }

        const name = prompt('Nome do splitter:', 'Splitter 1x' + outputs);
        if (!name) return;

        showToast('Clique no mapa para posicionar o splitter.');
        map.once('click', function (e) {
            fetch(splitterStoreUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    city: currentCity,
                    name: name,
                    parent_type: type,
                    parent_id: id,
                    lat: e.latlng.lat,
                    lng: e.latlng.lng,
                    input_ports: 1,
                    output_ports: outputs
                })
            })
                .then(r => Promise.all([r.ok, r.json()]))
                .then(([ok, json]) => {
                    if (!ok) { throw new Error(json.message || 'Erro ao criar splitter.'); }
                    showToast(json.message);
                    loadData();
                })
                .catch(err => showToast('Erro: ' + err.message));
        });
    }

    function openElementModal(type, id) {
        const el = findElement(type, id);
        if (!el) return;

        editingElement = { type: type, id: id };
        document.getElementById('elementModalTitle').textContent = type === 'cto' ? 'Editar CTO' : 'Editar CEO / Caixa de Emenda';
        document.getElementById('elName').value = el.name || '';
        document.getElementById('elCode').value = el.code || '';
        document.getElementById('elCapacity').value = el.capacity || 1;
        document.getElementById('elStatus').value = el.status || 'inactive';
        document.getElementById('elStreet').value = el.street || '';
        // CEO usa a cor padrao do mapa: a cor editavel e so da CTO.
        document.getElementById('elColorRow').style.display = type === 'cto' ? 'block' : 'none';
        document.getElementById('elColor').value = el.color || '#ef4444';

        openModal('elementModal');
    }

    document.getElementById('btnSaveElement').addEventListener('click', () => {
        if (!editingElement) return;

        const payload = {
            name: document.getElementById('elName').value,
            code: document.getElementById('elCode').value,
            capacity: Number(document.getElementById('elCapacity').value),
            status: document.getElementById('elStatus').value,
            street: document.getElementById('elStreet').value
        };

        if (editingElement.type === 'cto') {
            payload.color = document.getElementById('elColor').value;
        }

        fetch(elementUpdateUrl.replace('__TYPE__', editingElement.type).replace('__ID__', editingElement.id), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
            .then(r => Promise.all([r.ok, r.json()]))
            .then(([ok, json]) => {
                if (!ok) { throw new Error(json.message || 'Erro ao salvar.'); }
                showToast(json.message);
                closeModal('elementModal');
                loadData();
            })
            .catch(err => showToast('Erro: ' + err.message));
    });

    document.getElementById('btnCloseElementModal').addEventListener('click', () => closeModal('elementModal'));
    document.getElementById('btnCancelElement').addEventListener('click', () => closeModal('elementModal'));

    function openSplitterModal(id) {
        const s = splitters.find(e => e.id === Number(id));
        if (!s) return;

        editingSplitter = s;
        document.getElementById('spName').value = s.name || '';
        document.getElementById('spCode').value = s.code || '';
        document.getElementById('spOutputs').value = s.output_ports;

        const opts = caixas.map(c => '<option value="caixa:' + c.id + '">CEO ' + escHtml(c.code) + '</option>').join('')
            + ctos.map(c => '<option value="cto:' + c.id + '">CTO ' + escHtml(c.code) + '</option>').join('');
        document.getElementById('spParent').innerHTML = opts;
        document.getElementById('spParent').value = s.parent_type + ':' + s.parent_id;

        openModal('splitterModal');
    }

    document.getElementById('btnSaveSplitter').addEventListener('click', () => {
        if (!editingSplitter) return;

        const [parentType, parentId] = document.getElementById('spParent').value.split(':');

        fetch(splitterUpdateUrl.replace('__ID__', editingSplitter.id), {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                name: document.getElementById('spName').value,
                code: document.getElementById('spCode').value,
                input_ports: 1,
                output_ports: Number(document.getElementById('spOutputs').value),
                parent_type: parentType,
                parent_id: Number(parentId)
            })
        })
            .then(r => Promise.all([r.ok, r.json()]))
            .then(([ok, json]) => {
                if (!ok) { throw new Error(json.message || 'Erro ao salvar.'); }
                showToast(json.message);
                closeModal('splitterModal');
                loadData();
            })
            .catch(err => showToast('Erro: ' + err.message));
    });

    document.getElementById('btnCloseSplitterModal').addEventListener('click', () => closeModal('splitterModal'));
    document.getElementById('btnCancelSplitter').addEventListener('click', () => closeModal('splitterModal'));

    function openModal(id) {
        const el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    // Desenho de fibra
    map.on('click', function(e) {
        if (mode !== 'fiber') return;
        drawingPoints.push({ lat: e.latlng.lat, lng: e.latlng.lng });

        if (drawingPoints.length >= 2) {
            const prev = drawingPoints[drawingPoints.length - 2];
            const curr = drawingPoints[drawingPoints.length - 1];
            drawingDistance += haversine(prev, curr);
        }

        if (drawingPolyline) drawingPolyline.remove();
        drawingPolyline = L.polyline(drawingPoints, { color: '#f59e0b', weight: 3, dashArray: '6 6' }).addTo(map);

        const info = document.getElementById('fiberInfo');
        info.style.display = 'block';
        info.innerHTML = 'Fibra em desenho: <b>' + fmtMeters(drawingDistance) + '</b>' +
            '<span class="btn-group">' +
            '<button id="btnSaveFiber">Salvar</button>' +
            '<button id="btnUndoPoint">Desfazer</button>' +
            '<button id="btnCancelFiber" class="danger">Cancelar</button>' +
            '</span>';

        document.getElementById('btnSaveFiber').addEventListener('click', saveFiber);
        document.getElementById('btnUndoPoint').addEventListener('click', undoPoint);
        document.getElementById('btnCancelFiber').addEventListener('click', () => setMode('move'));
    });

    function undoPoint() {
        if (drawingPoints.length < 2) { setMode('move'); return; }
        if (drawingPoints.length >= 2) {
            const a = drawingPoints[drawingPoints.length - 2];
            const b = drawingPoints[drawingPoints.length - 1];
            drawingDistance -= haversine(a, b);
        }
        drawingPoints.pop();
        if (drawingPolyline) drawingPolyline.remove();
        drawingPolyline = L.polyline(drawingPoints, { color: '#f59e0b', weight: 3, dashArray: '6 6' });

        const info = document.getElementById('fiberInfo');
        info.innerHTML = 'Fibra em desenho: <b>' + fmtMeters(drawingDistance) + '</b>' +
            '<span class="btn-group">' +
            '<button id="btnSaveFiber">Salvar</button>' +
            '<button id="btnUndoPoint">Desfazer</button>' +
            '<button id="btnCancelFiber" class="danger">Cancelar</button>' +
            '</span>';
        document.getElementById('btnSaveFiber').addEventListener('click', saveFiber);
        document.getElementById('btnUndoPoint').addEventListener('click', undoPoint);
        document.getElementById('btnCancelFiber').addEventListener('click', () => setMode('move'));
    }

    function saveFiber() {
        if (drawingPoints.length < 2) { showToast('Desenhe pelo menos 2 pontos.'); return; }
        const type = prompt('Tipo de fibra? (tronco, distribuicao, drop)', 'distribuicao');
        if (!['tronco', 'distribuicao', 'drop'].includes(type)) { showToast('Tipo inválido.'); return; }

        fetch(fiberStoreUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                city: currentCity,
                name: 'Fibra ' + type + ' ' + new Date().toLocaleDateString('pt-BR'),
                type: type,
                geometry: drawingPoints
            })
        })
            .then(r => r.json())
            .then(() => { showToast('Fibra salva (' + fmtMeters(drawingDistance) + ').'); loadData(); })
            .catch(err => showToast('Erro: ' + err.message))
            .finally(() => {
                if (drawingPolyline) drawingPolyline.remove();
                drawingPolyline = null;
                drawingPoints = [];
                drawingDistance = 0;
                setMode('move');
            });
    }

    setTimeout(() => { map.invalidateSize(); }, 200);
    window.addEventListener('sidebar-toggle', () => { map.invalidateSize(); });
    loadData();
})();
</script>
@endpush
@endsection