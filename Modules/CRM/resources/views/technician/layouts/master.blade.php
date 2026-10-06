<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal do Tecnico') - MyISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">
@php
    $techUser = Auth::guard('technician')->user();
    $techInitials = $techUser?->initials() ?? 'T';
@endphp
{{-- `flex` em todos os breakpoints, e nao so em `lg`. Abaixo de `lg` o
     wrapper virava `display: block`: o div de conteudo deixava de ser item
     flex, o `flex-1` do `<main>` nao resolvia altura e o `overflow: hidden`
     do pai cortava a tela no meio - sem barra de rolagem. No celular a
     sidebar e `fixed`, ou seja, sai do fluxo, entao o unico item flex da
     linha e o de conteudo. --}}
<div x-data="{ open: false }" class="flex h-screen overflow-hidden supports-[height:100dvh]:h-dvh">
    <div class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden"
         :class="open ? '' : 'hidden'"
         @click="open = false"
         aria-hidden="true"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 flex-col bg-gray-900 text-white transition-transform duration-200 ease-out lg:static lg:z-auto lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full'"
           @keydown.escape.window="open = false">
        <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-gray-700 px-5">
            <a href="{{ route('technician.portal.dashboard') }}" class="text-xl font-bold tracking-tight">My<span class="text-blue-400">ISP</span></a>
            <button type="button" class="-mr-2 p-2 text-gray-400 hover:text-white lg:hidden"
                    @click="open = false" aria-label="Fechar menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="border-b border-gray-800 px-5 py-2">
            <span class="inline-block rounded bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">Tecnico</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <a href="{{ route('technician.portal.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('technician.portal.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="truncate">Dashboard</span>
            </a>
            <a href="{{ route('technician.portal.ftth') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('technician.portal.ftth', 'technician.portal.ftth.map') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="truncate">Rede FTTH</span>
            </a>
            <a href="{{ route('technician.portal.ftth.map') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('technician.portal.ftth.map*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 19.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span class="truncate">Mapa da Rede</span>
            </a>
            <a href="{{ route('technician.portal.service-orders') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('technician.portal.service-orders*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="truncate">Minhas OS</span>
                @php
                    $todayCount = $techUser?->serviceOrders()->whereDate('data_agendamento', today())->count() ?? 0;
                @endphp
                @if($todayCount > 0)
                <span class="ml-auto rounded-full bg-yellow-500 px-1.5 py-0.5 text-xs font-bold text-white">{{ $todayCount }}</span>
                @endif
            </a>

            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Conta</p>
            <a href="{{ route('technician.portal.profile') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('technician.portal.profile*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="truncate">Meu Perfil</span>
            </a>
        </nav>

        <div class="shrink-0 border-t border-gray-700 p-4">
            <div class="flex items-center gap-3 text-sm">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">{{ $techInitials }}</div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-white">{{ $techUser?->name ?? 'Tecnico' }}</p>
                    <form method="POST" action="{{ route('technician.portal.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-gray-500 hover:text-gray-300">Sair</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
        <header class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white px-4 sm:px-6">
            <button type="button" class="-ml-2 rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden"
                    @click="open = true" aria-label="Abrir menu" :aria-expanded="open">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="min-w-0 flex-1 truncate text-base font-semibold text-gray-800 sm:text-lg">@yield('title', 'Dashboard')</h1>
            <span id="clock" class="hidden shrink-0 text-sm text-gray-500 sm:block"></span>
        </header>
        <main class="flex-1 overflow-y-auto p-4 sm:p-6">
            @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script>
    function updateClock() {
        var el = document.getElementById('clock');
        if (el) {
            el.textContent = new Date().toLocaleString('pt-BR');
        }
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@stack('scripts')
</body>
</html>
