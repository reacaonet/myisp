@extends('core::layouts.master')

@section('title', 'Itens de Estoque')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Itens</h2>
        <a href="{{ route('crm.stock-items.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Novo Item
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome ou SKU..." class="flex-1 border rounded-lg px-3 py-2 text-sm">
                <select name="category" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Todas categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Buscar</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="px-4 py-3 font-medium">SKU</th>
                        <th class="px-4 py-3 font-medium">Nome</th>
                        <th class="px-4 py-3 font-medium">Categoria</th>
                        <th class="px-4 py-3 font-medium">Unidade</th>
                        <th class="px-4 py-3 font-medium">Estoque</th>
                        <th class="px-4 py-3 font-medium">Mínimo</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $item->sku }}</td>
                        <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $item->category->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->unit }}</td>
                        <td class="px-4 py-3 font-bold">{{ $item->totalStock() }}</td>
                        <td class="px-4 py-3">{{ $item->min_stock }}</td>
                        <td class="px-4 py-3">
                            @if($item->isLowStock())
                                <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Baixo</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">OK</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('crm.stock-items.show', $item) }}" class="text-green-600 hover:text-green-800 text-xs">Ver</a>
                                <a href="{{ route('crm.stock-items.edit', $item) }}" class="text-blue-600 hover:text-blue-800 text-xs">Editar</a>
                                <form method="POST" action="{{ route('crm.stock-items.destroy', $item) }}" onsubmit="return confirm('Remover este item?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Remover</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Nenhum item encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $items->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
