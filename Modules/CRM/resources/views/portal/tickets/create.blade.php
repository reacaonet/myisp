@extends('crm::portal.layouts.master')

@section('title', 'Abrir Chamado')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Abrir Chamado</h3>
        </div>

        @if($errors->any())
        <div class="mx-6 mt-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="p-6">
            <form method="POST" action="{{ route('crm.portal.tickets.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assunto *</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                           placeholder="Descreva brevemente o problema">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                        <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="">Selecione...</option>
                            <option value="conexao" @selected(old('category') == 'conexao')>Conexao/Internet</option>
                            <option value="velocidade" @selected(old('category') == 'velocidade')>Velocidade</option>
                            <option value="fatura" @selected(old('category') == 'fatura')>Fatura/Pagamento</option>
                            <option value="instalacao" @selected(old('category') == 'instalacao')>Instalacao</option>
                            <option value="equipamento" @selected(old('category') == 'equipamento')>Equipamento</option>
                            <option value="outro" @selected(old('category') == 'outro')>Outro</option>
                        </select>
                    </div>
                </div>

                @if($client->activeContracts->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contrato Relacionado</label>
                    <select name="contract_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Nenhum</option>
                        @foreach($client->activeContracts as $contract)
                        <option value="{{ $contract->id }}" @selected(old('contract_id') == $contract->id)>
                            {{ $contract->codigo ?? 'Contrato #' . $contract->id }} - {{ $contract->plan->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descricao do Problema *</label>
                    <textarea name="description" rows="5" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="Descreva o problema com detalhes: quando acontece, frequencia, o que ja tentou...">{{ old('description') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('crm.portal.tickets') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Abrir Chamado</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
