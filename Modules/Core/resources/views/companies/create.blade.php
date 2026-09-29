@extends('core::layouts.master')

@section('title', 'Nova Compania')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('core.companies.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar</a>
    </div>

    <form method="POST" action="{{ route('core.companies.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf

        <h2 class="text-xl font-bold text-gray-900 mb-6">Nova Compania</h2>

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
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Razao / nome da compania ou franquia">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="ex: franquia-campinas">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                <input type="text" name="code" value="{{ old('code') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="ex: FR001">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Franquia de (matriz / pai)</label>
                <select name="parent_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Nenhuma (raiz/franqueadora)</option>
                    @foreach($parents as $p)
                        <option value="{{ $p->id }}" {{ old('parent_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-4 pb-1">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_franchise" value="1" {{ old('is_franchise') ? 'checked' : '' }} class="rounded border-gray-300">
                    E uma franquia
                </label>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded border-gray-300">
                    Ativa
                </label>
            </div>

            <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Dados Fiscais (opcional - herda da matriz/franqueadora se vazio)</h3>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Razao Social</label>
                <input type="text" name="legal_name" value="{{ old('legal_name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="00.000.000/0001-00 LTDA">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                <input type="text" name="fantasy_name" value="{{ old('fantasy_name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Nome usado na marca">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                <input type="text" name="document" value="{{ old('document') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="00.000.000/0001-00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Inscricao Estadual</label>
                <input type="text" name="state_registration" value="{{ old('state_registration') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Inscricao Municipal</label>
                <input type="text" name="municipal_registration" value="{{ old('municipal_registration') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Celular</label>
                <input type="text" name="cellphone" value="{{ old('cellphone') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site</label>
                <input type="text" name="website" value="{{ old('website') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Endereco</label>
                <input type="text" name="address" value="{{ old('address') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                <input type="text" name="city" value="{{ old('city') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                    <input type="text" name="state" value="{{ old('state') }}" maxlength="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                    <input type="text" name="zip" value="{{ old('zip') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Filial Matriz (criada automaticamente)</h3>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Matriz</label>
                <input type="text" name="matrix_name" value="{{ old('matrix_name', 'Matriz') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codigo da Matriz</label>
                <input type="text" name="matrix_code" value="{{ old('matrix_code', 'MAT') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-2">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">Administrador da Franquia (convite)</h3>
                <p class="text-xs text-gray-500 mb-3">Opcional. Se voce deixar a senha em branco, geramos uma senha temporaria exibida uma unica vez ao final do cadastro.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Administrador</label>
                <input type="text" name="admin_name" value="{{ old('admin_name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email do Administrador</label>
                <input type="email" name="admin_email" value="{{ old('admin_email') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone do Administrador</label>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Senha (em branco = senha temporaria gerada)</label>
                <input type="password" name="admin_password" autocomplete="new-password" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Compania</button>
        </div>
    </form>
</div>
@endsection