@extends('core::layouts.master')

@section('title', 'Fornecedores')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Fornecedores</h2>
        <a href="{{ route('crm.suppliers.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Novo Fornecedor</a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex gap-4 items-end">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, empresa, documento..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Categoria</label>
            <select name="category" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todas</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Nome</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Empresa</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Contato</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Categoria</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $supplier->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $supplier->company_name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $supplier->phone ?? $supplier->email ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $supplier->category ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($supplier->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativo</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Inativo</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('crm.suppliers.show', $supplier) }}" class="text-blue-600 hover:text-blue-800 text-xs">Ver</a>
                            <a href="{{ route('crm.suppliers.edit', $supplier) }}" class="text-blue-600 hover:text-blue-800 text-xs">Editar</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhum fornecedor encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $suppliers->withQueryString()->links() }}</div>
</div>
@endsection
