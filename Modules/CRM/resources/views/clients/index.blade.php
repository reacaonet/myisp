@extends('core::layouts.master')

@section('title', 'Clientes')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Clientes</h2>
        <div class="flex gap-3">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Buscar cliente..." value="{{ request('search') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Buscar</button>
            </form>
            <a href="{{ route('crm.clients.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo Cliente
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">Documento</th>
                    <th class="px-6 py-4 font-medium">Email</th>
                    <th class="px-6 py-4 font-medium">Celular</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('crm.clients.show', $client) }}" class="text-blue-600 hover:underline font-medium">{{ $client->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->document }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->email ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->cellphone ?? '-' }}</td>
                    <td class="px-6 py-4">@include('crm::clients._status_badge', ['status' => $client->status])</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <a href="{{ route('crm.clients.show', $client) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Visualizar</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <a href="{{ route('crm.clients.edit', $client) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Editar</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <form method="POST" action="{{ route('crm.clients.destroy', $client) }}" onsubmit="return confirm('Remover cliente {{ $client->name }}?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                        Nenhum cliente encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())
    <div class="p-6 border-t border-gray-200">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
