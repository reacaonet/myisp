<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Cliente - MyISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-xl shadow-sm border border-gray-200 p-8 mx-4">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Portal do Cliente</h2>
            <p class="text-sm text-gray-500 mt-1">Acesse com seu login e senha</p>
        </div>

        <form method="POST" action="{{ route('crm.portal.login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                <input type="text" name="login" value="{{ old('login') }}" required autofocus
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('login') border-red-500 @enderror">
                @error('login') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                <input type="password" name="senha" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Lembrar-me
                </label>
            </div>

            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
