@extends('core::layouts.master')

@section('title', 'Meu Perfil')

@section('content')
<div class="max-w-3xl mx-auto">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-4">
            @if($user->avatarUrl())
                <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover border border-gray-200">
            @else
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <span class="text-2xl font-bold text-blue-600">{{ $user->initials() }}</span>
                </div>
            @endif
            <div class="min-w-0">
                <h3 class="text-lg font-semibold text-gray-800 truncate">{{ $user->name }}</h3>
                <p class="text-sm text-gray-500">{{ $user->group?->name ?? 'Sem grupo' }}@if($user->cargo) &middot; {{ $user->cargo }} @endif</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('core.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Dados pessoais</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-400 mt-1">Este e o login da conta.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Celular</label>
                    <input type="text" name="cellphone" value="{{ old('cellphone', $user->cellphone) }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('cellphone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                    <input type="text" name="city" value="{{ old('city', $user->city) }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                    <input type="text" name="state" value="{{ old('state', $user->state) }}" maxlength="2" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm uppercase focus:ring-blue-500 focus:border-blue-500">
                    @error('state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Foto</h3>

            @if($user->avatarUrl())
                <div class="flex items-center gap-4 mb-4">
                    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover border border-gray-200">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="remove_avatar" value="1" class="rounded border-gray-300 text-red-600">
                        <span class="text-sm text-gray-700">Remover a foto atual</span>
                    </label>
                </div>
            @endif

            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:text-sm file:font-medium @error('avatar') border-red-500 @enderror">
            @error('avatar') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            <p class="mt-1 text-xs text-gray-400">JPG, PNG, WebP ou GIF ate 2MB. Enviar uma imagem substitui a atual.</p>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Alteracoes</button>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Acesso e contexto</h3>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Grupo</dt>
                <dd class="font-medium text-gray-800">{{ $user->group?->name ?? 'Sem grupo' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Empresa atual</dt>
                <dd class="font-medium text-gray-800">{{ $company?->name ?? 'Todas as empresas' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Filial atual</dt>
                <dd class="font-medium text-gray-800">{{ $branch?->name ?? 'Todas as filiais' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Senha</dt>
                <dd class="font-medium text-gray-800">
                    <a href="{{ route('password.edit') }}" class="text-blue-600 hover:underline">Alterar minha senha</a>
                </dd>
            </div>
        </dl>

        <p class="mt-4 text-xs text-gray-400">
            Grupo, empresa e filial sao definidos por um administrador do sistema.
        </p>
    </div>
</div>
@endsection
