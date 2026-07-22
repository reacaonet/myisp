@extends('core::layouts.master')

@section('title', 'Nova Configuracao')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Nova Configuracao</h2>
        <a href="{{ route('core.settings.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>
    <form method="POST" action="{{ route('core.settings.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Chave *</label>
                <input type="text" name="key" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono" placeholder="ex: company_name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="text">Texto</option>
                    <option value="textarea">Textarea</option>
                    <option value="number">Numero</option>
                    <option value="boolean">Sim/Nao</option>
                    <option value="password">Senha</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grupo *</label>
                <input type="text" name="group" value="general" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" list="groups">
                <datalist id="groups">
                    <option value="general">
                    <option value="billing">
                    <option value="company">
                    <option value="mikrotik">
                    <option value="notifications">
                </datalist>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor</label>
                <textarea name="value" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.settings.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
        </div>
    </form>
</div>
@endsection
