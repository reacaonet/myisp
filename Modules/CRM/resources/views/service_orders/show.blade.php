@extends('core::layouts.master')

@section('title', "OS {$order->codigo}")

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">OS {{ $order->codigo }}</h2>
                <p class="text-sm text-gray-500">Aberta em {{ $order->emissao?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <a href="{{ route('crm.service-orders.edit', $order) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">Editar</a>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Cliente</h3>
                <p class="font-medium text-gray-900">{{ $order->client->name ?? 'N/D' }}</p>
                <p class="text-sm text-gray-500">{{ $order->client->document ?? '' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Detalhes</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Situacao</dt>
                        <dd><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ $order->situacao }}</span></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Servico</dt>
                        <dd class="text-gray-900">{{ $order->servico ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tecnico</dt>
                        <dd class="text-gray-900">{{ $order->technician->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Valor</dt>
                        <dd class="text-gray-900">R$ {{ number_format($order->preco, 2, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        @if($order->problema)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Problema</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $order->problema }}</p>
        </div>
        @endif

        @if($order->diagnostico)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Diagnostico</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $order->diagnostico }}</p>
        </div>
        @endif

        @if($order->solucao)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Solucao</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $order->solucao }}</p>
        </div>
        @endif
    </div>

    <div class="mt-4 flex justify-end">
        <form method="POST" action="{{ route('crm.service-orders.destroy', $order) }}" onsubmit="return confirm('Remover OS {{ $order->codigo }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Remover OS</button>
        </form>
    </div>
</div>
@endsection
