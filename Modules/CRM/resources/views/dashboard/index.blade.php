@extends('core::layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Clientes Ativos</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $active_clients }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Total Clientes</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $total_clients }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Contratos Ativos</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $active_contracts }}</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Planos</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $total_plans }}</p>
            </div>
            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Faturas Pendentes</p>
                <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $total_pending }}</p>
                <p class="text-xs text-gray-400 mt-1">R$ {{ number_format($pending_amount, 2, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Faturas Vencidas</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $total_overdue }}</p>
                <p class="text-xs text-gray-400 mt-1">R$ {{ number_format($overdue_amount, 2, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Faturas Pagas</p>
                <p class="text-3xl font-bold text-green-600 mt-1">{{ $total_paid }}</p>
                <p class="text-xs text-gray-400 mt-1">R$ {{ number_format($paid_amount, 2, ',', '.') }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">Receita do Mes</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">R$ {{ number_format(($monthly_revenue[now()->format('Y-m')] ?? 0), 2, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ now()->format('F/Y') }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Receita Mensal (6 meses)</h2>
        <canvas id="revenueChart" height="200"></canvas>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Faturas Vencidas</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-200">
                        <th class="pb-2 font-medium">Cliente</th>
                        <th class="pb-2 font-medium">Fatura</th>
                        <th class="pb-2 font-medium">Valor</th>
                        <th class="pb-2 font-medium">Vencimento</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_overdue as $inv)
                    <tr class="border-b border-gray-100">
                        <td class="py-2">
                            <a href="{{ route('crm.clients.show', $inv->client) }}" class="text-blue-600 hover:underline">{{ $inv->client->name }}</a>
                        </td>
                        <td class="py-2">
                            <a href="{{ route('billing.invoices.show', $inv) }}" class="text-blue-600 hover:underline">{{ $inv->invoice_number }}</a>
                        </td>
                        <td class="py-2 text-red-600 font-medium">R$ {{ number_format($inv->total, 2, ',', '.') }}</td>
                        <td class="py-2 text-gray-600">{{ $inv->due_date->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-400">Nenhuma fatura vencida.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Ultimos Clientes Cadastrados</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-200">
                    <th class="pb-3 font-medium">Nome</th>
                    <th class="pb-3 font-medium">Documento</th>
                    <th class="pb-3 font-medium">Telefone</th>
                    <th class="pb-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_clients as $client)
                <tr class="border-b border-gray-100">
                    <td class="py-3">
                        <a href="{{ route('crm.clients.show', $client) }}" class="text-blue-600 hover:underline font-medium">{{ $client->name }}</a>
                    </td>
                    <td class="py-3 text-gray-600">{{ $client->document }}</td>
                    <td class="py-3 text-gray-600">{{ $client->cellphone ?? $client->phone }}</td>
                    <td class="py-3">
                        @include('crm::clients._status_badge', ['status' => $client->status])
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-gray-400">Nenhum cliente cadastrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(isset($mikrotik_status) && count($mikrotik_status) > 0)
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Servidores MikroTik</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($mikrotik_status as $mk)
        <div class="border rounded-lg p-4 {{ $mk['online'] ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full {{ $mk['online'] ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <span class="font-medium text-gray-900">{{ $mk['server']->name }}</span>
                </div>
                <span class="text-xs text-gray-500">{{ $mk['server']->ip }}</span>
            </div>
            @if($mk['online'])
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div><span class="text-gray-500">CPU:</span> <span class="font-medium">{{ $mk['cpu'] }}%</span></div>
                <div><span class="text-gray-500">RAM:</span> <span class="font-medium">{{ number_format(($mk['memory_free'] ?? 0) / 1048576, 0) }} MB</span></div>
                <div><span class="text-gray-500">PPPoE:</span> <span class="font-medium text-blue-600">{{ $mk['pppoe_count'] }}</span></div>
                <div><span class="text-gray-500">Hotspot:</span> <span class="font-medium text-purple-600">{{ $mk['hotspot_count'] }}</span></div>
                <div class="col-span-2"><span class="text-gray-500">Uptime:</span> <span class="font-medium">{{ $mk['uptime'] }}</span></div>
            </div>
            @else
            <p class="text-xs text-red-600">Offline - {{ $mk['error'] ?? 'Sem conexao' }}</p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($monthly_revenue)) !!},
        datasets: [{
            label: 'Receita (R$)',
            data: {!! json_encode(array_values($monthly_revenue)) !!},
            backgroundColor: '#3b82f6',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR', {minimumFractionDigits: 2}) }
            }
        }
    }
});
</script>
@endpush
