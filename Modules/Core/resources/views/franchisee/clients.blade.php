@extends('core::layouts.master')

@section('title', 'Clientes da Franquia')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Clientes da Franquia</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $company?->name ?? 'Nenhuma empresa vinculada' }}
            </p>
        </div>
        <a href="{{ route('core.franchisee.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">
            Voltar ao painel
        </a>
    </div>

    <form method="GET" action="{{ route('core.franchisee.clients') }}" class="mb-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" value="{{ $search }}"
                       class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Nome, documento, e-mail, telefone ou login">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach(['active' => 'Ativo', 'inactive' => 'Inativo', 'suspended' => 'Suspenso', 'canceled' => 'Cancelado'] as $value => $label)
                        <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
                @if($search !== '' || $status)
                    <a href="{{ route('core.franchisee.clients') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Limpar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Nome</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Documento</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Contato</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Filial</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr class="border-b border-gray-100 last:border-0">
                        <td class="px-4 py-3 text-gray-900">
                            @if(Auth::user()?->hasPermission('clients'))
                                <a href="{{ route('crm.clients.show', $client->id) }}" class="text-blue-600 hover:underline">{{ $client->name }}</a>
                            @else
                                {{ $client->name }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $client->document ?: '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $client->cellphone ?: $client->phone ?: '-' }}
                            @if($client->email)
                                <span class="block text-xs text-gray-400">{{ $client->email }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $client->branch?->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $client->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $client->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Nenhum cliente encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</div>
@endsection