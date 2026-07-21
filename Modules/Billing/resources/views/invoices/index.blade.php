@extends('core::layouts.master')

@section('title', 'Faturas')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">A Receber (Pendente)</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">R$ {{ number_format($stats['pending'], 2, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Vencido</p>
        <p class="text-2xl font-bold text-red-600 mt-1">R$ {{ number_format($stats['overdue'], 2, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Recebido</p>
        <p class="text-2xl font-bold text-green-600 mt-1">R$ {{ number_format($stats['paid'], 2, ',', '.') }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Faturas</h2>
        <div class="flex gap-3 flex-wrap">
            <form method="GET" class="flex gap-2">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    <option value="pending" @selected(request('status') == 'pending')>Pendente</option>
                    <option value="paid" @selected(request('status') == 'paid')>Pago</option>
                    <option value="overdue" @selected(request('status') == 'overdue')>Vencido</option>
                    <option value="canceled" @selected(request('status') == 'canceled')>Cancelado</option>
                </select>
                <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-48">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Buscar</button>
            </form>
            <a href="{{ route('billing.invoices.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Fatura
            </a>
            <form method="POST" action="{{ route('billing.invoices.generate') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                    Gerar Faturas
                </button>
            </form>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Fatura</th>
                    <th class="px-6 py-4 font-medium">Cliente</th>
                    <th class="px-6 py-4 font-medium">Valor</th>
                    <th class="px-6 py-4 font-medium">Vencimento</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $invoice->invoice_number }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('billing.invoices.show', $invoice) }}" class="text-blue-600 hover:underline font-medium">{{ $invoice->client->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-gray-900 font-medium">R$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">@include('billing::partials._status_badge', ['status' => $invoice->status])</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('billing.invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Detalhes</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <a href="{{ route('billing.invoices.edit', $invoice) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Editar</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <form method="POST" action="{{ route('billing.invoices.destroy', $invoice) }}" onsubmit="return confirm('Remover fatura {{ $invoice->invoice_number }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">Nenhuma fatura encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
