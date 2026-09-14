@extends('crm::technician.layouts.master')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 350px; max-height: 500px; border-radius: 10px; z-index: 0; }
    .marker-cto { background: #ef4444; width: 14px; height: 14px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 1px 4px rgba(0,0,0,.4); }
</style>
@endpush

@section('title', $cto->code)

@section('content')
<div class="mb-4">
    <a href="{{ route('technician.portal.ftth') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Rede FTTH
    </a>
</div>

<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-bold text-gray-900">{{ $cto->code }} - {{ $cto->name }}</h1>
        @if($cto->status === 'active')
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>
        @elseif($cto->status === 'maintenance')
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Manutencao</span>
        @else
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ ucfirst($cto->status) }}</span>
        @endif
    </div>
    @if($cto->status !== 'active')
    <form method="POST" action="{{ route('technician.portal.ftth.ctos.activate', $cto) }}" onsubmit="return confirm('Ativar esta CTO? O escritorio verá a alteracao.');">
        @csrf
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Ativar CTO
        </button>
    </form>
    @endif
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div id="map"></div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4">Dados da CTO</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><p class="text-gray-400">Projeto</p><p class="font-medium text-gray-900">{{ $cto->ftthProject?->name ?: '-' }}</p></div>
                <div><p class="text-gray-400">Porta da OLT</p><p class="font-mono font-medium text-gray-900">{{ $cto->olt_port ?: '-' }}</p></div>
                <div><p class="text-gray-400">Splitter</p><p class="font-medium text-gray-900">{{ $cto->splitter_config ?: '-' }}</p></div>
                <div><p class="text-gray-400">Portas</p><p class="font-medium text-gray-900">{{ $cto->used_ports }}/{{ $cto->capacity }}</p></div>
                <div class="col-span-2"><p class="text-gray-400">Endereco</p><p class="font-medium text-gray-900">{{ $cto->full_address }}</p></div>
                <div><p class="text-gray-400">Latitude</p><p class="font-mono text-gray-900">{{ $cto->latitude }}</p></div>
                <div><p class="text-gray-400">Longitude</p><p class="font-mono text-gray-900">{{ $cto->longitude }}</p></div>
            </div>
            @if($cto->project_notes)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400 mb-1">Observacoes do Projeto (escritorio)</p>
                <p class="text-sm text-gray-700">{{ $cto->project_notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        @if($cto->caixaEmenda)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Caixa de Emenda</h3>
            <a href="{{ route('technician.portal.ftth.caixas.show', $cto->caixaEmenda) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                <div>
                    <span class="font-mono font-medium text-sm text-gray-900">{{ $cto->caixaEmenda->code }}</span>
                    <span class="text-xs text-gray-500 ml-2">{{ $cto->caixaEmenda->street }}</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $cto->caixaEmenda->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($cto->caixaEmenda->status) }}</span>
            </a>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Progresso das Fusoes</h3>
            @if($cto->fusions_count > 0)
            <div class="flex items-center gap-4 mb-3">
                <span class="text-3xl font-bold text-green-600">{{ $doneCount }}</span>
                <span class="text-gray-300 text-xl">/</span>
                <span class="text-3xl font-bold text-gray-900">{{ $cto->fusions_count }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                <div class="bg-green-500 h-3 rounded-full transition-all" style="width: {{ $cto->fusions_count > 0 ? round(($doneCount / $cto->fusions_count) * 100) : 0 }}%"></div>
            </div>
            <p class="text-xs text-gray-400">{{ $cto->fusions_count > 0 ? round(($doneCount / $cto->fusions_count) * 100) : 0 }}% concluido</p>
            @if($pendingCount === 0)
            <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-sm font-medium text-green-700">Todas as fusoes executadas!</p>
            </div>
            @endif
            @else
            <p class="text-sm text-gray-400">Nenhuma fusao no plano.</p>
            @endif
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Plano de Fusao</h3>
    @if($cto->fusions->isEmpty())
        <p class="text-sm text-gray-400">Nenhuma fusao registrada.</p>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b border-gray-200">
                    <th class="pb-2 font-medium">Fibra</th>
                    <th class="pb-2 font-medium">Porta OLT</th>
                    <th class="pb-2 font-medium">Tubo</th>
                    <th class="pb-2 font-medium">Destino</th>
                    <th class="pb-2 font-medium">Status</th>
                    <th class="pb-2 font-medium text-right">Acao</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($cto->fusions as $fusion)
                <tr class="{{ $fusion->status === 'done' ? 'bg-green-50/50' : '' }}">
                    <td class="py-2 font-medium text-gray-900">{{ $fusion->fiber_number ? 'F' . $fusion->fiber_number : '-' }}</td>
                    <td class="py-2 font-mono text-gray-600">{{ $fusion->olt_port ?: '-' }}</td>
                    <td class="py-2 text-gray-600">{{ $fusion->tube ?: '-' }}</td>
                    <td class="py-2 text-gray-600">{{ $fusion->destination ?: '-' }}</td>
                    <td class="py-2">
                        @if($fusion->status === 'done')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Executada</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Pendente</span>
                        @endif
                    </td>
                    <td class="py-2 text-right">
                        @if($fusion->status === 'pending')
                        <form method="POST" action="{{ route('technician.portal.ftth.fusions.done', $fusion) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-green-600 hover:underline">Marcar feito</button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">OK</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-3">Observacoes do Tecnico</h3>
    <p class="text-xs text-gray-400 mb-3">Notas visiveis ao escritorio. Use para registrar observacoes sobre a instalacao em campo.</p>
    <form method="POST" action="{{ route('technician.portal.ftth.ctos.notes', $cto) }}">
        @csrf
        <textarea name="technician_notes" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: fibra 3 rompida, necessita reposicao...">{{ $cto->technician_notes }}</textarea>
        <div class="mt-2 text-right">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar observacoes</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
    var map = L.map('map', { zoomControl: true }).setView([{{ $cto->latitude }}, {{ $cto->longitude }}], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);
    var icon = L.divIcon({ className: 'marker-cto', iconSize: [14, 14], iconAnchor: [7, 7] });
    L.marker([{{ $cto->latitude }}, {{ $cto->longitude }}], { icon })
        .addTo(map)
        .bindPopup('<b>{{ $cto->code }}</b><br>{{ $cto->full_address }}')
        .openPopup();
    setTimeout(function() { map.invalidateSize(); }, 200);
})();
</script>
@endpush