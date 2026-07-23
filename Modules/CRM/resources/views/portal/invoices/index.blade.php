@extends('crm::portal.layouts.master')

@section('title', 'Minhas Faturas')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-800">Minhas Faturas</h3>
        <div class="flex gap-2">
            <a href="{{ route('crm.portal.invoices') }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ !request('status') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Todas
            </a>
            <a href="{{ route('crm.portal.invoices', ['status' => 'pending']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ request('status') === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Abertas
            </a>
            <a href="{{ route('crm.portal.invoices', ['status' => 'paid']) }}" class="px-3 py-1.5 text-xs font-medium rounded-lg {{ request('status') === 'paid' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Pagas
            </a>
        </div>
    </div>

    @if($invoices->isEmpty())
    <div class="p-12 text-center text-gray-400">Nenhuma fatura encontrada.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 font-medium">Numero</th>
                    <th class="px-6 py-3 font-medium">Referencia</th>
                    <th class="px-6 py-3 font-medium">Vencimento</th>
                    <th class="px-6 py-3 font-medium">Valor</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                <tr class="border-b border-gray-100 hover:bg-gray-50 {{ $inv->status === 'overdue' ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-3 font-mono text-gray-900">{{ $inv->invoice_number }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ str_pad($inv->mes ?? $inv->due_date->month, 2, '0', STR_PAD_LEFT) }}/{{ $inv->ano ?? $inv->due_date->year }}</td>
                    <td class="px-6 py-3 {{ $inv->due_date->isPast() && $inv->status !== 'paid' ? 'text-red-600 font-medium' : 'text-gray-600' }}">{{ $inv->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-gray-900 font-medium">R$ {{ number_format($inv->total, 2, ',', '.') }}</td>
                    <td class="px-6 py-3">@include('crm::clients._status_badge', ['status' => $inv->status])</td>
                    <td class="px-6 py-3">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('crm.portal.invoices.show', $inv) }}" title="Detalhes" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            @if(in_array($inv->status, ['pending', 'overdue']))
                            <a href="{{ route('crm.portal.invoices.boleto', $inv) }}" title="Ver Boleto" class="p-1.5 rounded hover:bg-gray-100 text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-gray-200">
        {{ $invoices->links() }}
    </div>
    @endif

    <div class="p-6 border-t border-gray-200 text-center">
        <a href="{{ route('crm.portal.dashboard') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar ao Dashboard</a>
    </div>
</div>
@endsection
