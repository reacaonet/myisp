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
                <label class="block text-sm font-medium text-gray-700 mb-1">Compania</label>
                <select name="company_id" id="company_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ old('company_id', $branch->company_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Filial</label>
                <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                <input type="text" name="code" value="{{ old('code', $branch->code) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filial pai</label>
                <select name="parent_id" id="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" @disabled($branch->isMatrix())>
                    <option value="">Nenhuma (Matriz)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" data-company="{{ $b->company_id }}" {{ old('parent_id', $branch->parent_id) == $b->id ? 'selected' : '' }}>{{ $b->company->name ?? '' }} - {{ $b->name }}</option>
                    @endforeach
                </select>
                @if($branch->isMatrix())
                    <p class="mt-1 text-xs text-gray-400">A filial Matriz nao possui pai.</p>
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

<script>
    const companySelect = document.getElementById('company_id');
    const parentSelect = document.getElementById('parent_id');

    function filterParents() {
        const companyId = companySelect.value;
        for (const option of parentSelect.options) {
            if (option.value === '') continue;
            option.hidden = option.dataset.company !== companyId;
        }
    }

    if (companySelect) {
        companySelect.addEventListener('change', filterParents);
        filterParents();
    }
</script>
@endsection