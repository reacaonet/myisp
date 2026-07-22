@extends('crm::technician.layouts.master')

@section('title', 'Minhas Ordens de Servico')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
        <h2 class="text-xl font-bold text-gray-900">Minhas Ordens de Servico</h2>
        <div class="flex items-center gap-2 flex-wrap">
            <select wire:model="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Todas</option>
                <option value="active">Aberta</option>
                <option value="in_progress">Em Andamento</option>
                <option value="closed">Fechada</option>
                <option value="canceled">Cancelada</option>
            </select>
        </div>
    </div>

    @if($serviceOrders->isEmpty())
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma ordem de servico</h3>
        <p class="mt-1 text-sm text-gray-500">Voce nao possui ordens de servico atribuidas.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">OS</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servico</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agendamento</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Situacao</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($serviceOrders as $os)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $os->codigo }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $os->client->name }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">{{ $os->servico ?? $os->tipo_servico }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ $os->data_agendamento?->format('d/m/Y') ?? '-' }}
                        @if($os->hora_agendamento) &nbsp;{{ $os->hora_agendamento }} @endif
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $os->situacao === 'O' ? 'bg-blue-100 text-blue-700' : ($os->situacao === 'A' ? 'bg-yellow-100 text-yellow-700' : ($os->situacao === 'F' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')) }}">
                            {{ $os->situacao === 'O' ? 'Aberta' : ($os->situacao === 'A' ? 'Em Andamento' : ($os->situacao === 'F' ? 'Finalizada' : 'Cancelada')) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $os->status === 'active' ? 'bg-blue-100 text-blue-700' : ($os->status === 'in_progress' ? 'bg-purple-100 text-purple-700' : ($os->status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700')) }}">
                            {{ ucfirst(str_replace('_', ' ', $os->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-right">
                        <a href="{{ route('technician.portal.service-orders.show', $os) }}" class="text-blue-600 hover:underline">Ver</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex justify-center">
        {{ $serviceOrders->links() }}
    </div>
    @endif
</div>
@endsection