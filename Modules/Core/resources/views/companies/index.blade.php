@extends('core::layouts.master')

@section('title', 'Companias / Franquias')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Companias / Franquias</h2>
        <a href="{{ route('core.companies.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova Compania
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    @if(session('onboarding'))
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-900 rounded-lg text-sm">
            <p class="font-semibold mb-1">Convite enviado para {{ session('onboarding')['name'] }} ({{ session('onboarding')['email'] }})</p>
            @if(session('onboarding')['password'])
                <p class="mb-1">Senha temporaria: <span class="font-mono font-semibold">{{ session('onboarding')['password'] }}</span></p>
                <p class="text-xs text-blue-700">Esta senha aparece apenas uma vez. O usuario devera definir uma nova senha no primeiro acesso.</p>
            @else
                <p class="text-xs text-blue-700">O usuario recebeu a senha informada e debera troca-la no primeiro acesso.</p>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Nome</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Tipo</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Matriz / Pai</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">CNPJ</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Filiais</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Usuarios</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($companies as $company)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-900">{{ $company->name }}</div>
                        <div class="text-xs text-gray-400">Slug: {{ $company->slug }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($company->isRoot())
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">Franqueadora</span>
                        @elseif($company->is_franchise)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Franquia</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Compania</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $company->parent->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $company->fiscal('document') ?: '—' }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $company->branches_count }}</td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ $company->users_count }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($company->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativa</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Inativa</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('core.companies.edit', $company) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600 inline-flex">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <a href="{{ route('core.companies.admin.form', $company) }}" title="Convidar administrador" class="p-1.5 rounded hover:bg-blue-50 text-blue-600 inline-flex">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm3 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-9.303 3.99c1.563-1.46 3.385-1.22 4.682.893l.72 1.281a1.5 1.5 0 01-.42 2.14l-3.108 1.941a1.5 1.5 0 01-1.838-.03L3.07 13.09a1.5 1.5 0 011.06-2.37l1.31.394a1.5 1.5 0 001.06 2.37z"/></svg>
                            </a>
                            @if(!$company->isRoot())
                            <form method="POST" action="{{ route('core.companies.destroy', $company) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta compania?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600 inline-flex">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">Nenhuma compania encontrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection