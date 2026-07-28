@extends('core::layouts.master')

@section('title', 'Nova Movimentação')

@section('content')
<div class="max-w-xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Nova Movimentação</h2>

    <form method="POST" action="{{ route('crm.stock-movements.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Item *</label>
            <select name="item_id" required class="w-full border rounded-lg px-3 py-2">
                <option value="">Selecione...</option>
                @foreach($items as $item)
                    <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>{{ $item->name }} ({{ $item->sku }})</option>
                @endforeach
            </select>
            @error('item_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
            <select name="type" required class="w-full border rounded-lg px-3 py-2">
                <option value="">Selecione...</option>
                <option value="entry" {{ old('type') == 'entry' ? 'selected' : '' }}>Entrada (compra/recebimento)</option>
                <option value="exit" {{ old('type') == 'exit' ? 'selected' : '' }}>Saída (empréstimo para técnico)</option>
                <option value="return" {{ old('type') == 'return' ? 'selected' : '' }}>Devolução (técico devolve)</option>
                <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>Transferência (entre locais)</option>
                <option value="installation" {{ old('type') == 'installation' ? 'selected' : '' }}>Instalação (consumido no cliente)</option>
            </select>
            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Local *</label>
            <select name="location_id" required class="w-full border rounded-lg px-3 py-2">
                <option value="">Selecione...</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }} ({{ $loc->type === 'deposit' ? 'Depósito' : 'Técnico' }})</option>
                @endforeach
            </select>
            @error('location_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade *</label>
            <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" required class="w-full border rounded-lg px-3 py-2">
            @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Referência</label>
            <input type="text" name="reference" value="{{ old('reference') }}" placeholder="Nota fiscal, OS, etc." class="w-full border rounded-lg px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
            <textarea name="notes" rows="3" class="w-full border rounded-lg px-3 py-2">{{ old('notes') }}</textarea>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Registrar</button>
            <a href="{{ route('crm.stock-movements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Cancelar</a>
        </div>
    </form>
</div>
@endsection
