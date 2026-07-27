@extends('core::layouts.master')

@section('title', 'Editar Grupo - ' . $group->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Editar Grupo: {{ $group->name }}</h2>
        <a href="{{ route('core.user-groups.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="POST" action="{{ route('core.user-groups.update', $group) }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $group->name) }}" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                    <input type="text" name="slug" value="{{ old('slug', $group->slug) }}" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 font-mono">
                    @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descricao</label>
                <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('description', $group->description) }}</textarea>
            </div>
            <div class="mt-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $group->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm font-medium text-gray-700">Ativo</span>
                </label>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Permissoes</h3>

            <div class="flex items-center gap-3 mb-4">
                <button type="button" onclick="toggleAll(true)" class="text-xs text-blue-600 hover:underline">Marcar todas</button>
                <span class="text-gray-300">|</span>
                <button type="button" onclick="toggleAll(false)" class="text-xs text-blue-600 hover:underline">Desmarcar todas</button>
            </div>

            <div class="space-y-2">
                @foreach($permissions as $key => $perm)
                <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100 cursor-pointer">
                    <input type="checkbox" name="perm_{{ $key }}" value="1" {{ $perm['granted'] ? 'checked' : '' }} class="perm-check rounded border-gray-300 text-blue-600">
                    <span class="text-sm font-medium text-gray-700">{{ $perm['label'] }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('core.user-groups.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Alteracoes</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleAll(state) {
    document.querySelectorAll('.perm-check').forEach(cb => cb.checked = state);
}
</script>
@endpush
@endsection
