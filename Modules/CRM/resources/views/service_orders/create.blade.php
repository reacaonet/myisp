@extends('core::layouts.master')

@section('title', 'Nova Ordem de Servico')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Nova Ordem de Servico</h2>
        </div>
        <form method="POST" action="{{ route('crm.service-orders.store') }}" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select name="client_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tecnico</label>
                    <select name="technician_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($technicians as $t)
                            <option value="{{ $t->id }}" @selected(old('technician_id') == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Situacao *</label>
                    <select name="situacao" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="O">Orcamento</option>
                        <option value="I">Instalado</option>
                        <option value="NI">Instalacao</option>
                        <option value="M">Manutencao</option>
                        <option value="R">Recuperacao</option>
                        <option value="A">Aprovado</option>
                        <option value="CS">Cancelamento</option>
                        <option value="C">Cancelada</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Servico</label>
                    <select name="tipo_servico" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="instalacao">Instalacao</option>
                        <option value="manutencao">Manutencao</option>
                        <option value="cancelamento">Cancelamento</option>
                        <option value="recuperacao">Recuperacao</option>
                        <option value="orcamento">Orcamento</option>
                        <option value="visita_tecnica">Visita Tecnica</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Servico</label>
                <input type="text" name="servico" value="{{ old('servico') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Emissao</label>
                    <input type="date" name="emissao" value="{{ old('emissao', date('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora Abertura</label>
                    <input type="time" name="hora_abertura" value="{{ old('hora_abertura', date('H:i')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agendamento</label>
                    <input type="date" name="data_agendamento" value="{{ old('data_agendamento') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hora Agendamento</label>
                    <input type="time" name="hora_agendamento" value="{{ old('hora_agendamento') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Problema</label>
                <textarea name="problema" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('problema') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Diagnostico</label>
                <textarea name="diagnostico" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('diagnostico') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Solucao</label>
                <textarea name="solucao" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('solucao') }}</textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                    <input type="number" step="0.01" name="preco" value="{{ old('preco') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serie</label>
                    <input type="text" name="serie" value="{{ old('serie') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Atendente</label>
                    <input type="text" name="atendente" value="{{ old('atendente', 'Admin') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('crm.service-orders.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Criar OS</button>
            </div>
        </form>
    </div>
</div>
@endsection
