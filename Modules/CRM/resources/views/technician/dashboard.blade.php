@extends('crm::technician.layouts.master')

@section('title', 'Dashboard')

@php
    $todayOrders = $serviceOrders->filter(fn($os) => $os->data_agendamento && $os->data_agendamento->isToday());
    $inProgressOrders = $serviceOrders->filter(fn($os) => $os->situacao === 'A');
@endphp

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Bem-vindo, {{ $technician->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $technician->cargo ?? 'Tecnico' }} &middot; {{ $technician->email ?? '-' }} &middot; {{ $technician->cellphone ?? $technician->phone ?? '-' }}</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-500">{{ now()->format('d/m/Y') }}</p>
            <p class="text-xs text-gray-400">{{ now()->translatedFormat('l') }}</p>
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

@if($inProgressOrders->isNotEmpty())
<div class="bg-yellow-50 rounded-xl shadow-sm border border-yellow-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-yellow-800 mb-4">Em Andamento Agora</h3>
    <div class="space-y-3">
        @foreach($inProgressOrders as $os)
        <a href="{{ route('technician.portal.service-orders.show', $os) }}" class="flex items-center justify-between p-3 bg-white rounded-lg border border-yellow-200 hover:shadow-sm transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $os->codigo }}</p>
                    <p class="text-sm text-gray-500">{{ $os->client->name }} - {{ $os->servico ?? $os->tipo_servico }}</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @endforeach
    </div>
</div>
@endif

@if($todayOrders->isNotEmpty())
<div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-blue-800 mb-4">Agenda de Hoje</h3>
    <div class="space-y-3">
        @foreach($todayOrders as $os)
        <a href="{{ route('technician.portal.service-orders.show', $os) }}" class="flex items-center justify-between p-3 bg-white rounded-lg border border-blue-200 hover:shadow-sm transition">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="font-medium text-gray-900">{{ $os->codigo }} @if($os->hora_agendamento) <span class="text-sm text-gray-500">as {{ $os->hora_agendamento }}</span> @endif</p>
                    <p class="text-sm text-gray-500">{{ $os->client->name }} - {{ $os->servico ?? $os->tipo_servico }}</p>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $os->situacao === 'O' ? 'bg-blue-100 text-blue-700' : ($os->situacao === 'A' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                {{ $os->situacao === 'O' ? 'Aberta' : ($os->situacao === 'A' ? 'Em Andamento' : 'Finalizada') }}
            </span>
        </a>
        @endforeach
    </div>
</div>
@endif

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
                            <a href="{{ route('technician.portal.service-orders.show', $os) }}" title="Ver" class="p-1.5 rounded hover:bg-blue-50 text-blue-600 inline-flex"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
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