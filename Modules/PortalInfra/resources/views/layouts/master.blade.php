@php
    $user = Auth::user();
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Infraestrutura') - MyISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
            <div class="h-16 flex items-center px-6 border-b border-gray-700">
                <a href="{{ route('infra.dashboard') }}" class="text-xl font-bold tracking-tight">My<span class="text-blue-400">ISP</span></a>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                <a href="{{ route('infra.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                {{-- MikroTik --}}
                @if($user && $user->hasPermission('mikrotik_servers'))
                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-4 mb-2">MikroTik</p>
                <a href="{{ route('infra.mikrotik-servers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.mikrotik-servers.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    Servidores
                </a>
                <a href="{{ route('infra.mikrotik.pppoe-active') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.mikrotik.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    Sessoes Ativas
                </a>
                @endif

                {{-- Provisionamento --}}
                @if($user && $user->hasPermission('provisioning'))
                <a href="{{ route('infra.provisioning.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.provisioning.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Provisionamento
                </a>
                @endif

                {{-- Monitoramento --}}
                @if($user && ($user->hasPermission('uptime') || $user->hasPermission('network_monitor') || $user->hasPermission('backups')))
                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-4 mb-2">Monitoramento</p>
                @if($user->hasPermission('uptime'))
                <a href="{{ route('infra.uptime.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.uptime.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Uptime
                </a>
                @endif
                @if($user->hasPermission('network_monitor'))
                <a href="{{ route('infra.network-monitor.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.network-monitor.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Monitor de Rede
                </a>
                @endif
                @if($user->hasPermission('backups'))
                <a href="{{ route('infra.mikrotik-backups.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.mikrotik-backups.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Backups
                </a>
                @endif
                @endif

                {{-- Rede --}}
                @if($user && ($user->hasPermission('site_blocking') || $user->hasPermission('hotspot_coupons')))
                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-4 mb-2">Rede</p>
                @if($user->hasPermission('site_blocking'))
                <a href="{{ route('infra.site-blocking.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.site-blocking.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Bloqueio de Sites
                </a>
                @endif
                @if($user->hasPermission('hotspot_coupons'))
                <a href="{{ route('infra.hotspot-coupons.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.hotspot-coupons.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    Cupons Hotspot
                </a>
                @endif
                @endif

                {{-- Equipamentos --}}
                @if($user && ($user->hasPermission('equipment') || $user->hasPermission('manufacturers')))
                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-4 mb-2">Equipamentos</p>
                @if($user->hasPermission('equipment'))
                <a href="{{ route('infra.equipment.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.equipment.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    Equipamentos
                </a>
                @endif
                @if($user->hasPermission('manufacturers'))
                <a href="{{ route('infra.manufacturers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.manufacturers.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Fabricantes
                </a>
                @endif
                @endif

                {{-- FTTH --}}
                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-4 mb-2">FTTH</p>
                <a href="{{ route('infra.ftth.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.ftth.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Dashboard FTTH
                </a>
                <a href="{{ route('infra.ftth.ctos.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.ftth.ctos.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    CTOs
                </a>
                <a href="{{ route('infra.ftth.caixas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.ftth.caixas.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Caixas de Emenda
                </a>
                <a href="{{ route('infra.ftth.map') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.ftth.map') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    Mapa da Rede
                </a>
                <a href="{{ route('infra.ftth.generate') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.ftth.generate') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Gerar por Rua
                </a>
                <a href="{{ route('infra.ftth.generate.cities') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.ftth.generate.cities') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                    Gerar por Cidades
                </a>
                <a href="{{ route('infra.ftth.export.kml') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('infra.ftth.export.kml*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Exportar KML
                </a>

                {{-- Links --}}
                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-6 mb-2">Links</p>
                <a href="{{ route('crm.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
                    Voltar ao CRM
                </a>

            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center gap-3 text-sm text-gray-400">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">{{ substr(Auth::user()?->name ?? 'A', 0, 1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-white">{{ Auth::user()?->name ?? 'Administrador' }}</p>
                        @if(Auth::user()?->group)
                            <p class="text-xs text-gray-500">{{ Auth::user()->group->name }}</p>
                        @endif
                        <form method="POST" action="{{ route('infra.logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-300">Sair</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Infraestrutura')</h1>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <span id="clock"></span>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleString('pt-BR');
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
    @stack('scripts')
</body>
</html>
