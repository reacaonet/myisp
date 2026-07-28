@extends('core::layouts.master')

@section('title', $item->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $item->name }}</h2>
            <p class="text-gray-500">SKU: {{ $item->sku }} | Categoria: {{ $item->category->name ?? '-' }} | Unidade: {{ $item->unit }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('crm.stock-items.edit', $item) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Editar</a>
            <a href="{{ route('crm.stock-items.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Voltar</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Estoque Total</p>
            <p class="text-3xl font-bold {{ $item->isLowStock() ? 'text-red-600' : 'text-green-600' }}">{{ $item->totalStock() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Estoque Mínimo</p>
            <p class="text-3xl font-bold text-gray-800">{{ $item->min_stock }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Status</p>
            @if($item->isLowStock())
                <span class="px-3 py-1 rounded-full text-sm bg-red-100 text-red-800">Estoque Baixo</span>
            @else
                <span class="px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">Estoque OK</span>
            @endif
        </div>
    </div>

    @if($item->balances->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Estoque por Local</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-medium">Local</th>
                        <th class="pb-2 font-medium">Tipo</th>
                        <th class="pb-2 font-medium">Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item->balances as $balance)
                    <tr class="border-b">
                        <td class="py-2">{{ $balance->location->name }}</td>
                        <td class="py-2">{{ $balance->location->type === 'deposit' ? 'Depósito' : 'Técnico' }}</td>
                        <td class="py-2 font-bold">{{ $balance->quantity }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Histórico de Movimentações</h3>
        @if($movements->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="pb-2 font-medium">Data</th>
                        <th class="pb-2 font-medium">Tipo</th>
                        <th class="pb-2 font-medium">Qtd</th>
                        <th class="pb-2 font-medium">Local</th>
                        <th class="pb-2 font-medium">Ref</th>
                        <th class="pb-2 font-medium">Responsável</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $m)
                    <tr class="border-b">
                        <td class="py-2">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2">{{ $m->type_label }}</td>
                        <td class="py-2">{{ $m->quantity }}</td>
                        <td class="py-2">{{ $m->location->name }}</td>
                        <td class="py-2 text-gray-500">{{ $m->reference ?? '-' }}</td>
                        <td class="py-2">{{ $m->performer->name ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $movements->links() }}</div>
        @else
        <p class="text-gray-400 text-center py-4">Nenhuma movimentação registrada.</p>
        @endif
    </div>
</div>
@endsection
