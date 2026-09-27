@extends('crm::portal.layouts.master')

@php $pageTitle = 'Contrato - ' . ($contract->plan?->name ?? '-'); @endphp
@section('title', $pageTitle)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $contract->plan?->name ?? '-' }}</h2>
                <p class="text-sm text-gray-500">Ativado em {{ $contract->activation_date->format('d/m/Y') }}</p>
            </div>
            @include('crm::clients._status_badge', ['status' => $contract->status])
            <a href="{{ route('crm.portal.contracts.print', $contract) }}" target="_blank" title="Imprimir Contrato" class="ml-2 p-2 rounded-lg text-gray-600 border border-gray-200 hover:bg-gray-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg></a>
        </div>

        <div class="p-6 space-y-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Plano</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Velocidade</dt>
                        <dd class="font-medium text-gray-900">{{ $contract->plan?->download_speed ?? 0 }}Mbps</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Upload</dt>
                        <dd class="font-medium text-gray-900">{{ $contract->plan?->upload_speed ?? 0 }}Mbps</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Valor</dt>
                        <dd class="font-medium text-gray-900">R$ {{ number_format($contract->plan?->price ?? 0, 2, ',', '.') }}</dd>
                    </div>
                    @if($contract->discount > 0)
                    <div>
                        <dt class="text-gray-500">Desconto</dt>
                        <dd class="text-green-600">-R$ {{ number_format($contract->discount, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500">Valor Final</dt>
                        <dd class="font-bold text-lg text-gray-900">R$ {{ number_format(($contract->plan?->price ?? 0) - $contract->discount, 2, ',', '.') }}</dd>
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

            @php
                $portalLogin = $contract->provisionedLogin();
                $portalIp = $contract->provisionedIp();
                $portalMac = $contract->provisionedMac();
            @endphp
            @if($portalLogin || $portalIp)
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Conexao</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    @if($portalLogin)
                    <div>
                        <dt class="text-gray-500">Usuario PPPoE</dt>
                        <dd class="font-medium font-mono text-gray-900">{{ $portalLogin }}</dd>
                    </div>
                    @endif
                    @if($portalIp)
                    <div>
                        <dt class="text-gray-500">Endereco IP</dt>
                        <dd class="font-medium font-mono text-gray-900">{{ $portalIp }}</dd>
                    </div>
                    @endif
                    @if($portalMac)
                    <div>
                        <dt class="text-gray-500">MAC Address</dt>
                        <dd class="font-medium font-mono text-gray-900">{{ $portalMac }}</dd>
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

            @if($contract->autobloqueio)
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
