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
            @if($serviceOrder->situacao === 'O')
            <form method="POST" action="{{ route('technician.portal.service-orders.start', $serviceOrder) }}" class="inline">
                @csrf
                <button type="submit" title="Iniciar" class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
            </form>
            @endif
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

    {{-- Timeline --}}
    <div class="mt-6 pt-4 border-t border-gray-200">
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4">Progresso</h3>
        <div class="flex items-center justify-between">
            <div class="flex flex-col items-center flex-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $serviceOrder->emissao ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <p class="text-xs font-medium text-gray-600 mt-2">Abertura</p>
                <p class="text-xs text-gray-400">{{ $serviceOrder->emissao?->format('d/m') ?? '-' }}</p>
            </div>
            <div class="flex-1 h-0.5 {{ $serviceOrder->situacao !== 'O' ? 'bg-blue-500' : 'bg-gray-200' }}"></div>
            <div class="flex flex-col items-center flex-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ in_array($serviceOrder->situacao, ['A', 'F']) ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-xs font-medium text-gray-600 mt-2">Em Andamento</p>
                @if($serviceOrder->saida)
                <p class="text-xs text-gray-400">{{ $serviceOrder->saida->format('d/m') }}</p>
                @endif
            </div>
            <div class="flex-1 h-0.5 {{ $serviceOrder->situacao === 'F' ? 'bg-green-500' : 'bg-gray-200' }}"></div>
            <div class="flex flex-col items-center flex-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $serviceOrder->situacao === 'F' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-xs font-medium text-gray-600 mt-2">Finalizada</p>
                @if($serviceOrder->situacao === 'F')
                <p class="text-xs text-gray-400">{{ $serviceOrder->updated_at->format('d/m') }}</p>
                @endif
            </div>
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

@if($serviceOrder->situacao === 'A')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Concluir Ordem de Servico</h3>
    <form method="POST" action="{{ route('technician.portal.service-orders.complete', $serviceOrder) }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Diagnostico</label>
            <textarea name="diagnostico" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Descreva o diagnostico...">{{ $serviceOrder->diagnostico }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Solucao</label>
            <textarea name="solucao" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Descreva a solucao aplicada...">{{ $serviceOrder->solucao }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit" title="Concluir OS" class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
        </div>
    </form>
</div>
@endif

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

        <div class="flex justify-end gap-1 pt-4 border-t border-gray-200">
            <a href="{{ route('technician.portal.dashboard') }}" title="Voltar" class="p-2 rounded-lg text-gray-600 border border-gray-300 hover:bg-gray-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></a>
            <button type="submit" title="Salvar Alteracoes" class="p-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></button>
        </div>
    </form>
</div>
@endsection