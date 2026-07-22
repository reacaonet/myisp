@extends('crm::portal.layouts.master')

@section('title', "Fatura {$invoice->invoice_number}")

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Fatura {{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-gray-500">Vencimento: {{ $invoice->due_date->format('d/m/Y') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @include('crm::clients._status_badge', ['status' => $invoice->status])
                @if($invoice->status === 'paid')
                <a href="{{ route('crm.portal.invoices.receipt', $invoice) }}" target="_blank" class="px-4 py-2 text-sm font-medium text-green-600 border border-green-200 rounded-lg hover:bg-green-50">Imprimir Recibo</a>
                @elseif(in_array($invoice->status, ['pending', 'overdue']))
                <a href="{{ route('crm.portal.invoices.pay', $invoice) }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Pagar</a>
                @endif
            </div>
        </div>

        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Cliente</dt>
                    <dd class="font-medium text-gray-900">{{ $invoice->client->name }}</dd>
                    <dd class="text-gray-500">{{ $invoice->client->document }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Contrato</dt>
                    <dd class="font-medium text-gray-900">{{ $invoice->contract?->plan?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Valor</dt>
                    <dd class="font-bold text-gray-900">R$ {{ number_format($invoice->amount, 2, ',', '.') }}</dd>
                </div>
                @if($invoice->discount > 0)
                <div>
                    <dt class="text-gray-500">Desconto</dt>
                    <dd class="text-green-600">-R$ {{ number_format($invoice->discount, 2, ',', '.') }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-gray-500">Total</dt>
                    <dd class="font-bold text-xl text-gray-900">R$ {{ number_format($invoice->total, 2, ',', '.') }}</dd>
                </div>
                @if($invoice->paid_date)
                <div>
                    <dt class="text-gray-500">Pago em</dt>
                    <dd class="text-gray-900">{{ $invoice->paid_date->format('d/m/Y') }}</dd>
                </div>
                @endif
                @if($invoice->payment_method)
                <div>
                    <dt class="text-gray-500">Forma de Pagamento</dt>
                    <dd class="text-gray-900">{{ strtoupper($invoice->payment_method) }}</dd>
                </div>
                @endif
            </dl>
        </div>

        @if($invoice->payments->isNotEmpty())
        <div class="border-t border-gray-200">
            <div class="p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Pagamentos</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200">
                            <th class="pb-2 font-medium">Data</th>
                            <th class="pb-2 font-medium">Valor</th>
                            <th class="pb-2 font-medium">Metodo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr class="border-b border-gray-100">
                            <td class="py-2">{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="py-2 font-medium">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                            <td class="py-2">{{ strtoupper($payment->payment_method) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($invoice->notes)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Observacoes</h3>
            <p class="text-sm text-gray-700">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('crm.portal.invoices') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar para faturas</a>
    </div>
</div>
@endsection
