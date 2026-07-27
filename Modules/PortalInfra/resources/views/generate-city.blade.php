@extends('infra::layouts.master')

@section('title', 'Gerar Rede por Cidade')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Gerar Rede Automatica por Cidade</h2>
            <p class="text-sm text-gray-500 mt-1">O sistema busca todas as ruas da cidade no OpenStreetMap (Overpass API) e gera CTOs a cada 250m automaticamente.</p>
        </div>

        <div class="p-6 space-y-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-blue-800 mb-2">Como funciona?</h3>
                <ol class="text-xs text-blue-700 space-y-1 list-decimal list-inside">
                    <li>Voce informa o nome da cidade e o estado</li>
                    <li>O sistema consulta a Overpass API (OpenStreetMap) e baixa todas as ruas residenciais, principais e secundarias</li>
                    <li>Percore cada rua calculando distancia com Haversine</li>
                    <li>A cada 250m, cria uma CTO nessa posicao</li>
                    <li>A cada 4 CTOs, cria uma Caixa de Emenda no centro geografico</li>
                    <li>As CTOs sao vinculadas automaticamente a caixa mais proxima</li>
                </ol>
            </div>

            <form method="POST" action="{{ route('infra.ftth.generate.city.run') }}" id="cityForm">
                @csrf

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Cidade *</label>
                        <input type="text" name="city_name" value="{{ old('city_name') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Florianopolis">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado (UF)</label>
                        <input type="text" name="state" value="{{ old('state') }}" maxlength="2"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="SC">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prefixo CTO (opcional)</label>
                        <input type="text" name="prefix" value="{{ old('prefix') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="FLN">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo CTOs (metros)</label>
                        <input type="number" name="interval" value="{{ old('interval', 250) }}" min="50" max="1000"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Atencao</p>
                            <p class="text-xs text-yellow-700 mt-1">Cidades grandes podem gerar milhares de CTOs. O processo pode levar alguns minutos. Recomendamos comecar por bairros especificos usando o gerador manual.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Ou use coordenadas de limite (bounding box)</h3>
                    <p class="text-xs text-gray-500 mb-3">Para gerar apenas uma regiao especifica, preencha as coordenadas:</p>
                    <div class="grid grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Sul (lat)</label>
                            <input type="number" step="0.0001" name="south" value="{{ old('south') }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm" placeholder="-27.65">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Oeste (lng)</label>
                            <input type="number" step="0.0001" name="west" value="{{ old('west') }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm" placeholder="-48.55">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Norte (lat)</label>
                            <input type="number" step="0.0001" name="north" value="{{ old('north') }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm" placeholder="-27.50">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Leste (lng)</label>
                            <input type="number" step="0.0001" name="east" value="{{ old('east') }}"
                                   class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm" placeholder="-48.40">
                        </div>
                    </div>
                </div>

                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <ul class="list-disc list-inside text-sm text-red-600">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('infra.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                    <button type="submit" id="submitBtn" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Gerar Rede da Cidade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('cityForm').addEventListener('submit', function() {
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Buscando ruas na Overpass API...';
});
</script>
@endpush
@endsection
