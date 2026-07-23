@extends('crm::portal.layouts.master')

@section('title', 'Ordens de Servico')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Ordens de Servico</h3>
    </div>

    @if($client->serviceOrders->isEmpty())
    <div class="p-12 text-center text-gray-400">Nenhuma ordem de servico encontrada.</div>
    @else
    <div class="divide-y divide-gray-100">
        @foreach($client->serviceOrders as $os)
        <div class="p-6 hover:bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-900">{{ $os->codigo }}</p>
                    <p class="text-sm text-gray-600">{{ $os->servico ?? $os->tipo_servico ?? 'Servico' }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($os->status == 'closed') bg-green-100 text-green-800
                        @elseif($os->status == 'canceled') bg-red-100 text-red-800
                        @elseif($os->status == 'active') bg-blue-100 text-blue-800
                        @else bg-yellow-100 text-yellow-800 @endif">
                        {{ $os->status == 'closed' ? 'Concluido' : ($os->status == 'canceled' ? 'Cancelado' : ($os->status == 'active' ? 'Em Andamento' : 'Aberto')) }}
                    </span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-2 text-sm text-gray-500">
                <div>Abertura: {{ $os->emissao?->format('d/m/Y') ?? '-' }}</div>
                <div>Tecnico: {{ $os->technician?->name ?? 'Nao definido' }}</div>
            </div>
            @if($os->problema)
            <div class="mt-2 p-3 bg-gray-50 rounded-lg text-sm text-gray-700">
                <span class="font-medium">Problema:</span> {{ $os->problema }}
            </div>
            @endif
            @if($os->solucao)
            <div class="mt-2 p-3 bg-green-50 rounded-lg text-sm text-green-700">
                <span class="font-medium">Solucao:</span> {{ $os->solucao }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div class="p-6 border-t border-gray-200 text-center">
        <a href="{{ route('crm.portal.dashboard') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1 justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Voltar ao Dashboard</a>
    </div>
</div>
@endsection
