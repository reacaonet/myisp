@extends('core::layouts.master')

@section('title', 'Planos')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Planos de Internet</h2>
        <div class="flex gap-3">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Buscar plano..." value="{{ request('search') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Buscar</button>
            </form>
            <a href="{{ route('crm.plans.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo Plano
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">Download</th>
                    <th class="px-6 py-4 font-medium">Upload</th>
                    <th class="px-6 py-4 font-medium">Valor</th>
                    <th class="px-6 py-4 font-medium">Ciclo</th>
                    <th class="px-6 py-4 font-medium">Servidor</th>
                    <th class="px-6 py-4 font-medium">Ativo</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $plan->name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ number_format($plan->download_speed / 1024, 0) }} Mbps</td>
                    <td class="px-6 py-4 text-gray-600">{{ number_format($plan->upload_speed / 1024, 0) }} Mbps</td>
                    <td class="px-6 py-4 text-gray-900 font-medium">R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ ucfirst($plan->billing_cycle) }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $plan->server?->name ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @if($plan->is_active)
                            <span class="text-green-600 font-medium">Sim</span>
                        @else
                            <span class="text-red-600 font-medium">Nao</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('crm.plans.edit', $plan) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">Editar</a>
                        <span class="text-gray-300 mx-2">|</span>
                        <form method="POST" action="{{ route('crm.plans.destroy', $plan) }}" onsubmit="return confirm('Remover plano {{ $plan->name }}?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium text-sm">Excluir</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">Nenhum plano encontrado.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($plans->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $plans->links() }}</div>
    @endif
</div>
@endsection
