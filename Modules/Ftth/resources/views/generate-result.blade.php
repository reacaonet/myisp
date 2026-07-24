@extends('core::layouts.master')

@section('title', 'Rede Gerada - ' . $street_name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Rede Gerada: {{ $street_name }}</h2>
                <p class="text-sm text-gray-500 mt-1">Gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
            <a href="{{ route('ftth.generate') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Gerar Nova Rede</a>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-blue-600">{{ $result['stats']['total_ctos'] }}</p>
                    <p class="text-sm text-blue-700 mt-1">CTOs Criadas</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-green-600">{{ $result['stats']['total_caixas'] }}</p>
                    <p class="text-sm text-green-700 mt-1">Caixas de Emenda</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($result['stats']['total_distance_km'], 2, ',', '.') }} m</p>
                    <p class="text-sm text-purple-700 mt-1">Distancia Total</p>
                </div>
                @if(isset($result['stats']['total_streets']))
                <div class="bg-orange-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-orange-600">{{ $result['stats']['total_streets'] }}</p>
                    <p class="text-sm text-orange-700 mt-1">Ruas Mapeadas</p>
                </div>
                @endif
            </div>

            @if(count($result['caixas']) > 0)
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Caixas de Emenda Criadas</h3>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($result['caixas'] as $caixa)
                    <a href="{{ route('ftth.caixas.show', $caixa) }}" class="flex items-center justify-between p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors border border-green-200">
                        <div>
                            <span class="font-mono font-medium text-sm text-green-900">{{ $caixa->code }}</span>
                            <span class="text-xs text-green-600 ml-2">{{ $caixa->name }}</span>
                        </div>
                        <span class="text-xs text-green-600">{{ $caixa->ctos_count }} CTOs</span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if(count($result['ctos']) > 0)
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">CTOs Criadas</h3>
                <div class="bg-gray-50 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Codigo</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Latitude</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Longitude</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Distancia</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500">Caixa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($result['ctos'] as $cto)
                            <tr class="hover:bg-gray-100">
                                <td class="px-3 py-2 font-mono font-medium">{{ $cto->code }}</td>
                                <td class="px-3 py-2 font-mono text-gray-600">{{ $cto->latitude }}</td>
                                <td class="px-3 py-2 font-mono text-gray-600">{{ $cto->longitude }}</td>
                                <td class="px-3 py-2 text-gray-600">{{ number_format($cto->distance_from_start, 1, ',', '.') }} m</td>
                                <td class="px-3 py-2">
                                    @if($cto->caixa_emenda_id && $cto->caixaEmenda)
                                        <a href="{{ route('ftth.caixas.show', $cto->caixa_emenda_id) }}" class="text-purple-600 hover:underline">{{ $cto->caixaEmenda->code }}</a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(count($result['ctos']) == 0)
            <div class="text-center py-8 text-gray-400">
                <p>Nenhuma CTO foi gerada. Verifique as coordenadas.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
