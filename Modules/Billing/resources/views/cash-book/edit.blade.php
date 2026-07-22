@extends('core::layouts.master')

@section('title', 'Editar Lancamento')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Editar Lancamento</h2>
        <a href="{{ route('billing.cash-book.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="POST" action="{{ route('billing.cash-book.update', $entry) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="entrada" {{ $entry->type === 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="saida" {{ $entry->type === 'saida' ? 'selected' : '' }}>Saida</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                <input type="number" step="0.01" name="amount" value="{{ $entry->amount }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descricao *</label>
                <input type="text" name="description" value="{{ $entry->description }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <input type="text" name="category" value="{{ $entry->category }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                <input type="date" name="entry_date" value="{{ $entry->entry_date->format('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Selecione</option>
                    @foreach(['dinheiro','pix','boleto','transferencia','cartao','cheque','outro'] as $method)
                    <option value="{{ $method }}" {{ $entry->payment_method === $method ? 'selected' : '' }}>{{ ucfirst($method) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                <input type="text" name="reference" value="{{ $entry->reference }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $entry->notes }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('billing.cash-book.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Atualizar</button>
        </div>
    </form>
</div>
@endsection
