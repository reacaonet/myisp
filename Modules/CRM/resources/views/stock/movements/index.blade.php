@extends('core::layouts.master')

@section('title', 'Movimentações de Estoque')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Movimentações</h2>
        <a href="{{ route('crm.stock-movements.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Nova Movimentação
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar item..." class="flex-1 border rounded-lg px-3 py-2 text-sm">
                <select name="type" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos tipos</option>
                    <option value="entry" {{ request('type') == 'entry' ? 'selected' : '' }}>Entrada</option>
                    <option value="exit" {{ request('type') == 'exit' ? 'selected' : '' }}>Saída</option>
                    <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Devolução</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transferência</option>
                    <option value="installation" {{ request('type') == 'installation' ? 'selected' : '' }}>Instalação</option>
                </select>
                <select name="location" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos locais</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Filtrar</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="px-4 py-3 font-medium">Data</th>
                        <th class="px-4 py-3 font-medium">Item</th>
                        <th class="px-4 py-3 font-medium">Tipo</th>
                        <th class="px-4 py-3 font-medium">Qtd</th>
                        <th class="px-4 py-3 font-medium">Local</th>
                        <th class="px-4 py-3 font-medium">Ref</th>
                        <th class="px-4 py-3 font-medium">Responsável</th>
                        <th class="px-4 py-3 font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $movement->item->name }}</td>
                        <td class="px-4 py-3">
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
                        <td class="px-4 py-3">{{ $movement->quantity }}</td>
                        <td class="px-4 py-3">{{ $movement->location->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $movement->reference ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $movement->performer->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('crm.stock-movements.show', $movement) }}" class="text-green-600 hover:text-green-800 text-xs">Ver</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-400">Nenhuma movimentação encontrada.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $movements->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
