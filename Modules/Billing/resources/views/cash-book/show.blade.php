@extends('core::layouts.master')

@section('title', 'Detalhes do Lancamento')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Detalhes do Lancamento</h2>
        <div class="flex gap-1">
            <a href="{{ route('billing.cash-book.edit', $entry) }}" title="Editar" class="p-2 rounded-lg text-blue-600 border border-blue-200 hover:bg-blue-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            <a href="{{ route('billing.cash-book.index') }}" title="Voltar" class="p-2 rounded-lg text-gray-600 border border-gray-300 hover:bg-gray-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Tipo</dt>
                <dd class="font-medium">
                    @if($entry->type === 'entrada')
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Entrada</span>
                    @else
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Saida</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Valor</dt>
                <dd class="font-bold text-lg {{ $entry->type === 'entrada' ? 'text-green-700' : 'text-red-700' }}">
                    {{ $entry->type === 'entrada' ? '+' : '-' }} R$ {{ number_format($entry->amount, 2, ',', '.') }}
                </dd>
            </div>
            <div class="col-span-2">
                <dt class="text-gray-500">Descricao</dt>
                <dd class="font-medium">{{ $entry->description }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Categoria</dt>
                <dd>{{ $entry->category ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Data</dt>
                <dd>{{ $entry->entry_date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Pagamento</dt>
                <dd>{{ $entry->payment_method ? ucfirst($entry->payment_method) : '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Referencia</dt>
                <dd>{{ $entry->reference ?? '-' }}</dd>
            </div>
            @if($entry->notes)
            <div class="col-span-2">
                <dt class="text-gray-500">Observacoes</dt>
                <dd class="bg-gray-50 rounded-lg p-3">{{ $entry->notes }}</dd>
            </div>
            @endif
            @if($entry->invoice)
            <div class="col-span-2">
                <dt class="text-gray-500">Fatura Vinculada</dt>
                <dd><a href="{{ route('billing.invoices.show', $entry->invoice) }}" class="text-blue-600 hover:underline">{{ $entry->invoice->invoice_number }}</a></dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection
