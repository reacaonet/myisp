@extends('core::layouts.master')

@section('title', 'Monitoramento Uptime')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Monitoramento Uptime</h2>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('crm.uptime.check-all') }}" class="inline">
                @csrf
                <button type="submit" title="Verificar Todos" class="p-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
            </form>
            <a href="{{ route('crm.uptime.create') }}" title="Novo Monitor" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></a>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Total</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-4">
            <p class="text-xs text-green-600 uppercase font-medium">UP</p>
            <p class="text-xl font-bold text-green-700">{{ $stats['up'] }}</p>
        </div>
        <div class="bg-red-50 rounded-xl shadow-sm border border-red-200 p-4">
            <p class="text-xs text-red-600 uppercase font-medium">DOWN</p>
            <p class="text-xl font-bold text-red-700">{{ $stats['down'] }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Nao Verificado</p>
            <p class="text-xl font-bold text-gray-600">{{ $stats['unknown'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Nome</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Host</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Tipo</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Servidor</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Latencia</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Ultima Verificacao</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($monitors as $monitor)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        @if($monitor->is_up === true)
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500"></span><span class="text-xs text-green-700">UP</span></span>
                        @elseif($monitor->is_up === false)
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500"></span><span class="text-xs text-red-700">DOWN</span></span>
                        @else
                            <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-gray-400"></span><span class="text-xs text-gray-500">-</span></span>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-medium"><a href="{{ route('crm.uptime.show', $monitor) }}" class="text-blue-600 hover:underline">{{ $monitor->name }}</a></td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $monitor->host }}:{{ $monitor->port }}</td>
                    <td class="px-4 py-3"><span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600">{{ strtoupper($monitor->type) }}</span></td>
                    <td class="px-4 py-3 text-gray-500">{{ $monitor->server?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">{{ $monitor->response_time_ms ? $monitor->response_time_ms . 'ms' : '-' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $monitor->last_check_at?->diffForHumans() ?? 'Nunca' }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-0.5">
                            <form method="POST" action="{{ route('crm.uptime.check', $monitor) }}" class="inline">
                                @csrf
                                <button type="submit" title="Verificar" class="p-1.5 rounded hover:bg-green-50 text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
                            </form>
                            <a href="{{ route('crm.uptime.edit', $monitor) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Nenhum monitor configurado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
