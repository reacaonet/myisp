@extends('core::layouts.master')

@section('title', 'Estoque - Dashboard')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-800">Dashboard do Estoque</h2>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total de Itens</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalItems }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Categorias</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalCategories }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Locais</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalLocations }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Unidades em Estoque</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalValue }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($lowStockItems->count() > 0)
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-800 mb-4">Itens com Estoque Baixo</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-red-700 border-b border-red-200">
                        <th class="pb-2 font-medium">SKU</th>
                        <th class="pb-2 font-medium">Nome</th>
                        <th class="pb-2 font-medium">Categoria</th>
                        <th class="pb-2 font-medium">Estoque Atual</th>
                        <th class="pb-2 font-medium">Estoque Mínimo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockItems as $item)
                    <tr class="border-b border-red-100">
                        <td class="py-2 text-red-700">{{ $item->sku }}</td>
                        <td class="py-2 text-red-700">{{ $item->name }}</td>
                        <td class="py-2 text-red-700">{{ $item->category->name ?? '-' }}</td>
                        <td class="py-2 text-red-700 font-bold">{{ $item->totalStock() }}</td>
                        <td class="py-2 text-red-700">{{ $item->min_stock }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Últimas Movimentações</h3>
        @if($recentMovements->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-medium">Data</th>
                        <th class="pb-2 font-medium">Item</th>
                        <th class="pb-2 font-medium">Tipo</th>
                        <th class="pb-2 font-medium">Qtd</th>
                        <th class="pb-2 font-medium">Local</th>
                        <th class="pb-2 font-medium">Responsável</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentMovements as $movement)
                    <tr class="border-b">
                        <td class="py-2">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2">{{ $movement->item->name }}</td>
                        <td class="py-2">
                            @if($movement->type === 'entry')
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Entrada</span>
                            @elseif($movement->type === 'exit')
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Saída</span>
                            @elseif($movement->type === 'return')
                                <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Devolução</span>
                            @elseif($movement->type === 'transfer')
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Transferência</span>
                            @elseif($movement->type === 'installation')
                                <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">Instalação</span>
                            @endif
                        </td>
                        <td class="py-2">{{ $movement->quantity }}</td>
                        <td class="py-2">{{ $movement->location->name }}</td>
                        <td class="py-2">{{ $movement->performer->name ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-center py-4">Nenhuma movimentação registrada.</p>
        @endif
    </div>
</div>
@endsection
