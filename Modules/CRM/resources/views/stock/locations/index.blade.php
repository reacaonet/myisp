@extends('core::layouts.master')

@section('title', 'Locais de Estoque')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Locais</h2>
        <a href="{{ route('crm.stock-locations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
            + Novo Local
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar..." class="flex-1 border rounded-lg px-3 py-2 text-sm">
                <select name="type" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="">Todos tipos</option>
                    <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Depósito</option>
                    <option value="technician" {{ request('type') == 'technician' ? 'selected' : '' }}>Técnico</option>
                </select>
                <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Buscar</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="px-4 py-3 font-medium">Nome</th>
                        <th class="px-4 py-3 font-medium">Tipo</th>
                        <th class="px-4 py-3 font-medium">Técnico</th>
                        <th class="px-4 py-3 font-medium">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $location->name }}</td>
                        <td class="px-4 py-3">
                            @if($location->type === 'deposit')
                                <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Depósito</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">Técnico</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $location->user->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('crm.stock-locations.edit', $location) }}" class="text-blue-600 hover:text-blue-800 text-xs">Editar</a>
                                <form method="POST" action="{{ route('crm.stock-locations.destroy', $location) }}" onsubmit="return confirm('Remover este local?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Remover</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhum local encontrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $locations->withQueryString()->links() }}</div>
    </div>
</div>
@endsection
