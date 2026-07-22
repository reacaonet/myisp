@extends('core::layouts.master')

@section('title', 'Relatorios')

@section('content')
<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Relatorios</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('billing.reports.invoices-by-due-date') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:border-blue-300 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Faturas por Vencimento</h3>
                    <p class="text-sm text-gray-500">Faturas filtradas por data de vencimento</p>
                </div>
            </div>
        </a>
        <a href="{{ route('billing.reports.invoices-by-status', ['status' => 'pending']) }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:border-yellow-300 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Faturas Abertas</h3>
                    <p class="text-sm text-gray-500">Faturas pendentes de pagamento</p>
                </div>
            </div>
        </a>
        <a href="{{ route('billing.reports.invoices-by-status', ['status' => 'paid']) }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:border-green-300 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Faturas Pagas</h3>
                    <p class="text-sm text-gray-500">Faturas ja liquidadas</p>
                </div>
            </div>
        </a>
        <a href="{{ route('billing.reports.invoices-by-status', ['status' => 'overdue']) }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:border-red-300 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-red-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Faturas Bloqueadas</h3>
                    <p class="text-sm text-gray-500">Faturas com bloqueio automatico</p>
                </div>
            </div>
        </a>
        <a href="{{ route('billing.reports.subscribers') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:border-purple-300 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Clientes Assinantes</h3>
                    <p class="text-sm text-gray-500">Contratos ativos e receita</p>
                </div>
            </div>
        </a>
        <a href="{{ route('billing.reports.plans-vs-clients') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:border-indigo-300 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Planos x Clientes</h3>
                    <p class="text-sm text-gray-500">Distribuicao de clientes por plano</p>
                </div>
            </div>
        </a>
        <a href="{{ route('billing.reports.cash-flow') }}" class="block p-6 bg-white rounded-xl shadow-sm border border-gray-200 hover:border-teal-300 hover:shadow-md transition">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-teal-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Movimento do Caixa</h3>
                    <p class="text-sm text-gray-500">Fluxo de caixa no periodo</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection
