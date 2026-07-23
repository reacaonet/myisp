@extends('core::layouts.master')

@section('title', $monitor->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ $monitor->name }}</h2>
        <div class="flex items-center gap-1">
            <form method="POST" action="{{ route('crm.uptime.check', $monitor) }}" class="inline">
                @csrf
                <button type="submit" title="Verificar Agora" class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
            </form>
            <a href="{{ route('crm.uptime.edit', $monitor) }}" title="Editar" class="p-2 rounded-lg text-blue-600 border border-blue-200 hover:bg-blue-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            <a href="{{ route('crm.uptime.index') }}" title="Voltar" class="p-2 rounded-lg text-gray-600 border border-gray-300 hover:bg-gray-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></a>
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
