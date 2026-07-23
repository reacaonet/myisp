@extends('core::layouts.master')

@section('title', 'Rede FTTH - Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Rede FTTH</h1>
    <p class="text-gray-500 text-sm mt-1">Gestao de infraestrutura optica - CTOs e Caixas de Emenda</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500 uppercase font-medium">CTOs Total</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_ctos'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500 uppercase font-medium">CTOs Ativas</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active_ctos'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500 uppercase font-medium">Caixas de Emenda</p>
        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_caixas'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500 uppercase font-medium">Caixas Ativas</p>
        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['active_caixas'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500 uppercase font-medium">Capacidade Total</p>
        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['total_capacity'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <p class="text-xs text-gray-500 uppercase font-medium">Portas Usadas</p>
        <p class="text-2xl font-bold text-orange-600 mt-1">{{ $stats['total_used'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Ultimas CTOs</h2>
            <a href="{{ route('ftth.ctos.index') }}" class="text-sm text-blue-600 hover:underline">Ver todas</a>
        </div>
        <div class="space-y-2">
            @forelse($recentCtos as $cto)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div>
                    <span class="font-medium text-sm text-gray-900">{{ $cto->code }}</span>
                    <span class="text-xs text-gray-500 ml-2">{{ $cto->street }}</span>
                </div>
                <div class="flex items-center gap-2">
                    @if($cto->caixa_emenda_id)
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">{{ $cto->caixaEmenda->code }}</span>
                    @endif
                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $cto->usage_percent }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $cto->used_ports }}/{{ $cto->capacity }}</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Nenhuma CTO cadastrada.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Ultimas Caixas de Emenda</h2>
            <a href="{{ route('ftth.caixas.index') }}" class="text-sm text-blue-600 hover:underline">Ver todas</a>
        </div>
        <div class="space-y-2">
            @forelse($recentCaixas as $caixa)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div>
                    <span class="font-medium text-sm text-gray-900">{{ $caixa->code }}</span>
                    <span class="text-xs text-gray-500 ml-2">{{ $caixa->street }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{{ $caixa->ctos_count }} CTOs</span>
                    <div class="w-16 bg-gray-200 rounded-full h-1.5">
                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $caixa->usage_percent }}%"></div>
                    </div>
                    <span class="text-xs text-gray-500">{{ $caixa->used_ports }}/{{ $caixa->capacity }}</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">Nenhuma caixa de emenda cadastrada.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Gerar por Cidade</h2>
        <p class="text-sm text-gray-500 mb-4">Informe o nome da cidade e o sistema busca todas as ruas automaticamente no OpenStreetMap.</p>
        <a href="{{ route('ftth.generate.city') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Gerar por Cidade
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Exportar KML</h2>
        <p class="text-sm text-gray-500 mb-4">Exporte a rede de uma cidade como arquivo KML para visualizar no Google Earth.</p>
        <a href="{{ route('ftth.export.kml') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Exportar KML
        </a>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-2">Gerar Manual</h2>
        <p class="text-sm text-gray-500 mb-4">Insira coordenadas manualmente (lat, lng por linha) para gerar uma unica rua.</p>
        <a href="{{ route('ftth.generate') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Gerar Manual
        </a>
    </div>
</div>
@endsection
