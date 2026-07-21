@extends('crm::portal.layouts.master')

@section('title', 'Meus Contratos')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Meus Contratos</h3>
    </div>

    @if($client->contracts->isEmpty())
    <div class="p-12 text-center text-gray-400">Nenhum contrato encontrado.</div>
    @else
    <div class="divide-y divide-gray-100">
        @foreach($client->contracts as $contract)
        <div class="p-6 hover:bg-gray-50">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <p class="font-medium text-gray-900">{{ $contract->plan->name }}</p>
                    <p class="text-sm text-gray-500">Ativado em {{ $contract->activation_date->format('d/m/Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-900">R$ {{ number_format($contract->plan->price - $contract->discount, 2, ',', '.') }}</p>
                    @include('crm::clients._status_badge', ['status' => $contract->status])
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">Vencimento:</span>
                    <span class="text-gray-900 font-medium">Dia {{ $contract->due_day }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Faturamento:</span>
                    <span class="text-gray-900 font-medium">{{ ucfirst($contract->billing_type ?? 'mensal') }}</span>
                </div>
                @if($contract->pppoe_user)
                <div>
                    <span class="text-gray-500">PPPoE:</span>
                    <span class="text-gray-900 font-medium font-mono text-xs">{{ $contract->pppoe_user }}</span>
                </div>
                @endif
                @if($contract->ip_address)
                <div>
                    <span class="text-gray-500">IP:</span>
                    <span class="text-gray-900 font-medium font-mono text-xs">{{ $contract->ip_address }}</span>
                </div>
                @endif
            </div>
            @if($contract->status === 'active')
            <div class="mt-3">
                <a href="{{ route('crm.portal.contracts.show', $contract) }}" class="text-sm text-blue-600 hover:underline">Ver detalhes</a>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div class="p-6 border-t border-gray-200 text-center">
        <a href="{{ route('crm.portal.dashboard') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar ao Dashboard</a>
    </div>
</div>
@endsection
