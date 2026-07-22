@extends('core::layouts.master')

@section('title', 'Novo Lancamento - Livro Caixa')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Novo Lancamento</h2>
        <a href="{{ route('billing.cash-book.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="POST" action="{{ route('billing.cash-book.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="entrada">Entrada</option>
                    <option value="saida">Saida</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                <input type="number" step="0.01" name="amount" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="0,00">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Descricao *</label>
                <input type="text" name="description" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Descricao do lancamento">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                <input type="text" name="category" list="categories" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: Material, Aluguel, Luz">
                <datalist id="categories">
                    @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat }}">
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento</label>
                <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Selecione</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="pix">PIX</option>
                    <option value="boleto">Boleto</option>
                    <option value="transferencia">Transferencia</option>
                    <option value="cartao">Cartao</option>
                    <option value="cheque">Cheque</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                <input type="text" name="reference" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: NF, Contrato, Boleto">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
            </div>
        </div>

        @if($errors->any())
        <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-3">
            @foreach($errors->all() as $error)
            <p class="text-sm text-red-600">{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('billing.cash-book.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
        </div>
    </form>
</div>
@endsection
