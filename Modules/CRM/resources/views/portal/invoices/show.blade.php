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
            <div class="flex items-center gap-1 flex-wrap justify-end">
                @include('crm::clients._status_badge', ['status' => $invoice->status])
                @if($invoice->status === 'paid')
                <a href="{{ route('crm.portal.invoices.receipt', $invoice) }}" target="_blank" title="Imprimir Recibo" class="p-2 rounded-lg text-green-600 hover:bg-green-50 border border-green-200"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
                @elseif(in_array($invoice->status, ['pending', 'overdue']))
                <a href="{{ route('crm.portal.invoices.pay', $invoice) }}" title="Pagar" class="p-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg></a>
                @if($invoice->boleto_numero || $invoice->pix_copy_paste)
                <a href="{{ route('crm.portal.invoices.boleto', $invoice) }}" title="Imprimir Boleto" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 border border-gray-300"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
                <form method="POST" action="{{ route('crm.portal.invoices.delete-payment', $invoice) }}" class="inline" onsubmit="return confirm('Remover os dados deste pagamento?')">
                    @csrf
                    <button type="submit" title="Excluir" class="p-2 rounded-lg text-red-600 hover:bg-red-50 border border-red-200"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </form>
                @endif
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

        @if($invoice->digitable_line || $invoice->barcode)
        <div class="border-t border-gray-200 p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-500 uppercase">Dados do Boleto</h3>
                @if(in_array($invoice->status, ['pending', 'overdue']))
                <form method="POST" action="{{ route('crm.portal.invoices.cancel-payment', $invoice) }}" class="inline" onsubmit="return confirm('Cancelar este pagamento no gateway?')">
                    @csrf
                    <button type="submit" title="Cancelar" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </form>
                @endif
            </div>
            @if($invoice->gateway)
            <div class="mb-3">
                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">{{ $invoice->gateway->name }}</span>
                @if($invoice->boleto_numero)
                <span class="text-xs text-gray-500 ml-2">ID: {{ $invoice->boleto_numero }}</span>
                @endif
            </div>
            @endif
            @if($invoice->digitable_line)
            <div class="bg-gray-50 rounded-lg p-4 mb-3">
                <p class="text-xs text-gray-500 mb-1">Linha Digitavel</p>
                <p class="font-mono text-sm text-gray-900 break-all">{{ $invoice->digitable_line }}</p>
            </div>
            @endif
            @if($invoice->barcode)
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">Codigo de Barras</p>
                <p class="font-mono text-sm text-gray-900">{{ $invoice->barcode }}</p>
            </div>
            @endif
            @if($invoice->gateway_payment_url)
            <a href="{{ $invoice->gateway_payment_url }}" target="_blank" title="Pagar Online" class="mt-3 inline-flex items-center gap-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg> Pagar Online</a>
            @endif
        </div>
        @endif

        @if($invoice->pix_copy_paste && !empty($invoice->gateway_qr_code))
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Pagamento via PIX</h3>
            <div class="flex flex-col md:flex-row items-center gap-6">
                <img src="data:image/png;base64,{{ $invoice->gateway_qr_code }}" alt="QR Code PIX" class="w-48 h-48 border border-gray-200 rounded-lg">
                <div class="flex-1">
                    <p class="text-sm text-gray-500 mb-2">Escaneie o QR Code ou copie o codigo PIX abaixo:</p>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="font-mono text-xs text-gray-700 break-all">{{ $invoice->pix_copy_paste }}</p>
                    </div>
                    <button onclick="navigator.clipboard.writeText('{{ $invoice->pix_copy_paste }}').then(() => this.textContent = 'Copiado!')" class="mt-2 px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg> Copiar Codigo PIX
                    </button>
                </div>
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
        <a href="{{ route('crm.portal.invoices') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Voltar para faturas</a>
    </div>
</div>
@endsection
