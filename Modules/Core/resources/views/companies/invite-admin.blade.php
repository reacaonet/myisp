@extends('core::layouts.master')

@section('title', 'Convidar administrador - '.$company->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('core.companies.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar</a>
    </div>

    <form method="POST" action="{{ route('core.companies.admin.store', $company) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf

        <h2 class="text-xl font-bold text-gray-900 mb-1">Convidar administrador</h2>
        <p class="text-sm text-gray-500 mb-6">
            {{ $company->name }} tera um novo usuario vinculado a companhia e a filial Matriz.
            Deixe a senha em branco para gerar uma senha temporaria exibida uma unica vez.
        </p>

        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" name="admin_phone" value="{{ old('admin_phone') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Grupo de Permissoes</label>
                <select name="admin_group_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Padrao (Administrador)</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ (int) old('admin_group_id') === $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha (opcional)</label>
                <input type="password" name="admin_password" autocomplete="new-password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="minimo de 8 caracteres">
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Convidar administrador</button>
        </div>
    </form>
</div>
@endsection
