@extends('core::layouts.master')

@section('title', $monitor->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ $monitor->name }}</h2>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('crm.uptime.check', $monitor) }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Verificar Agora</button>
            </form>
            <a href="{{ route('crm.uptime.edit', $monitor) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">Editar</a>
            <a href="{{ route('crm.uptime.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Status</p>
            @if($monitor->is_up === true)
                <p class="text-xl font-bold text-green-600">UP</p>
            @elseif($monitor->is_up === false)
                <p class="text-xl font-bold text-red-600">DOWN</p>
            @else
                <p class="text-xl font-bold text-gray-400">N/A</p>
            @endif
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Latencia</p>
            <p class="text-xl font-bold text-gray-900">{{ $monitor->response_time_ms ? $monitor->response_time_ms . 'ms' : '-' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Uptime 24h</p>
            <p class="text-xl font-bold {{ ($uptimePercent ?? 0) >= 99 ? 'text-green-600' : (($uptimePercent ?? 0) >= 95 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $uptimePercent !== null ? $uptimePercent . '%' : '-' }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Host</p>
            <p class="text-lg font-mono text-gray-900">{{ $monitor->host }}:{{ $monitor->port }}</p>
        </div>
    </div>

    @if($monitor->last_error)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
        <p class="text-sm font-medium text-red-700">Ultimo erro:</p>
        <p class="text-sm text-red-600">{{ $monitor->last_error }}</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Historico de Verificacoes</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Data/Hora</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Latencia</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Erro</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($checks as $check)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-xs">{{ $check->checked_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($check->is_up)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">UP</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">DOWN</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $check->response_time_ms ? $check->response_time_ms . 'ms' : '-' }}</td>
                    <td class="px-4 py-3 text-xs text-red-500">{{ $check->error_message ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Nenhuma verificacao registrada</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
