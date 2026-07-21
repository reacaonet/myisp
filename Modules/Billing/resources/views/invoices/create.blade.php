@extends('core::layouts.master')

@section('title', 'Nova Fatura')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Nova Fatura</h2>
        </div>
        <form method="POST" action="{{ route('billing.invoices.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                <select name="client_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Selecione...</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id') == $c->id)>{{ $c->name }} - {{ $c->document }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desconto</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vencimento *</label>
                    <input type="date" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="pending">Pendente</option>
                        <option value="paid">Pago</option>
                        <option value="overdue">Vencido</option>
                        <option value="canceled">Cancelado</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metodo Pagamento</label>
                    <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="pix">PIX</option>
                        <option value="boleto">Boleto</option>
                        <option value="credit_card">Cartao</option>
                        <option value="cash">Dinheiro</option>
                        <option value="debit_contract">Debito</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Pagamento</label>
                    <input type="date" name="paid_date" value="{{ old('paid_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Transacao ID</label>
                <input type="text" name="transaction_id" value="{{ old('transaction_id') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('billing.invoices.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Criar Fatura</button>
            </div>
        </form>
    </div>
</div>
@endsection
