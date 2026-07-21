@extends('crm::portal.layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Bem-vindo, {{ $client->name }}</h2>
            <p class="text-sm text-gray-500">{{ $client->document }} &middot; {{ $client->email ?? '-' }} &middot; {{ $client->cellphone ?? $client->phone ?? '-' }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Contratos</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_contracts'] }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['active_contracts'] }} ativos</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Faturas Pendentes</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['pending_invoices'] }}</p>
                <p class="text-xs text-gray-400 mt-1">R$ {{ number_format($stats['pending_amount'], 2, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Faturas Pagas</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['paid_invoices'] }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Ordens de Servico</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $client->serviceOrders->count() }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $stats['open_os'] }} abertas</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Chamados Abertos</p>
                <p class="text-3xl font-bold text-orange-600 mt-1">{{ $stats['open_tickets'] }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    @if($stats['last_invoice'] && $stats['last_invoice']->status !== 'paid')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Ultima Fatura</h3>
        @php $inv = $stats['last_invoice']; @endphp
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $inv->invoice_number }}</p>
                <p class="text-lg font-bold text-gray-900">R$ {{ number_format($inv->total, 2, ',', '.') }}</p>
                <p class="text-sm text-gray-500">Vencimento: {{ $inv->due_date->format('d/m/Y') }}</p>
            </div>
            <div class="text-right">
                @include('crm::clients._status_badge', ['status' => $inv->status])
            </div>
        </div>
        <a href="{{ route('crm.portal.invoices') }}" class="mt-4 inline-block text-sm text-blue-600 hover:underline">Ver todas as faturas</a>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Atalhos</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('crm.portal.invoices') }}" class="p-4 bg-blue-50 rounded-lg text-center hover:bg-blue-100 transition">
                <p class="font-medium text-blue-700 text-sm">Minhas Faturas</p>
            </a>
            <a href="{{ route('crm.portal.contracts') }}" class="p-4 bg-purple-50 rounded-lg text-center hover:bg-purple-100 transition">
                <p class="font-medium text-purple-700 text-sm">Meus Contratos</p>
            </a>
            <a href="{{ route('crm.portal.service-orders') }}" class="p-4 bg-orange-50 rounded-lg text-center hover:bg-orange-100 transition">
                <p class="font-medium text-orange-700 text-sm">Ordens de Servico</p>
            </a>
            <a href="{{ route('crm.portal.tickets.create') }}" class="p-4 bg-red-50 rounded-lg text-center hover:bg-red-100 transition">
                <p class="font-medium text-red-700 text-sm">Abrir Chamado</p>
            </a>
            <a href="{{ route('crm.portal.tickets') }}" class="p-4 bg-yellow-50 rounded-lg text-center hover:bg-yellow-100 transition">
                <p class="font-medium text-yellow-700 text-sm">Meus Chamados</p>
            </a>
            <a href="{{ route('crm.portal.profile') }}" class="p-4 bg-green-50 rounded-lg text-center hover:bg-green-100 transition">
                <p class="font-medium text-green-700 text-sm">Dados Pessoais</p>
            </a>
        </div>
    </div>
</div>

@if($client->serviceOrders->whereNotIn('status', ['closed', 'canceled'])->isNotEmpty())
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ordens de Servico Abertas</h3>
    @foreach($client->serviceOrders->whereNotIn('status', ['closed', 'canceled']) as $os)
    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
        <div>
            <p class="font-medium text-gray-900">{{ $os->codigo }} - {{ $os->servico ?? $os->tipo_servico }}</p>
            <p class="text-sm text-gray-500">{{ $os->emissao?->format('d/m/Y') }}</p>
        </div>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $os->status == 'open' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">{{ ucfirst($os->status) }}</span>
    </div>
    @endforeach
</div>
@endif
@endsection
