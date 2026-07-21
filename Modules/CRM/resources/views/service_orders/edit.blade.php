@extends('core::layouts.master')

@section('title', "Editar OS {$order->codigo}")

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar OS {{ $order->codigo }}</h2>
        </div>
        <form method="POST" action="{{ route('crm.service-orders.update', $order) }}" class="p-6 space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select name="client_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_id', $order->client_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tecnico</label>
                    <select name="technician_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}" @selected(old('technician_id', $order->technician_id) == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Situacao *</label>
                    <select name="situacao" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="O" @selected($order->situacao == 'O')>Orcamento</option>
                        <option value="I" @selected($order->situacao == 'I')>Instalado</option>
                        <option value="NI" @selected($order->situacao == 'NI')>Instalacao</option>
                        <option value="M" @selected($order->situacao == 'M')>Manutencao</option>
                        <option value="R" @selected($order->situacao == 'R')>Recuperacao</option>
                        <option value="A" @selected($order->situacao == 'A')>Aprovado</option>
                        <option value="CS" @selected($order->situacao == 'CS')>Cancelamento</option>
                        <option value="C" @selected($order->situacao == 'C')>Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Servico</label>
                    <select name="tipo_servico" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach(['instalacao','manutencao','cancelamento','recuperacao','orcamento','visita_tecnica','outro'] as $tipo)
                            <option value="{{ $tipo }}" @selected($order->tipo_servico == $tipo)>{{ ucfirst($tipo) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Servico</label>
                <input type="text" name="servico" value="{{ old('servico', $order->servico) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emissao</label>
                    <input type="date" name="emissao" value="{{ old('emissao', $order->emissao?->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora Abertura</label>
                    <input type="time" name="hora_abertura" value="{{ old('hora_abertura', $order->hora_abertura) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Problema</label>
                <textarea name="problema" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('problema', $order->problema) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Diagnostico</label>
                <textarea name="diagnostico" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('diagnostico', $order->diagnostico) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Solucao</label>
                <textarea name="solucao" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('solucao', $order->solucao) }}</textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                    <input type="number" step="0.01" name="preco" value="{{ old('preco', $order->preco) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serie</label>
                    <input type="text" name="serie" value="{{ old('serie', $order->serie) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Atendente</label>
                    <input type="text" name="atendente" value="{{ old('atendente', $order->atendente) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('crm.service-orders.show', $order) }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
