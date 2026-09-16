@extends('infra::layouts.master')

@section('title', 'Resultado da Geracao - Multi-Cidade')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Resultado da Geracao</h1>
            <p class="text-gray-500">{{ $stats['total_cities'] }} cidade(s) processada(s) com sucesso</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('infra.ftth.generate.cities') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Gerar Novamente</a>
            <a href="{{ route('infra.ftth.ctos.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Ver CTOs</a>
            <a href="{{ route('infra.ftth.caixas.index') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Ver Caixas</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_cities'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Cidades</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-purple-600">{{ $stats['total_ctos'] }}</p>
            <p class="text-xs text-gray-500 mt-1">CTOs Criadas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $stats['total_caixas'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Caixas Criadas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-orange-600">{{ number_format($stats['total_distance_km'], 1) }} km</p>
            <p class="text-xs text-gray-500 mt-1">Distancia Total</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-600">{{ $stats['total_streets'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Ruas Mapeadas</p>
        </div>
    </div>

    @if(!empty($errors))
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-yellow-800 mb-2">Avisos</h3>
        <ul class="list-disc list-inside text-sm text-yellow-700">
            @foreach($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
        $totalSkipped = collect($results)->sum(fn ($d) => $d['result']['stats']['skipped_out_of_bound'] ?? 0);
        $totalTooClose = collect($results)->sum(fn ($d) => $d['result']['stats']['skipped_too_close'] ?? 0);
    @endphp
    @if($totalSkipped > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-4">
        <p class="text-sm text-orange-700">{{ $totalSkipped }} posicoes de CTO fora do limite da cidade foram ignoradas.</p>
    </div>
    @endif
    @if($totalTooClose > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <p class="text-sm text-yellow-700">{{ $totalTooClose }} posicoes de CTO descartadas por estarem muito proximas de outra da mesma rua (menos do que o intervalo configurado).</p>
    </div>
    @endif

    @foreach($results as $cityData)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">{{ $cityData['city'] }}</h3>
                <p class="text-xs text-gray-500">{{ $cityData['result']['stats']['total_streets'] ?? 0 }} ruas | {{ number_format($cityData['result']['stats']['total_distance_km'], 1) }} km</p>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a href="{{ route('infra.ftth.projects.show', $cityData['project']) }}" class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-medium hover:bg-blue-200">{{ $cityData['project']->name }}</a>
                <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded font-medium">{{ $cityData['result']['stats']['total_ctos'] }} CTOs</span>
                <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">{{ $cityData['result']['stats']['total_caixas'] }} Caixas</span>
            </div>
        </div>

        @if(!empty($cityData['result']['caixas']))
        <div class="p-4 border-b border-gray-100">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Caixas de Emenda Criadas</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($cityData['result']['caixas'] as $caixa)
                <a href="{{ route('infra.ftth.caixas.show', $caixa) }}" class="flex items-center justify-between bg-green-50 rounded-lg p-2 text-xs hover:bg-green-100 transition-colors border border-green-200">
                    <span class="font-mono font-medium text-green-900">{{ $caixa->code }}</span>
                    <span class="text-green-600">{{ $caixa->street }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        @if(!empty($cityData['result']['ctos']))
        <div class="p-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">CTOs Geradas</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($cityData['result']['ctos'] as $cto)
                <a href="{{ route('infra.ftth.ctos.show', $cto) }}" class="flex items-center justify-between bg-gray-50 rounded-lg p-2 text-xs hover:bg-gray-100 transition-colors">
                    <div>
                        <span class="font-mono font-medium text-gray-800">{{ $cto->code }}</span>
                        <span class="text-gray-500"> &rarr; {{ $cto->caixaEmenda->code ?? 'sem caixa' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-500">
                        <span>{{ number_format($cto->distance_from_start ?? 0, 0) }} m</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endsection