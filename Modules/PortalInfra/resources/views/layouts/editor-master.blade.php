<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Editor FTTH') - MyISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased h-screen overflow-hidden">

<div class="flex flex-col h-screen">
    <header class="h-11 bg-gray-900 text-white flex items-center justify-between px-4 shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('infra.dashboard') }}" class="text-sm font-bold tracking-tight hover:text-blue-400 transition">
                <span class="text-blue-400">My</span>ISP
            </a>
            <span class="text-xs text-gray-400 hidden sm:inline">Editor de Rede FTTH</span>
        </div>
        <a href="{{ route('infra.dashboard') }}" class="text-xs px-3 py-1.5 bg-gray-700 hover:bg-gray-600 rounded-lg transition">
            &larr; Voltar ao painel
        </a>
    </header>

    <main class="flex-1 min-h-0 p-3 overflow-hidden">
        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
