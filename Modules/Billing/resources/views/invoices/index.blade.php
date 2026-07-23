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
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('billing.invoices.show', $invoice) }}" title="Detalhes" class="p-1.5 rounded hover:bg-gray-100 text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('billing.invoices.edit', $invoice) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('billing.invoices.destroy', $invoice) }}" onsubmit="return confirm('Remover fatura {{ $invoice->invoice_number }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
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
