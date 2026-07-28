@extends('core::layouts.master')

@section('title', 'Editar Categoria')

@section('content')
<div class="max-w-xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Categoria</h2>

    <form method="POST" action="{{ route('crm.stock-categories.update', $category) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('description', $category->description) }}</textarea>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Atualizar</button>
            <a href="{{ route('crm.stock-categories.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Cancelar</a>
        </div>
    </form>
</div>
@endsection
