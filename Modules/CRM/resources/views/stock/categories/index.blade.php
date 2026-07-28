@extends('core::layouts.master')

@section('title', 'Categorias de Estoque')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Categorias</h2>
        <a href="{{ route('crm.stock-categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Nova Categoria
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..." class="flex-1 border rounded-lg px-3 py-2 text-sm">
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Buscar</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="px-4 py-3 font-medium">Nome</th>
                        <th class="px-4 py-3 font-medium">Descrição</th>
                        <th class="px-4 py-3 font-medium">Itens</th>
                        <th class="px-4 py-3 font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->description ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $category->items_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('crm.stock-categories.edit', $category) }}" class="text-blue-600 hover:text-blue-800 text-xs">Editar</a>
                                <form method="POST" action="{{ route('crm.stock-categories.destroy', $category) }}" onsubmit="return confirm('Remover esta categoria?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Remover</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhuma categoria encontrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $categories->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
