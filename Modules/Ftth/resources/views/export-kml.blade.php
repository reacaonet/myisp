@extends('core::layouts.master')

@section('title', 'Exportar KML/KMZ')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Exportar Rede FTTH - KML</h2>
            <p class="text-sm text-gray-500 mt-1">Exporte a rede de CTOs e Caixas de Emenda como arquivo KML para visualizar no Google Earth.</p>
        </div>

        <div class="p-6">
            @if($cities->isEmpty())
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-gray-400">Nenhuma rede FTTH gerada ainda.</p>
                <a href="{{ route('ftth.generate.city') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Gerar Rede Primeiro
                </a>
            </div>
            @else
            <p class="text-sm text-gray-600 mb-4">Selecione a cidade para exportar o arquivo KML:</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($cities as $city)
                <a href="{{ route('ftth.export.kml.download', $city) }}"
                   class="flex items-center justify-between p-4 border-2 border-gray-200 rounded-lg hover:border-green-400 hover:bg-green-50 transition-all group">
                    <div>
                        <p class="font-medium text-gray-900">{{ $city }}</p>
                        <p class="text-xs text-gray-500">
                            @php
                                $ctoCount = \Modules\Ftth\Models\Cto::where('city', $city)->count();
                                $caixaCount = \Modules\Ftth\Models\CaixaEmenda::where('city', $city)->count();
                            @endphp
                            {{ $ctoCount }} CTOs | {{ $caixaCount }} Caixas
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-green-600 group-hover:text-green-700">
                        <span class="text-sm font-medium">KML</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Como usar</p>
                        <p class="text-xs text-blue-700 mt-1">Abra o arquivo .kml no Google Earth Pro ou importe no Google Maps. CTOs aparecem em vermelho, Caixas de Emenda em verde. Clique em cada ponto para ver detalhes.</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
