@extends('infra::layouts.master')

@section('title', 'Dashboard Infraestrutura')

@section('content')
{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Servidores</p>
            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['servers_total'] }}</p>
        <p class="text-xs text-green-600">{{ $stats['servers_active'] }} ativos</p>
    </div>
    <a href="{{ route('infra.provisioning.index') }}" class="bg-white rounded-xl shadow-sm p-4 border border-gray-200 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Provisionamentos</p>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['provisions_total'] }}</p>
        <p class="text-xs text-green-600">{{ $stats['provisions_ok'] }} ok · <span class="text-red-600">{{ $stats['provisions_failed'] }} falhas</span></p>
    </a>
    <a href="{{ route('infra.ftth.ctos.index') }}" class="bg-white rounded-xl shadow-sm p-4 border border-gray-200 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">CTOs</p>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['ctos_total'] }}</p>
        <p class="text-xs text-gray-500">{{ $stats['ctos_used_ports'] }}/{{ $stats['ctos_capacity'] }} portas usadas</p>
    </a>
    <a href="{{ route('infra.ftth.caixas.index') }}" class="bg-white rounded-xl shadow-sm p-4 border border-gray-200 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Caixas</p>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['caixas_total'] }}</p>
        <p class="text-xs text-gray-500">de emenda</p>
    </a>
    <a href="{{ route('infra.ftth.projects.index') }}" class="bg-white rounded-xl shadow-sm p-4 border border-gray-200 hover:shadow-md transition-shadow block">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Projetos FTTH</p>
            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['projects_total'] }}</p>
        <p class="text-xs text-gray-500">em andamento</p>
    </a>
    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Uptime</p>
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
        </div>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['uptime_up'] }}/{{ $stats['uptime_total'] }}</p>
        <p class="text-xs text-gray-500">monitores online</p>
    </div>
</div>

{{-- Servidores --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Servidores MikroTik</h3>
        <a href="{{ route('infra.mikrotik-servers.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Ver todos</a>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($servers as $server)
            <a href="{{ route('infra.mikrotik-servers.show', $server) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">{{ $server->name }}</p>
                        <p class="text-xs text-gray-500">{{ $server->ip }}:{{ $server->port }} · {{ strtoupper($server->type) }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($server->is_active)
                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Ativo</span>
                    @else
                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">Inativo</span>
                    @endif
                </div>
            </a>
        @empty
            <p class="px-4 py-6 text-sm text-gray-500 text-center">Nenhum servidor cadastrado.</p>
        @endforelse
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Provisionamentos recentes --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Provisionamentos Recentes</h3>
            <a href="{{ route('infra.provisioning.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Ver todos</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentProvisions as $p)
                <div class="px-4 py-3">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-800">{{ $p->login }}</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $p->success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $p->success ? 'OK' : 'Falha' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">
                        {{ $p->type === 'pppoe' ? 'PPPoE' : 'Hotspot' }} · {{ $p->mikrotikServer?->name ?? '—' }} · {{ $p->created_at->diffForHumans() }}
                    </p>
                    @if(!$p->success && $p->error)
                        <p class="text-xs text-red-600 mt-1 line-clamp-1">{{ $p->error }}</p>
                    @endif
                </div>
            @empty
                <p class="px-4 py-6 text-sm text-gray-500 text-center">Nenhum provisionamento ainda.</p>
            @endforelse
        </div>
    </div>

    {{-- CTOs recentes --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">CTOs Recentes</h3>
            <a href="{{ route('infra.ftth.ctos.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Ver todos</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentCtos as $cto)
                <a href="{{ route('infra.ftth.ctos.show', $cto) }}" class="px-4 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div>
                        <p class="font-medium text-gray-800">{{ $cto->name }}</p>
                        <p class="text-xs text-gray-500">{{ $cto->code }} · {{ $cto->city ?? '—' }}</p>
                    </div>
                    <span class="text-xs text-gray-500">{{ $cto->usage_percent }}% ocupado</span>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-gray-500 text-center">Nenhuma CTO cadastrada.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Backups --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Backups</h3>
            <a href="{{ route('infra.mikrotik-backups.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Ver todos</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recentBackups as $b)
                <div class="px-4 py-3">
                    <p class="font-medium text-gray-800 line-clamp-1">{{ $b->filename }}</p>
                    <p class="text-xs text-gray-500">{{ $b->server?->name ?? '—' }} · {{ $b->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <p class="px-4 py-6 text-sm text-gray-500 text-center">Nenhum backup ainda.</p>
            @endforelse
        </div>
    </div>

    {{-- Atalhos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 lg:col-span-2">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Atalhos Rápidos</h3>
        </div>
        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
            <a href="{{ route('infra.mikrotik-servers.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                Servidores
            </a>
            <a href="{{ route('infra.mikrotik.pppoe-active') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                Sessoes Ativas
            </a>
            <a href="{{ route('infra.provisioning.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Provisionamento
            </a>
            <a href="{{ route('infra.uptime.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Uptime
            </a>
            <a href="{{ route('infra.network-monitor.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Monitor de Rede
            </a>
            <a href="{{ route('infra.mikrotik-backups.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Backups
            </a>
            <a href="{{ route('infra.site-blocking.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                Bloqueio de Sites
            </a>
            <a href="{{ route('infra.hotspot-coupons.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                Cupons Hotspot
            </a>
            <a href="{{ route('infra.equipment.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                Equipamentos
            </a>
            <a href="{{ route('infra.manufacturers.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Fabricantes
            </a>
            <a href="{{ route('infra.ftth.ctos.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                CTOs
            </a>
            <a href="{{ route('infra.ftth.caixas.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Caixas
            </a>
            <a href="{{ route('infra.ftth.map') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Mapa da Rede
            </a>
            <a href="{{ route('infra.ftth.projects.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                Projetos FTTH
            </a>
            <a href="{{ route('infra.ftth.export.kml') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 hover:bg-blue-50 text-sm text-gray-700 hover:text-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exportar KML
            </a>
        </div>
    </div>
</div>
@endsection