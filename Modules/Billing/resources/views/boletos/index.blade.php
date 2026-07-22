@extends('core::layouts.master')

@section('title', 'Gerar Boleto')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Geracao de Boleto</h2>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">Configuracao Bancaria</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-500">Banco:</span><p class="font-medium">{{ $bankSettings['bank'] ?? 'Nao configurado' }}</p></div>
            <div><span class="text-gray-500">Agencia:</span><p class="font-medium">{{ $bankSettings['agency'] ?? 'N/A' }}</p></div>
            <div><span class="text-gray-500">Conta:</span><p class="font-medium">{{ $bankSettings['account'] ?? 'N/A' }}</p></div>
            <div><span class="text-gray-500">Cedente:</span><p class="font-medium">{{ $bankSettings['company'] ?? 'N/A' }}</p></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Cliente</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Fatura</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Vencimento</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Valor</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $invoice->client->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 font-medium">R$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center space-x-2">
                        <a href="{{ route('billing.boleto.print', $invoice) }}" target="_blank" class="text-green-600 hover:underline text-xs">Imprimir</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhuma fatura pendente para gerar boleto.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
