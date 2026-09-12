@extends('infra::layouts.master')

@section('title', 'Projetos - Rede FTTH')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Projetos FTTH</h1>
        <p class="text-gray-500 text-sm">Projetos de implantacao de rede</p>
    </div>
    <a href="{{ route('infra.ftth.projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Novo Projeto
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome ou cidade..."
               class="flex-1 min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg text-sm">
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filtrar</button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Projeto</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Cidade</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Prefixo</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">CTOs</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Caixas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Distancia</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Criado em</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Acoes</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    <a href="{{ route('infra.ftth.projects.show', $project) }}" class="text-blue-600 hover:underline">{{ $project->name }}</a>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ $project->city ?: '-' }}{{ $project->state ? '/' . $project->state : '' }}</td>
                <td class="px-4 py-3 text-sm font-mono text-gray-500">{{ $project->prefix ?: '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $project->ctos_count }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ $project->caixas_count }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ number_format($project->total_distance_km, 2, ',', '.') }} km</td>
                <td class="px-4 py-3 text-sm">
                    @if($project->status == 'active')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativo</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inativo</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-400">{{ $project->created_at->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-sm">
                    <div class="flex items-center gap-1">
                        <a href="{{ route('infra.ftth.projects.show', $project) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded" title="Ver">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('infra.ftth.projects.edit', $project) }}" class="p-1.5 text-blue-500 hover:text-blue-700 rounded" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('infra.ftth.projects.destroy', $project) }}" onsubmit="return confirm('Excluir este projeto? CTOs e caixas serao desvinculadas.')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded" title="Excluir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-4 py-12 text-center text-gray-400">Nenhum projeto encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-gray-200">
        {{ $projects->withQueryString()->links() }}
    </div>
</div>
@endsection