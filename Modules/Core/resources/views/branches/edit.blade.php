@extends('core::layouts.master')

@section('title', 'Editar Filial')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('core.branches.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar</a>
    </div>

    <form method="POST" action="{{ route('core.branches.update', $branch) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf @method('PUT')

        <h2 class="text-xl font-bold text-gray-900 mb-6">Editar Filial</h2>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Filial</label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                <input type="text" name="document" value="{{ old('document', $branch->documentFormatted()) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="00.000.000/0000-00">
                <p class="text-xs text-gray-500 mt-1">Opcional. Deixe vazio se a filial nao emite nota em nome proprio.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                <input type="text" name="code" value="{{ old('code', $branch->code) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidade pai (opcional)</label>
                <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" @disabled($branch->isMatrix())>
                    <option value="">Nenhuma — filial direto da empresa</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ old('parent_id', $branch->parent_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
                @if($branch->isMatrix())
                    <p class="mt-1 text-xs text-gray-400">A Matriz nao possui unidade pai.</p>
                @endif
            </div>
            <div class="md:col-span-2">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    Ativa
                </label>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Filial</button>
        </div>
    </form>
</div>
@endsection