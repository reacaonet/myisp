@extends('core::layouts.master')

@section('title', $server->name . ' - Detalhes')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('crm.network-monitor.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-lg font-semibold text-gray-800">{{ $server->name }}</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Online</span>
        </div>
        <div class="flex items-center gap-1">
            <a href="{{ route('crm.network-monitor.active-users', $server) }}" title="Usuarios Ativos" class="p-2 bg-green-600 text-white rounded-lg hover:bg-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></a>
            <button onclick="location.reload()" title="Atualizar" class="p-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Identidade</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ $identity[0]['name'] ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Versao</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ $resources[0]['version'] ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Uptime</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ $resources[0]['uptime'] ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Board</p>
            <p class="text-lg font-bold text-gray-900 mt-1">{{ $resources[0]['board-name'] ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase mb-2">CPU</p>
            @php $cpuLoad = $resources[0]['cpu-load'] ?? 0; @endphp
            <div class="flex items-end gap-2">
                <span class="text-3xl font-bold text-gray-900">{{ $cpuLoad }}%</span>
                <span class="text-sm text-gray-500 mb-1">{{ $resources[0]['cpu-count'] ?? 0 }} cores</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                <div class="bg-{{ $cpuLoad > 80 ? 'red' : ($cpuLoad > 50 ? 'yellow' : 'green') }}-500 h-3 rounded-full transition-all" style="width: {{ $cpuLoad }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase mb-2">Memoria</p>
            @php
                $totalMem = $resources[0]['total-memory'] ?? 0;
                $freeMem = $resources[0]['free-memory'] ?? 0;
                $usedMem = $totalMem - $freeMem;
                $memPct = $totalMem > 0 ? ($usedMem / $totalMem) * 100 : 0;
            @endphp
            <div class="flex items-end gap-2">
                <span class="text-3xl font-bold text-gray-900">{{ number_format($usedMem / 1048576, 1) }}MB</span>
                <span class="text-sm text-gray-500 mb-1">/ {{ number_format($totalMem / 1048576, 1) }}MB</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                <div class="bg-{{ $memPct > 80 ? 'red' : ($memPct > 50 ? 'yellow' : 'blue') }}-500 h-3 rounded-full transition-all" style="width: {{ $memPct }}%"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase mb-2">Disco</p>
            @php
                $totalHdd = $resources[0]['total-hdd-space'] ?? 0;
                $freeHdd = $resources[0]['free-hdd-space'] ?? 0;
                $usedHdd = $totalHdd - $freeHdd;
                $hddPct = $totalHdd > 0 ? ($usedHdd / $totalHdd) * 100 : 0;
            @endphp
            <div class="flex items-end gap-2">
                <span class="text-3xl font-bold text-gray-900">{{ number_format($usedHdd / 1048576, 1) }}MB</span>
                <span class="text-sm text-gray-500 mb-1">/ {{ number_format($totalHdd / 1048576, 1) }}MB</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                <div class="bg-{{ $hddPct > 80 ? 'red' : ($hddPct > 50 ? 'yellow' : 'purple') }}-500 h-3 rounded-full transition-all" style="width: {{ $hddPct }}%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">PPPoE Ativos ({{ is_array($pppoeActive['pppoe'] ?? null) ? count($pppoeActive['pppoe']) : 0 }})</h3>
            </div>
            <div class="overflow-x-auto max-h-64">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-2 font-medium">Login</th>
                            <th class="px-4 py-2 font-medium">Servico</th>
                            <th class="px-4 py-2 font-medium">Uptime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pppoeActive['pppoe'] ?? [] as $user)
                        <tr class="border-b border-gray-100">
                            <td class="px-4 py-2 font-mono text-xs">{{ $user['name'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $user['service'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ $user['uptime'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Nenhum usuario PPPoE ativo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Hotspot Ativos ({{ is_array($hotspotActive['hotspot'] ?? null) ? count($hotspotActive['hotspot']) : 0 }})</h3>
            </div>
            <div class="overflow-x-auto max-h-64">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                            <th class="px-4 py-2 font-medium">Login</th>
                            <th class="px-4 py-2 font-medium">IP</th>
                            <th class="px-4 py-2 font-medium">Uptime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotspotActive['hotspot'] ?? [] as $user)
                        <tr class="border-b border-gray-100">
                            <td class="px-4 py-2 font-mono text-xs">{{ $user['user'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 font-mono text-xs">{{ $user['address'] ?? '-' }}</td>
                            <td class="px-4 py-2 text-gray-600 text-xs">{{ $user['uptime'] ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-400">Nenhum usuario Hotspot ativo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Interfaces</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                        <th class="px-4 py-2 font-medium">Nome</th>
                        <th class="px-4 py-2 font-medium">Tipo</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium">RX</th>
                        <th class="px-4 py-2 font-medium">TX</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($interfaces as $iface)
                    <tr class="border-b border-gray-100 {{ ($iface['disabled'] ?? 'false') == 'true' ? 'bg-gray-50 opacity-60' : '' }}">
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $iface['name'] }}</td>
                        <td class="px-4 py-2 text-gray-600">{{ $iface['type'] ?? '-' }}</td>
                        <td class="px-4 py-2">
                            @if(($iface['running'] ?? 'false') == 'true')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Up</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Down</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-600 font-mono text-xs">{{ $iface['rx-rate'] ?? '-' }}</td>
                        <td class="px-4 py-2 text-gray-600 font-mono text-xs">{{ $iface['tx-rate'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
setTimeout(() => location.reload(), 15000);
</script>
@endpush
@endsection
