@extends('core::layouts.master')

@section('title', 'Clientes')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Clientes</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $clients->total() }} {{ $clients->total() === 1 ? 'cliente' : 'clientes' }} na rede</p>
        </div>
        <a href="{{ route('crm.clients.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Novo Cliente
        </a>
    </div>

    <form method="GET" class="p-6 border-b border-gray-200 bg-gray-50/60 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="sm:col-span-2 lg:col-span-4">
                <label class="block text-xs font-medium text-gray-600 mb-1">Busca</label>
                <input type="text" name="search" value="{{ $filters['search'] }}"
                       placeholder="Nome, documento, e-mail, telefone, celular, login ou codigo"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Filial</label>
                <select name="branch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todas as filiais</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($filters['branch_id'] === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="active" @selected($filters['status'] === 'active')>Ativo</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Inativo</option>
                    <option value="suspended" @selected($filters['status'] === 'suspended')>Suspenso</option>
                    <option value="canceled" @selected($filters['status'] === 'canceled')>Cancelado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Pessoa</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="individual" @selected($filters['type'] === 'individual')>Fisica</option>
                    <option value="legal" @selected($filters['type'] === 'legal')>Juridica</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Assinante</label>
                <select name="tipo_assinante" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="pf" @selected($filters['tipo_assinante'] === 'pf')>PF</option>
                    <option value="pj" @selected($filters['tipo_assinante'] === 'pj')>PJ</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Utilizacao</label>
                <select name="tipo_utilizacao" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todas</option>
                    <option value="residencial" @selected($filters['tipo_utilizacao'] === 'residencial')>Residencial</option>
                    <option value="comercial" @selected($filters['tipo_utilizacao'] === 'comercial')>Comercial</option>
                    <option value="institucional" @selected($filters['tipo_utilizacao'] === 'institucional')>Institucional</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Grupo</label>
                <select name="grupo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    @foreach($groups as $group)
                        <option value="{{ $group }}" @selected($filters['grupo'] === $group)>{{ $group }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Contrato</label>
                <select name="contract" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Todos</option>
                    <option value="active" @selected($filters['contract'] === 'active')>Com contrato ativo</option>
                    <option value="without" @selected($filters['contract'] === 'without')>Sem contrato</option>
                    <option value="suspended" @selected($filters['contract'] === 'suspended')>Contrato suspenso/cancelado</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Ordenar por</label>
                <select name="order" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="recent" @selected($filters['order'] === 'recent')>Mais recentes</option>
                    <option value="oldest" @selected($filters['order'] === 'oldest')>Mais antigos</option>
                    <option value="name" @selected($filters['order'] === 'name')>Nome (A-Z)</option>
                    <option value="name_desc" @selected($filters['order'] === 'name_desc')>Nome (Z-A)</option>
                </select>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 pt-1">
            <p class="text-xs text-gray-500">
                @php $ativos = collect($filters)->filter(fn ($v) => $v !== null && $v !== '' && $v !== 'recent')->count(); @endphp
                {{ $ativos > 0 ? $ativos.' filtro(s) ativo(s)' : 'Nenhum filtro ativo' }}
            </p>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.clients.index') }}" class="px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-100">Limpar</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
            </div>
        </div>
    </form>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">Filial</th>
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
                    <td class="px-6 py-4 text-gray-600">{{ $client->branch?->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->document }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->email ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $client->cellphone ?? '-' }}</td>
                    <td class="px-6 py-4">@include('crm::clients._status_badge', ['status' => $client->status])</td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('crm.clients.show', $client) }}" title="Visualizar" class="p-1.5 rounded hover:bg-gray-100 text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('crm.clients.edit', $client) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('crm.clients.destroy', $client) }}" onsubmit="return confirm('Remover cliente {{ $client->name }}?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-400">
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
