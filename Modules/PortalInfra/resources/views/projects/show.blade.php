@extends('infra::layouts.master')

@section('title', $project->name . ' - Projeto FTTH')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $project->name }}</h1>
            <p class="text-gray-500 text-sm">{{ $project->city }}{{ $project->state ? '/' . $project->state : '' }} &middot; Criado {{ $project->created_at->format('d/m/Y') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('infra.ftth.projects.edit', $project) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50">Editar</a>
            <a href="{{ route('infra.ftth.projects.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Voltar</a>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $project->ctos_count }}</div>
            <div class="text-sm text-gray-500">CTOs</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $project->caixas_count }}</div>
            <div class="text-sm text-gray-500">Caixas de Emenda</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="text-2xl font-bold text-gray-900">{{ number_format($project->total_distance_km, 2, ',', '.') }}</div>
            <div class="text-sm text-gray-500">km de Fibra</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="text-2xl font-bold text-gray-900">{{ $project->total_streets }}</div>
            <div class="text-sm text-gray-500">Ruas Mapeadas</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-700">CTOs</div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Codigo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rua</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Caixa</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Capacidade</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($ctos as $cto)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $cto->code }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cto->street ?: '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cto->caixaEmenda?->code ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $cto->used_ports }}/{{ $cto->capacity }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($cto->status == 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $cto->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhuma CTO neste projeto.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-700">Caixas de Emenda</div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Codigo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rua</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">CTOs</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Capacidade</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($caixas as $caixa)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $caixa->code }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $caixa->street ?: '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $caixa->ctos_count }}</td>
                    <td class="px-4 py-3 text-sm text-gray-500">{{ $caixa->used_ports }}/{{ $caixa->capacity }}</td>
                    <td class="px-4 py-3 text-sm">
                        @if($caixa->status == 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Ativa</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $caixa->status }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">Nenhuma caixa neste projeto.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection