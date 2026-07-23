<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MyISP') - MyISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        <aside class="w-64 bg-gray-900 text-white flex flex-col shrink-0">
            <div class="h-16 flex items-center px-6 border-b border-gray-700">
                <a href="{{ route('crm.dashboard') }}" class="text-xl font-bold tracking-tight">My<span class="text-blue-400">ISP</span></a>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mb-2">CRM</p>
                <a href="{{ route('crm.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('crm.clients.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.clients.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
                    Clientes
                </a>
                <a href="{{ route('crm.plans.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.plans.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    Planos
                </a>
                <a href="{{ route('crm.contracts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.contracts.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Contratos
                </a>
                <a href="{{ route('crm.service-orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.service-orders.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    Ordens de Servico
                </a>
                <a href="{{ route('crm.technicians.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.technicians.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Tecnicos
                </a>
                <a href="{{ route('crm.equipment.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.equipment.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    Equipamentos
                </a>
                <a href="{{ route('crm.manufacturers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.manufacturers.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Fabricantes
                </a>
                <a href="{{ route('crm.suppliers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.suppliers.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Fornecedores
                </a>
                <a href="{{ route('crm.hotspot-coupons.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.hotspot-coupons.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    Cupons Hotspot
                </a>

                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-6 mb-2">MikroTik</p>
                <a href="{{ route('crm.mikrotik-servers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik-servers.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    Servidores
                </a>
                <a href="{{ route('crm.provisioning.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.provisioning.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Provisionamento
                </a>
                <a href="{{ route('crm.uptime.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.uptime.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Uptime
                </a>
                <a href="{{ route('crm.mikrotik-backups.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik-backups.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Backups
                </a>
                <a href="{{ route('crm.site-blocking.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.site-blocking.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Bloqueio Sites
                </a>
                <a href="{{ route('crm.mikrotik.pppoe-active') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.pppoe-active') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                    Sessoes Ativas
                </a>
                <a href="{{ route('crm.mikrotik.ip-pools') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.ip-pools') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    IP Pools
                </a>
                <a href="{{ route('crm.mikrotik.nat-rules') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.nat-rules') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    NAT / Firewall
                </a>
                <a href="{{ route('crm.mikrotik.address-list') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.address-list') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Address List
                </a>
                <a href="{{ route('crm.mikrotik.interfaces') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.interfaces') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                    Interfaces
                </a>
                <a href="{{ route('crm.mikrotik.arp') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.arp') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    Tabela ARP
                </a>
                <a href="{{ route('crm.mikrotik.scripts') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.scripts*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Gerador de Scripts
                </a>
                <a href="{{ route('crm.mikrotik.logs') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.mikrotik.logs') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Logs MikroTik
                </a>

                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-6 mb-2">Rede FTTH</p>
                <a href="{{ route('ftth.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('ftth.dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Dashboard FTTH
                </a>
                <a href="{{ route('ftth.ctos.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('ftth.ctos.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    CTOs
                </a>
                <a href="{{ route('ftth.caixas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('ftth.caixas.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Caixas de Emenda
                </a>
                <a href="{{ route('ftth.generate') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('ftth.generate') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Gerar Manual
                </a>
                <a href="{{ route('ftth.generate.city') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('ftth.generate.city*') || request()->routeIs('ftth.generate.cities') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Gerar por Cidade
                </a>

                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-6 mb-2">Suporte</p>
                <a href="{{ route('technician.portal.dashboard') }}" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Portal Tecnico
                </a>
                <a href="{{ route('crm.tickets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.tickets.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Chamados
                </a>
                <a href="{{ route('crm.newsletter.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.newsletter.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Newsletter
                </a>

                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-6 mb-2">Financeiro</p>
                <a href="{{ route('billing.invoices.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('billing.invoices.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Faturas
                </a>
                <a href="{{ route('billing.invoices.index', ['status' => 'paid']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->get('status') === 'paid' && request()->routeIs('billing.invoices.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Pagamentos
                </a>
                <a href="{{ route('billing.cash-book.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('billing.cash-book.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Livro Caixa
                </a>
                <a href="{{ route('billing.reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('billing.reports.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Relatorios
                </a>
                <a href="{{ route('billing.boleto.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('billing.boleto.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    Boletos
                </a>
                <a href="{{ route('billing.gateways.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('billing.gateways.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Gateways
                </a>

                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-6 mb-2">Sistema</p>
                <a href="{{ route('core.permissions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('core.permissions.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Permissoes
                </a>
                <a href="{{ route('core.settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('core.settings.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Configuracoes
                </a>

                <p class="text-xs font-semibold uppercase text-gray-500 px-3 mt-6 mb-2">Infraestrutura</p>
                <a href="{{ route('crm.network-monitor.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('crm.network-monitor.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Servidores de Rede
                </a>
            </nav>
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center gap-3 text-sm text-gray-400">
                    <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">{{ substr(Auth::user()?->name ?? 'A', 0, 1) }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-white">{{ Auth::user()?->name ?? 'Administrador' }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-xs text-gray-500 hover:text-gray-300">Sair</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
                <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
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
