@extends('crm::technician.layouts.master')

@section('title', 'OS - {{ $serviceOrder->codigo }}')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">{{ $serviceOrder->codigo }} - {{ $serviceOrder->servico ?? $serviceOrder->tipo_servico }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $serviceOrder->client->name }} &middot; {{ $serviceOrder->client->document }}</p>
        </div>
        <div class="flex items-center gap-4 flex-wrap">
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $serviceOrder->situacao === 'O' ? 'bg-blue-100 text-blue-700' : ($serviceOrder->situacao === 'A' ? 'bg-yellow-100 text-yellow-700' : ($serviceOrder->situacao === 'F' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')) }}">
                {{ $serviceOrder->situacao === 'O' ? 'Aberta' : ($serviceOrder->situacao === 'A' ? 'Em Andamento' : ($serviceOrder->situacao === 'F' ? 'Finalizada' : 'Cancelada')) }}
            </span>
            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $serviceOrder->status === 'active' ? 'bg-blue-100 text-blue-700' : ($serviceOrder->status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                {{ ucfirst($serviceOrder->status) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
        <div>
            <p class="font-medium text-gray-500">Agendamento</p>
            <p class="text-gray-900">{{ $serviceOrder->data_agendamento?->format('d/m/Y') ?? '-' }} @if($serviceOrder->hora_agendamento) {{ $serviceOrder->hora_agendamento }} @endif</p>
        </div>
        <div>
            <p class="font-medium text-gray-500">Emissao</p>
            <p class="text-gray-900">{{ $serviceOrder->emissao?->format('d/m/Y') ?? '-' }} {{ $serviceOrder->hora_abertura ?? '' }}</p>
        </div>
        <div>
            <p class="font-medium text-gray-500">Tecnico</p>
            <p class="text-gray-900">{{ $serviceOrder->technician->name ?? 'Nao atribuido' }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informacoes do Cliente</h3>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Nome</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->client->name }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Documento</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->client->document }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Telefone</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->client->phone }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Celular</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->client->cellphone }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->client->email }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Endereco</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->client->addresses->first()->full_address ?? 'N/A' }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Detalhes do Contrato</h3>
        @if($serviceOrder->contract)
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-gray-500">Plano</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->contract->plan->name ?? 'N/A' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">Tipo Conexao</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->contract->tipo_conexao ?? 'N/A' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">PPPoE User</dt><dd class="font-medium text-gray-900 font-mono text-sm">{{ $serviceOrder->contract->pppoe_user ?? 'N/A' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">IP</dt><dd class="font-medium text-gray-900">{{ $serviceOrder->contract->ip_address ?? 'N/A' }}</dd></div>
            <div class="flex justify-between"><dt class="text-gray-500">MAC</dt><dd class="font-medium text-gray-900 font-mono text-sm">{{ $serviceOrder->contract->mac_address ?? 'N/A' }}</dd></div>
        </dl>
        @else
        <p class="text-gray-500">Sem contrato vinculado</p>
        @endif
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Descricao do Problema</h3>
    <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ $serviceOrder->problema ?? 'Nao informado' }}</div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Diagnostico e Solucao</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <p class="font-medium text-gray-500 mb-2">Diagnostico</p>
            <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap border border-gray-200 rounded-lg p-4 min-h-[100px]">{{ $serviceOrder->diagnostico ?? 'Nao informado' }}</div>
        </div>
        <div>
            <p class="font-medium text-gray-500 mb-2">Solucao</p>
            <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap border border-gray-200 rounded-lg p-4 min-h-[100px]">{{ $serviceOrder->solucao ?? 'Nao informado' }}</div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Atualizar Ordem de Servico</h3>
    <form method="POST" action="{{ route('technician.portal.service-orders.update', $serviceOrder) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Situacao (O/A/F/C)</label>
                <select name="situacao" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="O" {{ $serviceOrder->situacao === 'O' ? 'selected' : '' }}>Aberta (O)</option>
                    <option value="A" {{ $serviceOrder->situacao === 'A' ? 'selected' : '' }}>Em Andamento (A)</option>
                    <option value="F" {{ $serviceOrder->situacao === 'F' ? 'selected' : '' }}>Finalizada (F)</option>
                    <option value="C" {{ $serviceOrder->situacao === 'C' ? 'selected' : '' }}>Cancelada (C)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="active" {{ $serviceOrder->status === 'active' ? 'selected' : '' }}>Ativa</option>
                    <option value="in_progress" {{ $serviceOrder->status === 'in_progress' ? 'selected' : '' }}>Em Andamento</option>
                    <option value="closed" {{ $serviceOrder->status === 'closed' ? 'selected' : '' }}>Fechada</option>
                    <option value="canceled" {{ $serviceOrder->status === 'canceled' ? 'selected' : '' }}>Cancelada</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Encerrado</label>
                <select name="encerrado" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="0" {{ !$serviceOrder->encerrado ? 'selected' : '' }}>Nao</option>
                    <option value="1" {{ $serviceOrder->encerrado ? 'selected' : '' }}>Sim</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Diagnostico</label>
            <textarea name="diagnostico" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $serviceOrder->diagnostico }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Solucao</label>
            <textarea name="solucao" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ $serviceOrder->solucao }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Preco (R$)</label>
            <input type="number" step="0.01" name="preco" value="{{ $serviceOrder->preco }}" class="w-full max-w-xs px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('technician.portal.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Voltar</a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Salvar Alteracoes</button>
        </div>
    </form>
</div>
@endsection