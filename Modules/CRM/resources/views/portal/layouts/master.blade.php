<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Area do Cliente') - MyISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">
@php
    $portalUser = Auth::guard('client')->user();
    $portalInitials = mb_strtoupper(mb_substr(trim((string) ($portalUser?->name ?? 'C')), 0, 1));
@endphp
{{-- `x-data` no wrapper controla a gaveta: no desktop ela e ignorada e a
     sidebar fica sempre visivel (`lg:translate-x-0`). Sem isso o `w-64`
     fixo espremia a area de conteudo para ~160px no celular. --}}
<div x-data="{ open: false }" class="h-screen overflow-hidden lg:flex">
    <div class="fixed inset-0 z-40 bg-gray-900/60 lg:hidden"
         :class="open ? '' : 'hidden'"
         @click="open = false"
         aria-hidden="true"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 shrink-0 flex-col bg-gray-900 text-white transition-transform duration-200 ease-out lg:static lg:z-auto lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full'"
           @keydown.escape.window="open = false">
        <div class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-gray-700 px-5">
            <a href="{{ route('crm.portal.dashboard') }}" class="text-xl font-bold tracking-tight">My<span class="text-blue-400">ISP</span></a>
            <button type="button" class="-mr-2 p-2 text-gray-400 hover:text-white lg:hidden"
                    @click="open = false" aria-label="Fechar menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="border-b border-gray-800 px-5 py-2">
            <span class="inline-block rounded bg-blue-600 px-2 py-0.5 text-xs font-medium text-white">Area do Cliente</span>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <a href="{{ route('crm.portal.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('crm.portal.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="truncate">Dashboard</span>
            </a>

            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Servico</p>
            <a href="{{ route('crm.portal.invoices') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('crm.portal.invoices*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="truncate">Faturas</span>
                @php
                    $pendingCount = \Modules\Billing\Models\Invoice::where('client_id', $portalUser?->id)->whereIn('status', ['pending', 'overdue'])->count();
                @endphp
                @if($pendingCount > 0)
                <span class="ml-auto rounded-full bg-red-500 px-1.5 py-0.5 text-xs font-bold text-white">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('crm.portal.contracts') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('crm.portal.contracts*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="truncate">Contratos</span>
            </a>
            <a href="{{ route('crm.portal.service-orders') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('crm.portal.service-orders*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="truncate">Ordens de Servico</span>
            </a>
            <a href="{{ route('crm.portal.tickets') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('crm.portal.tickets*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span class="truncate">Chamados</span>
            </a>

            <p class="px-3 pb-1 pt-4 text-xs font-semibold uppercase tracking-wider text-gray-500">Conta</p>
            <a href="{{ route('crm.portal.profile') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('crm.portal.profile*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="truncate">Dados Pessoais</span>
            </a>
        </nav>

        <div class="shrink-0 border-t border-gray-700 p-4">
            <div class="flex items-center gap-3 text-sm">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">{{ $portalInitials }}</div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-white">{{ $portalUser?->name ?? 'Cliente' }}</p>
                    <form method="POST" action="{{ route('crm.portal.logout') }}" class="inline">
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
