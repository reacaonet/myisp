@extends('crm::portal.layouts.master')

@section('title', 'Pagamento Gerado')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Pagamento Gerado com Sucesso</h2>
        </div>

        <div class="p-6 text-center">
            @if($validated['payment_method'] === 'pix')
                @if(!empty($result['qr_code']))
                <div class="mb-6">
                    <p class="text-sm text-gray-500 mb-4">Escaneie o QR Code abaixo para pagar via PIX:</p>
                    <img src="data:image/png;base64,{{ $result['qr_code'] }}" alt="QR Code PIX" class="mx-auto mb-4" style="max-width: 300px;">
                </div>
                @endif

                @if(!empty($result['copy_paste']))
                <div class="mb-6">
                    <p class="text-sm text-gray-500 mb-2">Ou copie o codigo PIX:</p>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <code class="text-xs text-gray-700 break-all">{{ $result['copy_paste'] }}</code>
                    </div>
                    <button onclick="navigator.clipboard.writeText('{{ $result['copy_paste'] }}')" class="mt-2 px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">
                        Copiar Codigo PIX
                    </button>
                </div>
                @endif
            @elseif($validated['payment_method'] === 'boleto')
                <div class="mb-6">
                    <p class="text-sm text-gray-500 mb-4">Clique no botao abaixo para visualizar e imprimir seu boleto:</p>
                    @if(!empty($result['boleto_url']))
                    <a href="{{ $result['boleto_url'] }}" target="_blank" class="inline-block px-6 py-3 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Abrir Boleto
                    </a>
                    @else
                    <p class="text-red-500">Link do boleto nao disponivel.</p>
                    @endif
                </div>
            @endif

            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-sm text-left">
                <p class="text-gray-500">Fatura: <span class="font-medium text-gray-900">{{ $invoice->invoice_number }}</span></p>
                <p class="text-gray-500">Valor: <span class="font-bold text-gray-900">R$ {{ number_format($invoice->total, 2, ',', '.') }}</span></p>
                <p class="text-gray-500">Vencimento: <span class="font-medium text-gray-900">{{ $invoice->due_date->format('d/m/Y') }}</span></p>
            </div>

            <div class="flex justify-center gap-3">
                <a href="{{ route('crm.portal.invoices.show', $invoice) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Ver Detalhes da Fatura</a>
                <a href="{{ route('crm.portal.invoices') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Voltar para Faturas</a>
            </div>
        </div>
    </div>
</div>
@endsection
