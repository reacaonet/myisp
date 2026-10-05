@extends('core::layouts.master')

@section('title', 'Editar Usuario - ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Editar Usuario: {{ $user->name }}</h2>
        <a href="{{ route('core.users.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="POST" action="{{ route('core.users.update', $user) }}">
        @csrf @method('PUT')

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha (deixe vazio para manter)</label>
                    <input type="password" name="password" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grupo *</label>
                    <select name="user_group_id" required class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('user_group_id', $user->user_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('user_group_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                @if($canAssignContext)
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase text-gray-500 mb-2">Franquia de atuacao</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Empresas / Franquias</label>
                            <select name="company_ids[]" id="company_ids" multiple size="6"
                                class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}"
                                        {{ in_array($company->id, old('company_ids', $user->companies->pluck('id')->all())) ? 'selected' : '' }}>
                                        {{ $company->name }}{{ $company->is_franchise ? ' (franquia)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Use Ctrl ou Shift para escolher varias. Sem selecao o usuario fica sem vinculo.</p>
                            @error('company_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @error('company_ids.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Filiais</label>
                            <select name="branch_ids[]" id="branch_ids" multiple size="6"
                                class="w-full rounded-lg border-gray-300 border px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" data-company="{{ $branch->company_id }}"
                                        {{ in_array($branch->id, old('branch_ids', $user->branches->pluck('id')->all())) ? 'selected' : '' }}>
                                        {{ $branch->name }} - {{ $branch->company?->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">A filial marcada ja vincula a empresa dela.</p>
                            @error('branch_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            @error('branch_ids.*') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        O usuario opera todas as empresas marcadas e troca de contexto no topo da tela.
                    </p>
                </div>
                @endif
            </div>
            <div class="mt-4">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                    <span class="text-sm font-medium text-gray-700">Ativo</span>
                </label>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.users.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Alteracoes</button>
        </div>
    </form>
</div>
@endsection
