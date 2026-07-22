@extends('crm::portal.layouts.master')

@section('title', "Pagar Fatura {$invoice->invoice_number}")

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-900">Pagamento da Fatura</h2>
            <p class="text-sm text-gray-500 mt-1">Selecione a forma de pagamento abaixo</p>
        </div>

        <div class="p-6">
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Numero</p>
                        <p class="font-bold text-gray-900">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Vencimento</p>
                        <p class="font-bold text-gray-900">{{ $invoice->due_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-gray-500">Valor Total</p>
                        <p class="font-bold text-2xl text-gray-900">R$ {{ number_format($invoice->total, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            @if($gateways->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <p>Nenhum gateway de pagamento disponivel no momento.</p>
            </div>
            @else
            <form method="POST" action="{{ route('crm.portal.invoices.pay.store', $invoice) }}">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Gateway de Pagamento</label>
                    @foreach($gateways as $gateway)
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg mb-2 cursor-pointer hover:bg-gray-50 {{ old('gateway_id') == $gateway->id ? 'border-blue-500 bg-blue-50' : '' }}">
                        <input type="radio" name="gateway_id" value="{{ $gateway->id }}" {{ old('gateway_id', $gateways->first()->id) == $gateway->id ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                        <span class="font-medium text-gray-900">{{ $gateway->name }}</span>
                        <span class="text-xs text-gray-500 ml-auto">
                            @if($gateway->supports_pix) PIX @endif
                            @if($gateway->supports_pix && $gateway->supports_boleto) / @endif
                            @if($gateway->supports_boleto) Boleto @endif
                        </span>
                    </label>
                    @endforeach
                    @error('gateway_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Forma de Pagamento</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 flex-1 {{ old('payment_method') === 'pix' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="payment_method" value="pix" {{ old('payment_method', 'pix') === 'pix' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <p class="font-medium text-gray-900">PIX</p>
                                <p class="text-xs text-gray-500">Pagamento instantaneo</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 flex-1 {{ old('payment_method') === 'boleto' ? 'border-blue-500 bg-blue-50' : '' }}">
                            <input type="radio" name="payment_method" value="boleto" {{ old('payment_method') === 'boleto' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <p class="font-medium text-gray-900">Boleto</p>
                                <p class="text-xs text-gray-500">Vencimento em ate 3 dias uteis</p>
                            </div>
                        </label>
                    </div>
                    @error('payment_method')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('crm.portal.invoices.show', $invoice) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Gerar Pagamento</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
