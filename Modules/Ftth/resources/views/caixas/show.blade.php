@extends('core::layouts.master')

@section('title', 'Caixa ' . $caixa->code)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">{{ $caixa->code }} - {{ $caixa->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $caixa->full_address }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($caixa->status == 'active')
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>
                @elseif($caixa->status == 'maintenance')
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Manutencao</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inativa</span>
                @endif
            </div>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">CTOs Vinculadas</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $caixa->ctos_count }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Capacidade</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $caixa->usage_percent }}%"></div>
                        </div>
                        <span class="text-sm font-medium">{{ $caixa->used_ports }}/{{ $caixa->capacity }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Latitude</p>
                    <p class="text-sm font-mono">{{ $caixa->latitude }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase">Longitude</p>
                    <p class="text-sm font-mono">{{ $caixa->longitude }}</p>
                </div>
            </div>

            @if($caixa->notes)
            <div>
                <p class="text-xs text-gray-500 uppercase">Observacoes</p>
                <p class="text-sm text-gray-700">{{ $caixa->notes }}</p>
            </div>
            @endif

            @if($caixa->ctos->count() > 0)
            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">CTOs Vinculadas</h3>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($caixa->ctos as $cto)
                    <a href="{{ route('ftth.ctos.show', $cto) }}" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div>
                            <span class="font-mono font-medium text-sm text-gray-900">{{ $cto->code }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $cto->street }}</span>
                        </div>
                        <div class="w-16 bg-gray-200 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $cto->usage_percent }}%"></div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <form method="POST" action="{{ route('ftth.caixas.destroy', $caixa) }}" onsubmit="return confirm('Excluir esta Caixa de Emenda? Esta acao nao pode ser desfeita.')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600">Excluir</button>
                </form>
                <a href="{{ route('ftth.caixas.edit', $caixa) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600">Editar</a>
                <a href="{{ route('ftth.caixas.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Voltar</a>
            </div>
        </div>
    </div>
</div>
@endsection
