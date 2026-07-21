@extends('crm::portal.layouts.master')

@section('title', 'Minhas Faturas')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Minhas Faturas</h3>
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
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-3 font-mono text-gray-900">{{ $inv->invoice_number }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ str_pad($inv->mes ?? $inv->due_date->month, 2, '0', STR_PAD_LEFT) }}/{{ $inv->ano ?? $inv->due_date->year }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $inv->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-3 text-gray-900 font-medium">R$ {{ number_format($inv->total, 2, ',', '.') }}</td>
                    <td class="px-6 py-3">@include('crm::clients._status_badge', ['status' => $inv->status])</td>
                    <td class="px-6 py-3">
                        <a href="{{ route('crm.portal.invoices.show', $inv) }}" class="text-blue-600 hover:underline text-xs">Detalhes</a>
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
