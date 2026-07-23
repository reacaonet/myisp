@extends('core::layouts.master')

@section('title', 'CTO ' . $cto->code)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">{{ $cto->code }} - {{ $cto->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $cto->full_address }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($cto->status == 'active')
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>
                @elseif($cto->status == 'maintenance')
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Manutencao</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inativa</span>
                @endif
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Caixa de Emenda</p>
                    @if($cto->caixa_emenda_id)
                        <a href="{{ route('ftth.caixas.show', $cto->caixa_emenda_id) }}" class="text-purple-600 hover:underline font-medium">{{ $cto->caixaEmenda->code }}</a>
                    @else
                        <p class="text-gray-400">Nenhuma</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Capacidade</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $cto->usage_percent }}%"></div>
                        </div>
                        <span class="text-sm font-medium">{{ $cto->used_ports }}/{{ $cto->capacity }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Latitude</p>
                    <p class="text-sm font-mono">{{ $cto->latitude }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Longitude</p>
                    <p class="text-sm font-mono">{{ $cto->longitude }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-gray-500 uppercase">Distancia percorrida</p>
                <p class="text-sm font-medium">{{ number_format($cto->distance_from_start, 2, ',', '.') }} m</p>
            </div>

            @if($cto->notes)
            <div>
                <p class="text-xs text-gray-500 uppercase">Observacoes</p>
                <p class="text-sm text-gray-700">{{ $cto->notes }}</p>
            </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('ftth.ctos.edit', $cto) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600">Editar</a>
                <a href="{{ route('ftth.ctos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Voltar</a>
            </div>
        </div>
    </div>
</div>
@endsection
