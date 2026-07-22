@extends('core::layouts.master')

@section('title', 'Planos x Clientes')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Planos x Clientes</h2>
        <a href="{{ route('billing.reports.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Total Assinantes</p>
            <p class="text-xl font-bold text-gray-900">{{ $totalActive }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Receita Mensal Total</p>
            <p class="text-xl font-bold text-green-600">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Plano</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Preco</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Clientes Ativos</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Receita</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">% do Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($plans as $plan)
                @php
                    $revenue = $plan->active_contracts * $plan->price;
                    $percent = $totalActive > 0 ? ($plan->active_contracts / $totalActive * 100) : 0;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $plan->name }}</td>
                    <td class="px-4 py-3 text-right">R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">{{ $plan->active_contracts }}</td>
                    <td class="px-4 py-3 text-right font-medium">R$ {{ number_format($revenue, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-20 bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                            <span class="text-xs text-gray-500 w-10 text-right">{{ number_format($percent, 1) }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhum plano encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
