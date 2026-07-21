@extends('core::layouts.master')

@section('title', "Editar Fatura {$invoice->invoice_number}")

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar Fatura {{ $invoice->invoice_number }}</h2>
        </div>
        <form method="POST" action="{{ route('billing.invoices.update', $invoice) }}" class="p-6 space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                <select name="client_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $invoice->client_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                    <input type="number" step="0.01" name="amount" value="{{ old('amount', $invoice->amount) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desconto</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount', $invoice->discount) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vencimento *</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="pending" @selected(old('status', $invoice->status) == 'pending')>Pendente</option>
                        <option value="paid" @selected(old('status', $invoice->status) == 'paid')>Pago</option>
                        <option value="overdue" @selected(old('status', $invoice->status) == 'overdue')>Vencido</option>
                        <option value="canceled" @selected(old('status', $invoice->status) == 'canceled')>Cancelado</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metodo Pagamento</label>
                    <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="pix" @selected(old('payment_method', $invoice->payment_method) == 'pix')>PIX</option>
                        <option value="boleto" @selected(old('payment_method', $invoice->payment_method) == 'boleto')>Boleto</option>
                        <option value="credit_card" @selected(old('payment_method', $invoice->payment_method) == 'credit_card')>Cartao</option>
                        <option value="cash" @selected(old('payment_method', $invoice->payment_method) == 'cash')>Dinheiro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Pagamento</label>
                    <input type="date" name="paid_date" value="{{ old('paid_date', $invoice->paid_date?->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Transacao ID</label>
                <input type="text" name="transaction_id" value="{{ old('transaction_id', $invoice->transaction_id) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('billing.invoices.show', $invoice) }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
