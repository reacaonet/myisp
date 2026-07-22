@extends('core::layouts.master')

@section('title', 'Monitoramento Uptime')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Monitoramento Uptime</h2>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('crm.uptime.check-all') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Verificar Todos</button>
            </form>
            <a href="{{ route('crm.uptime.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Novo Monitor</a>
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
                        <div class="flex items-center justify-center gap-2">
                            <form method="POST" action="{{ route('crm.uptime.check', $monitor) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 text-xs">Verificar</button>
                            </form>
                            <a href="{{ route('crm.uptime.edit', $monitor) }}" class="text-blue-600 hover:text-blue-800 text-xs">Editar</a>
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
