@extends('core::layouts.master')

@section('title', 'Editar Item')

@section('content')
<div class="max-w-xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar Item</h2>

    <form method="POST" action="{{ route('crm.stock-items.update', $item) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoria *</label>
            <select name="category_id" required class="w-full border rounded-lg px-3 py-2">
                <option value="">Selecione...</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
            <input type="text" name="sku" value="{{ old('sku', $item->sku) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
            <input type="text" name="name" value="{{ old('name', $item->name) }}" required class="w-full border rounded-lg px-3 py-2">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
            <textarea name="description" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('description', $item->description) }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unidade *</label>
                <select name="unit" required class="w-full border rounded-lg px-3 py-2">
                    <option value="un" {{ old('unit', $item->unit) == 'un' ? 'selected' : '' }}>Unidade (un)</option>
                    <option value="mt" {{ old('unit', $item->unit) == 'mt' ? 'selected' : '' }}>Metro (mt)</option>
                    <option value="cx" {{ old('unit', $item->unit) == 'cx' ? 'selected' : '' }}>Caixa (cx)</option>
                    <option value="pc" {{ old('unit', $item->unit) == 'pc' ? 'selected' : '' }}>Peça (pc)</option>
                    <option value="par" {{ old('unit', $item->unit) == 'par' ? 'selected' : '' }}>Par (par)</option>
                    <option value="kg" {{ old('unit', $item->unit) == 'kg' ? 'selected' : '' }}>Quilograma (kg)</option>
                    <option value="l" {{ old('unit', $item->unit) == 'l' ? 'selected' : '' }}>Litro (l)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estoque Mínimo *</label>
                <input type="number" name="min_stock" value="{{ old('min_stock', $item->min_stock) }}" min="0" required class="w-full border rounded-lg px-3 py-2">
            </div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Atualizar</button>
            <a href="{{ route('crm.stock-items.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Cancelar</a>
        </div>
    </form>
</div>
@endsection
