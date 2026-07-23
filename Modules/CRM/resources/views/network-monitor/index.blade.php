@extends('core::layouts.master')

@section('title', 'Monitoramento de Rede')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Servidores de Rede</h2>
        <button onclick="location.reload()" title="Atualizar" class="p-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($servers as $server)
            @php $stats = $serverStats[$server->id] ?? ['online' => false]; @endphp
            <div class="bg-white rounded-xl shadow-sm border {{ $stats['online'] ? 'border-green-200' : 'border-red-200' }}">
                <div class="p-4 border-b {{ $stats['online'] ? 'border-green-100 bg-green-50' : 'border-red-100 bg-red-50' }} flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $server->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $server->ip }}</p>
                    </div>
                    @if($stats['online'])
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Online</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Offline</span>
                    @endif
                </div>

                @if($stats['online'])
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Identidade</span>
                        <span class="font-medium text-gray-900">{{ $stats['identity'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Board</span>
                        <span class="font-medium text-gray-900">{{ $stats['board'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Versao</span>
                        <span class="font-medium text-gray-900">{{ $stats['version'] }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Uptime</span>
                        <span class="font-medium text-gray-900">{{ $stats['uptime'] }}</span>
                    </div>

                    <div class="pt-2">
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>CPU</span>
                            <span>{{ $stats['cpu_load'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-{{ $stats['cpu_load'] > 80 ? 'red' : ($stats['cpu_load'] > 50 ? 'yellow' : 'green') }}-500 h-2 rounded-full" style="width: {{ $stats['cpu_load'] }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Memoria</span>
                            <span>{{ number_format(($stats['total_memory'] - $stats['free_memory']) / 1048576, 1) }}MB / {{ number_format($stats['total_memory'] / 1048576, 1) }}MB</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php $memPct = $stats['total_memory'] > 0 ? (($stats['total_memory'] - $stats['free_memory']) / $stats['total_memory']) * 100 : 0; @endphp
                            <div class="bg-{{ $memPct > 80 ? 'red' : ($memPct > 50 ? 'yellow' : 'blue') }}-500 h-2 rounded-full" style="width: {{ $memPct }}%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                            <span>Disco</span>
                            <span>{{ number_format(($stats['total_hdd'] - $stats['free_hdd']) / 1048576, 1) }}MB / {{ number_format($stats['total_hdd'] / 1048576, 1) }}MB</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            @php $hddPct = $stats['total_hdd'] > 0 ? (($stats['total_hdd'] - $stats['free_hdd']) / $stats['total_hdd']) * 100 : 0; @endphp
                            <div class="bg-{{ $hddPct > 80 ? 'red' : ($hddPct > 50 ? 'yellow' : 'purple') }}-500 h-2 rounded-full" style="width: {{ $hddPct }}%"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-1 text-sm">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span class="text-gray-600">PPPoE:</span>
                            <span class="font-semibold text-gray-900">{{ $stats['pppoe_count'] }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-sm">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            <span class="text-gray-600">Hotspot:</span>
                            <span class="font-semibold text-gray-900">{{ $stats['hotspot_count'] }}</span>
                        </div>
                    </div>
                </div>
                @else
                <div class="p-4">
                    <p class="text-sm text-red-600">{{ $stats['error'] ?? 'Servidor inacessivel' }}</p>
                </div>
                @endif

                <div class="p-4 border-t border-gray-100 flex items-center gap-1">
                    @if($stats['online'])
                        <a href="{{ route('crm.network-monitor.show', $server) }}" title="Detalhes" class="flex-1 text-center px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-medium hover:bg-blue-700 flex items-center justify-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Detalhes</a>
                        <a href="{{ route('crm.network-monitor.active-users', $server) }}" title="Usuarios Ativos" class="flex-1 text-center px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-medium hover:bg-green-700 flex items-center justify-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg> Usuarios</a>
                    @else
                        <span class="flex-1 text-center px-3 py-1.5 bg-gray-200 text-gray-400 rounded-lg text-xs font-medium cursor-not-allowed">Indisponivel</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-gray-400">
                Nenhum servidor MikroTik ativo cadastrado.
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
setTimeout(() => location.reload(), 30000);
</script>
@endpush
@endsection
