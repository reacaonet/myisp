@extends('core::layouts.master')

@section('title', 'Faturas por Vencimento')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Faturas por Vencimento</h2>
        <a href="{{ route('billing.reports.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Data Inicio</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Data Fim</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Total</p>
            <p class="text-xl font-bold text-gray-900">R$ {{ number_format($stats['total'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-400">{{ $stats['count'] }} faturas</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Pendentes</p>
            <p class="text-xl font-bold text-yellow-600">R$ {{ number_format($stats['pending'], 2, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Pagas</p>
            <p class="text-xl font-bold text-green-600">R$ {{ number_format($stats['paid'], 2, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Atrasadas</p>
            <p class="text-xl font-bold text-red-600">R$ {{ number_format($stats['overdue'], 2, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Qtde</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['count'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Vencimento</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Cliente</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Fatura</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Plano</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Valor</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 font-medium">{{ $invoice->client?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('billing.invoices.show', $invoice) }}" class="text-blue-600 hover:underline">{{ $invoice->invoice_number }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $invoice->contract?->plan?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium">R$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        @include('billing::partials._status_badge', ['status' => $invoice->status])
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhuma fatura encontrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
