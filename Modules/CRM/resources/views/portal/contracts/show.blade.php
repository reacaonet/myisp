@extends('crm::portal.layouts.master')

@section('title', "Contrato - {$contract->plan->name}")

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $contract->plan->name }}</h2>
                <p class="text-sm text-gray-500">Ativado em {{ $contract->activation_date->format('d/m/Y') }}</p>
            </div>
            @include('crm::clients._status_badge', ['status' => $contract->status])
        </div>

        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Plano</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Velocidade</dt>
                        <dd class="font-medium text-gray-900">{{ $contract->plan->download_speed }}Mbps</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Upload</dt>
                        <dd class="font-medium text-gray-900">{{ $contract->plan->upload_speed }}Mbps</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Valor</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($contract->plan->price, 2, ',', '.') }}</dd>
                    </div>
                    @if($contract->discount > 0)
                    <div>
                        <dt class="text-gray-500">Desconto</dt>
                        <dd class="text-green-600">-R$ {{ number_format($contract->discount, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500">Valor Final</dt>
                        <dd class="font-bold text-lg text-gray-900">R$ {{ number_format($contract->plan->price - $contract->discount, 2, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Faturamento</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Tipo</dt>
                        <dd class="font-medium text-gray-900">{{ ucfirst($contract->billing_type ?? 'Mensal') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Dia de Vencimento</dt>
                        <dd class="font-medium text-gray-900">{{ $contract->due_day }}</dd>
                    </div>
                </dl>
            </div>

            @if($contract->pppoe_user || $contract->ip_address)
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Conexao</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    @if($contract->pppoe_user)
                    <div>
                        <dt class="text-gray-500">Usuario PPPoE</dt>
                        <dd class="font-medium font-mono text-gray-900">{{ $contract->pppoe_user }}</dd>
                    </div>
                    @endif
                    @if($contract->ip_address)
                    <div>
                        <dt class="text-gray-500">Endereco IP</dt>
                        <dd class="font-medium font-mono text-gray-900">{{ $contract->ip_address }}</dd>
                    </div>
                    @endif
                    @if($contract->mac_address)
                    <div>
                        <dt class="text-gray-500">MAC Address</dt>
                        <dd class="font-medium font-mono text-gray-900">{{ $contract->mac_address }}</dd>
                    </div>
                    @endif
                    @if($contract->tipo_conexao)
                    <div>
                        <dt class="text-gray-500">Tipo de Conexao</dt>
                        <dd class="font-medium text-gray-900">{{ $contract->tipo_conexao }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            @if($contract->autoBloqueio)
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
                Bloqueio automatico ativado para este contrato.
            </div>
            @endif
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('crm.portal.contracts') }}" class="text-sm text-blue-600 hover:underline">&larr; Voltar para contratos</a>
    </div>
</div>
@endsection
