@extends('crm::technician.layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Bem-vindo, {{ $technician->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $technician->cargo ?? 'Tecnico' }} &middot; {{ $technician->email ?? '-' }} &middot; {{ $technician->cellphone ?? $technician->phone ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">OS Atribuidas</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_assigned'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Em Aberto</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ $stats['open'] }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Em Andamento</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['in_progress'] }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Concluidas (Hoje)</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['completed_today'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Minhas Ordens de Servico</h3>
    @if($serviceOrders->isEmpty())
        <p class="text-gray-500 text-center py-8">Nenhuma ordem de servico atribuida.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-500 border-b border-gray-200">
                        <th class="pb-3 font-medium">Código</th>
                        <th class="pb-3 font-medium">Cliente</th>
                        <th class="pb-3 font-medium">Serviço</th>
                        <th class="pb-3 font-medium">Agendamento</th>
                        <th class="pb-3 font-medium">Situação</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($serviceOrders as $os)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 text-sm font-medium text-gray-900">{{ $os->codigo }}</td>
                        <td class="py-3 text-sm text-gray-600">{{ $os->client->name }}</td>
                        <td class="py-3 text-sm text-gray-600">{{ $os->servico ?? $os->tipo_servico }}</td>
                        <td class="py-3 text-sm text-gray-600">
                            {{ $os->data_agendamento?->format('d/m/Y') }}
                            @if($os->hora_agendamento) &nbsp;{{ $os->hora_agendamento }} @endif
                        </td>
                        <td class="py-3 text-sm">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $os->situacao === 'O' ? 'bg-blue-100 text-blue-700' : ($os->situacao === 'A' ? 'bg-yellow-100 text-yellow-700' : ($os->situacao === 'F' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ $os->situacao === 'O' ? 'Aberta' : ($os->situacao === 'A' ? 'Em Andamento' : ($os->situacao === 'F' ? 'Finalizada' : 'Cancelada')) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $os->status === 'active' ? 'bg-blue-100 text-blue-700' : ($os->status === 'closed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                {{ ucfirst($os->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-sm text-right">
                            <a href="{{ route('technician.portal.service-orders.show', $os) }}" class="text-blue-600 hover:underline text-sm">Ver</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Atalhos Rapidos</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('technician.portal.service-orders') }}" class="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition">
            <p class="font-medium text-blue-700 text-sm">Todas as Minhas OS</p>
        </a>
        <a href="{{ route('technician.portal.service-orders', ['status' => 'open']) }}" class="p-4 bg-yellow-50 rounded-lg text-center hover:bg-yellow-100 transition">
            <p class="font-medium text-yellow-700 text-sm">OS em Aberto</p>
        </a>
        <a href="{{ route('technician.portal.service-orders', ['status' => 'in_progress']) }}" class="p-4 bg-purple-50 rounded-lg text-center hover:bg-purple-100 transition">
            <p class="font-medium text-purple-700 text-sm">Em Andamento</p>
        </a>
        <a href="{{ route('technician.portal.dashboard') }}" class="p-4 bg-green-50 rounded-lg text-center hover:bg-green-100 transition">
            <p class="font-medium text-green-700 text-sm">Dashboard</p>
        </a>
    </div>
</div>
@endsection