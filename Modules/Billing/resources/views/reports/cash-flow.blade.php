@extends('core::layouts.master')

@section('title', 'Movimento do Caixa')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Movimento do Caixa</h2>
        <a href="{{ route('billing.reports.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Data Inicio</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Data Fim</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
    </form>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-4">
            <p class="text-xs text-green-600 uppercase font-medium">Entradas (Pagamentos)</p>
            <p class="text-xl font-bold text-green-700">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</p>
        </div>
        <div class="bg-red-50 rounded-xl shadow-sm border border-red-200 p-4">
            <p class="text-xs text-red-600 uppercase font-medium">Saidas</p>
            <p class="text-xl font-bold text-red-700">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-4">
            <p class="text-xs text-blue-600 uppercase font-medium">Saldo do Periodo</p>
            <p class="text-xl font-bold {{ ($totalRecebido - $totalSaidas) >= 0 ? 'text-blue-700' : 'text-red-700' }}">
                R$ {{ number_format($totalRecebido - $totalSaidas, 2, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Detalhamento Diario</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Data</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Pagamentos</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Entradas</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Saidas</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Saldo Dia</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($dailyData as $day)
                @php $dayBalance = $day['payments'] + $day['entries'] - $day['exits']; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y (D)') }}</td>
                    <td class="px-4 py-3 text-right text-green-600">{{ $day['payments'] > 0 ? 'R$ ' . number_format($day['payments'], 2, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-blue-600">{{ $day['entries'] > 0 ? 'R$ ' . number_format($day['entries'], 2, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right text-red-600">{{ $day['exits'] > 0 ? 'R$ ' . number_format($day['exits'], 2, ',', '.') : '-' }}</td>
                    <td class="px-4 py-3 text-right font-medium {{ $dayBalance >= 0 ? 'text-green-700' : 'text-red-700' }}">
                        R$ {{ number_format($dayBalance, 2, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhum dado no periodo</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
