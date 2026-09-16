@extends('core::layouts.master')

@section('title', 'Banners da Landing')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Banners da Landing Page</h2>
        <div class="flex gap-3">
            <a href="{{ route('landing.index') }}" target="_blank" class="px-4 py-2 text-sm font-medium text-blue-600 rounded-lg hover:bg-blue-50">Ver site &#8599;</a>
            <a href="{{ route('core.banners.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo Banner
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 m-6">{{ session('success') }}</div>
    @endif

    <div class="p-6">
        @if($banners->isEmpty())
            <p class="text-center text-gray-400 py-12">Nenhum banner cadastrado. Crie o primeiro para exibir o slider.</p>
        @else
        <div class="space-y-3">
            @foreach($banners as $banner)
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 border border-gray-200 rounded-xl p-4 {{ !$banner->is_active ? 'opacity-60' : '' }}">
                <div class="w-full sm:w-44 h-24 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center shrink-0">
                    @if($banner->image)
                        <img src="{{ asset('storage/'.$banner->image) }}" alt="{{ $banner->title }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-2xl">🖼️</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-gray-900">{{ $banner->title }}</span>
                        @if($banner->badge)
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">{{ $banner->badge }}</span>
                        @endif
                        @if($banner->is_active)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Ativo</span>
                        @else
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">Inativo</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $banner->subtitle }}</p>
                    <p class="text-xs text-gray-400 mt-1">Ordem: {{ $banner->sort_order }} @if($banner->link_url) | {{ $banner->link_url }} @endif</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex flex-col gap-1">
                        @if(!$loop->first)
                        <form method="POST" action="{{ route('core.banners.move', ['id' => $banner->id, 'direction' => 'up']) }}" class="inline">
                            @csrf
                            <button type="submit" title="Mover para cima" class="p-1.5 rounded hover:bg-gray-100 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg></button>
                        </form>
                        @endif
                        @if(!$loop->last)
                        <form method="POST" action="{{ route('core.banners.move', ['id' => $banner->id, 'direction' => 'down']) }}" class="inline">
                            @csrf
                            <button type="submit" title="Mover para baixo" class="p-1.5 rounded hover:bg-gray-100 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></button>
                        </form>
                        @endif
                    </div>
                    <a href="{{ route('core.banners.edit', $banner) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                    <form method="POST" action="{{ route('core.banners.destroy', $banner) }}" onsubmit="return confirm('Remover banner {{ $banner->title }}?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-4">Use as setas para reordenar os banners de exibicao no slider da landing.</p>
        @endif
    </div>
</div>
@endsection