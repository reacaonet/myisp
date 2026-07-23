@extends('core::layouts.master')

@section('title', 'Contratos')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Contratos</h2>
        <div class="flex gap-3">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Buscar por cliente..." value="{{ request('search') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Buscar</button>
            </form>
            <a href="{{ route('crm.contracts.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo Contrato
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Cliente</th>
                    <th class="px-6 py-4 font-medium">Plano</th>
                    <th class="px-6 py-4 font-medium">Ativacao</th>
                    <th class="px-6 py-4 font-medium">Vencimento</th>
                    <th class="px-6 py-4 font-medium">Valor</th>
                    <th class="px-6 py-4 font-medium">Servidor</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $contract)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('crm.clients.show', $contract->client) }}" class="text-blue-600 hover:underline font-medium">{{ $contract->client->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $contract->plan->name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $contract->activation_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-gray-600">Dia {{ $contract->due_day }}</td>
                    <td class="px-6 py-4 text-gray-900 font-medium">R$ {{ number_format($contract->plan->price - $contract->discount + $contract->acrescimo, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $contract->server?->name ?? '-' }}</td>
                    <td class="px-6 py-4">@include('crm::clients._status_badge', ['status' => $contract->status])</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('crm.contracts.show', $contract) }}" title="Detalhes" class="p-1.5 rounded hover:bg-blue-50 text-blue-600 inline-flex"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">Nenhum contrato encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($contracts->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $contracts->links() }}</div>
    @endif
</div>
@endsection
