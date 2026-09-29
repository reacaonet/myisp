@extends('core::layouts.master')

@section('title', 'Filiais')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Filiais</h2>
            <p class="text-sm text-gray-500 mt-1">A rede e uma empresa so: cada filial e uma unidade, com CNPJ proprio opcional.</p>
        </div>
        <a href="{{ route('core.branches.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nova Filial
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Filial</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">CNPJ</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Codigo</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Matriz / Pai</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($branches as $branch)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $branch->name }}</td>
                    <td class="px-4 py-3 text-gray-700 font-mono text-xs">{{ $branch->documentFormatted() ?? 'Sem CNPJ' }}</td>
                    <td class="px-4 py-3 text-gray-500">
                        @if($branch->isMatrix())
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">Matriz</span>
                        @else
                            {{ $branch->parent->name ?? '—' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($branch->is_active)
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativa</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Inativa</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('core.branches.edit', $branch) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600 inline-flex">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('core.branches.destroy', $branch) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta filial?')">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600 inline-flex">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhuma filial encontrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
