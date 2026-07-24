@extends('core::layouts.master')

@section('title', 'CTO ' . $cto->code)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('ftth.ctos.index') }}" class="inline-flex items-center gap-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Voltar
            </a>
            <h1 class="text-xl font-bold text-gray-900">{{ $cto->code }} - {{ $cto->name }}</h1>
            @if($cto->status == 'active')
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>
            @elseif($cto->status == 'maintenance')
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Manutencao</span>
            @else
                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inativa</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('ftth.ctos.edit', $cto) }}" class="inline-flex items-center gap-1 px-4 py-2 text-white rounded-lg text-sm font-medium" style="background-color:#2563eb;" onmouseover="this.style.backgroundColor='#1d4ed8'" onmouseout="this.style.backgroundColor='#2563eb'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Editar
            </a>
            <form method="POST" action="{{ route('ftth.ctos.destroy', $cto) }}" onsubmit="return confirm('Excluir esta CTO? Esta acao nao pode ser desfeita.');">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 px-4 py-2 text-white rounded-lg text-sm font-medium" style="background-color:#dc2626;" onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Excluir
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Caixa de Emenda</p>
                    @if($cto->caixa_emenda_id && $cto->caixaEmenda)
                        <a href="{{ route('ftth.caixas.show', $cto->caixa_emenda_id) }}" class="text-purple-600 hover:underline font-medium text-sm">{{ $cto->caixaEmenda->code }}</a>
                    @else
                        <p class="text-gray-400 text-sm">Nenhuma</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Capacidade</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $cto->usage_percent }}%"></div>
                        </div>
                        <span class="text-sm font-medium">{{ $cto->used_ports }}/{{ $cto->capacity }}</span>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Latitude</p>
                    <p class="text-sm font-mono text-gray-900">{{ $cto->latitude }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Longitude</p>
                    <p class="text-sm font-mono text-gray-900">{{ $cto->longitude }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Distancia</p>
                    <p class="text-sm font-medium text-gray-900">{{ number_format($cto->distance_from_start, 2, ',', '.') }} m</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Cidade</p>
                    <p class="text-sm text-gray-900">{{ $cto->city ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Rua</p>
                    <p class="text-sm text-gray-900">{{ $cto->street ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium">Status</p>
                    <p class="text-sm text-gray-900">{{ ucfirst($cto->status) }}</p>
                </div>
            </div>

            @if($cto->notes)
            <div class="border-t border-gray-100 pt-4">
                <p class="text-xs text-gray-500 uppercase font-medium mb-1">Observacoes</p>
                <p class="text-sm text-gray-700">{{ $cto->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
