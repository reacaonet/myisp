@extends('core::layouts.master')

@section('title', 'CTOs - Rede FTTH')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">CTOs</h1>
        <p class="text-gray-500 text-sm">Caixas de Terminal Optico</p>
    </div>
    <a href="{{ route('ftth.ctos.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Nova CTO
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por codigo, nome ou rua..."
               class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg text-sm">
        <select name="city" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Todas Cidades</option>
            @foreach($cities as $city)
                <option value="{{ $city }}" @selected(request('city') == $city)>{{ $city }}</option>
            @endforeach
        </select>
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
            <option value="">Todos Status</option>
            <option value="active" @selected(request('status') == 'active')>Ativo</option>
            <option value="inactive" @selected(request('status') == 'inactive')>Inativo</option>
            <option value="maintenance" @selected(request('status') == 'maintenance')>Manutencao</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filtrar</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Codigo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cidade</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rua</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Caixa</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Capacidade</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Acoes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($ctos as $cto)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-mono font-medium text-gray-900">{{ $cto->code }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $cto->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $cto->city ?: '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $cto->street ?: '-' }}</td>
                <td class="px-4 py-3 text-sm">
                    @if($cto->caixa_emenda_id)
                        <a href="{{ route('ftth.caixas.show', $cto->caixa_emenda_id) }}" class="text-purple-600 hover:underline">{{ $cto->caixaEmenda->code }}</a>
                    @else
                        <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-gray-200 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $cto->usage_percent }}%"></div>
                        </div>
                        <span class="text-gray-500 text-xs">{{ $cto->used_ports }}/{{ $cto->capacity }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm">
                    @if($cto->status == 'active')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>
                    @elseif($cto->status == 'maintenance')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Manutencao</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inativa</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex items-center gap-1">
                        <a href="{{ route('ftth.ctos.show', $cto) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded" title="Ver">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('ftth.ctos.edit', $cto) }}" class="p-1.5 text-gray-400 hover:text-yellow-600 rounded" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-gray-400">Nenhuma CTO encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $ctos->withQueryString()->links() }}
    </div>
</div>
@endsection
