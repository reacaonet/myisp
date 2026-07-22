@extends('core::layouts.master')

@section('title', 'Clientes Assinantes')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Clientes Assinantes</h2>
        <a href="{{ route('billing.reports.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Plano</label>
            <select name="plan_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos</option>
                @foreach($plans as $plan)
                <option value="{{ $plan->id }}" {{ $planId == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
    </form>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Total Assinantes</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Receita Mensal</p>
            <p class="text-xl font-bold text-green-600">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Planos Ativos</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['by_plan']->count() }}</p>
        </div>
    </div>

    @if($stats['by_plan']->count())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <h3 class="font-semibold text-gray-900 mb-3">Distribuicao por Plano</h3>
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200">
                <tr>
                    <th class="text-left pb-2 font-medium text-gray-500">Plano</th>
                    <th class="text-right pb-2 font-medium text-gray-500">Clientes</th>
                    <th class="text-right pb-2 font-medium text-gray-500">Receita</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats['by_plan'] as $item)
                <tr class="border-b border-gray-100">
                    <td class="py-2 font-medium">{{ $item['plan'] }}</td>
                    <td class="py-2 text-right">{{ $item['count'] }}</td>
                    <td class="py-2 text-right">R$ {{ number_format($item['revenue'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Cliente</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Plano</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Ativacao</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Servidor</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Valor</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($contracts as $contract)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $contract->client?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $contract->plan?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $contract->activation_date?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $contract->server?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium">R$ {{ number_format($contract->plan?->price ?? 0, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativo</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhum contrato ativo</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
