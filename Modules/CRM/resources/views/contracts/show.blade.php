@extends('core::layouts.master')

@section('title', 'Contrato - ' . ($contract->client->name ?? ''))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Contrato #{{ $contract->id }}</h2>
                <p class="text-sm text-gray-500">
                    Pedido: {{ $contract->pedido ?? '---' }}
                    &middot; Criado em {{ $contract->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="flex items-center gap-1">
                @include('crm::clients._status_badge', ['status' => $contract->status])
                <a href="{{ route('crm.contracts.edit', $contract) }}" title="Editar" class="p-2 rounded-lg text-blue-600 border border-blue-200 hover:bg-blue-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Cliente</h3>
                <p class="font-medium text-gray-900">{{ $contract->client->name }}</p>
                <p class="text-sm text-gray-500">{{ $contract->client->document }}</p>
                <a href="{{ route('crm.clients.show', $contract->client) }}" class="text-sm text-blue-600 hover:underline mt-2 inline-flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg> Ver cliente</a>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Plano</h3>
                <p class="font-medium text-gray-900">{{ $contract->plan->name }}</p>
                <p class="text-sm text-gray-500">{{ number_format($contract->plan->download_speed / 1024, 0) }} Mbps / {{ number_format($contract->plan->upload_speed / 1024, 0) }} Mbps</p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Datas</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Ativacao</dt><dd class="text-gray-900">{{ $contract->activation_date->format('d/m/Y') }}</dd></div>
                    @if($contract->due_date)
                    <div class="flex justify-between"><dt class="text-gray-500">Termino</dt><dd class="text-gray-900">{{ $contract->due_date->format('d/m/Y') }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-gray-500">Vencimento</dt><dd class="text-gray-900">Dia {{ $contract->due_day }}</dd></div>
                </dl>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Financeiro</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Valor Plano</dt><dd class="text-gray-900">R$ {{ number_format($contract->plan->price, 2, ',', '.') }}</dd></div>
                    @if($contract->discount > 0)
                    <div class="flex justify-between"><dt class="text-gray-500">Desconto</dt><dd class="text-green-600">- R$ {{ number_format($contract->discount, 2, ',', '.') }}</dd></div>
                    @endif
                    @if($contract->acrescimo > 0)
                    <div class="flex justify-between"><dt class="text-gray-500">Acrescimo</dt><dd class="text-red-600">+ R$ {{ number_format($contract->acrescimo, 2, ',', '.') }}</dd></div>
                    @endif
                    <div class="flex justify-between border-t border-gray-100 pt-1">
                        <dt class="text-gray-700 font-medium">Valor Final</dt>
                        <dd class="text-gray-900 font-bold">R$ {{ number_format($contract->plan->price - $contract->discount + $contract->acrescimo, 2, ',', '.') }}</dd>
                    </div>
                    <div class="flex justify-between"><dt class="text-gray-500">Cobranca</dt><dd class="text-gray-900">{{ strtoupper($contract->billing_type) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Isento</dt><dd class="text-gray-900">{{ $contract->insento ? 'Sim' : 'Nao' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Auto-bloqueio</dt><dd class="text-gray-900">{{ $contract->autobloqueio ? 'Sim' : 'Nao' }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Dados de Conexao</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Tipo Conexao</dt><dd class="text-gray-900">{{ strtoupper($contract->tipo_conexao) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Situacao</dt><dd class="text-gray-900">{{ $contract->situacao ?: 'Normal' }}</dd></div>
                @if($contract->server)
                <div class="flex justify-between"><dt class="text-gray-500">Servidor</dt><dd class="text-gray-900">{{ $contract->server->name }}</dd></div>
                @endif
                @if($contract->ip_pool)
                <div class="flex justify-between"><dt class="text-gray-500">IP Pool</dt><dd class="text-gray-900 font-mono">{{ $contract->ip_pool }}</dd></div>
                @endif
                @if($contract->pppoe_user)
                <div class="flex justify-between"><dt class="text-gray-500">Usuario PPPoE</dt><dd class="text-gray-900 font-mono">{{ $contract->pppoe_user }}</dd></div>
                @endif
                @if($contract->pppoe_password)
                <div class="flex justify-between"><dt class="text-gray-500">Senha PPPoE</dt><dd class="text-gray-900 font-mono">{{ $contract->pppoe_password }}</dd></div>
                @endif
                @if($contract->wpa_key)
                <div class="flex justify-between"><dt class="text-gray-500">WPA Key</dt><dd class="text-gray-900 font-mono">{{ $contract->wpa_key }}</dd></div>
                @endif
                @if($contract->ip_address)
                <div class="flex justify-between"><dt class="text-gray-500">IP</dt><dd class="text-gray-900 font-mono">{{ $contract->ip_address }}</dd></div>
                @endif
                @if($contract->route_ip)
                <div class="flex justify-between"><dt class="text-gray-500">IP Roteamento</dt><dd class="text-gray-900 font-mono">{{ $contract->route_ip }}</dd></div>
                @endif
                @if($contract->ipv6)
                <div class="flex justify-between"><dt class="text-gray-500">IPv6</dt><dd class="text-gray-900 font-mono">{{ $contract->ipv6 }}</dd></div>
                @endif
                @if($contract->mac_address)
                <div class="flex justify-between"><dt class="text-gray-500">MAC</dt><dd class="text-gray-900 font-mono">{{ $contract->mac_address }}</dd></div>
                @endif
                @if($contract->mac_wireless)
                <div class="flex justify-between"><dt class="text-gray-500">MAC Wireless</dt><dd class="text-gray-900 font-mono">{{ $contract->mac_wireless }}</dd></div>
                @endif
            </dl>
        </div>

        @if($contract->ip_ubnt || $contract->porta_ubnt)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Ubiquiti</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                @if($contract->ip_ubnt)
                <div class="flex justify-between"><dt class="text-gray-500">IP Ubiquiti</dt><dd class="text-gray-900 font-mono">{{ $contract->ip_ubnt }}</dd></div>
                @endif
                @if($contract->porta_ubnt)
                <div class="flex justify-between"><dt class="text-gray-500">Porta</dt><dd class="text-gray-900">{{ $contract->porta_ubnt }}</dd></div>
                @endif
                @if($contract->login_ubnt)
                <div class="flex justify-between"><dt class="text-gray-500">Login</dt><dd class="text-gray-900">{{ $contract->login_ubnt }}</dd></div>
                @endif
                @if($contract->senha_ubnt)
                <div class="flex justify-between"><dt class="text-gray-500">Senha</dt><dd class="text-gray-900">{{ $contract->senha_ubnt }}</dd></div>
                @endif
            </dl>
        </div>
        @endif

        @if($contract->install_street || $contract->install_neighborhood)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Endereco de Instalacao</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                @if($contract->install_street)
                <div class="flex justify-between"><dt class="text-gray-500">Logradouro</dt><dd class="text-gray-900">{{ $contract->install_street }}{{ $contract->install_number ? ', '.$contract->install_number : '' }}</dd></div>
                @endif
                @if($contract->install_complement)
                <div class="flex justify-between"><dt class="text-gray-500">Complemento</dt><dd class="text-gray-900">{{ $contract->install_complement }}</dd></div>
                @endif
                @if($contract->install_neighborhood)
                <div class="flex justify-between"><dt class="text-gray-500">Bairro</dt><dd class="text-gray-900">{{ $contract->install_neighborhood }}</dd></div>
                @endif
                @if($contract->install_city)
                <div class="flex justify-between"><dt class="text-gray-500">Cidade/UF</dt><dd class="text-gray-900">{{ $contract->install_city }}/{{ $contract->install_state }}</dd></div>
                @endif
                @if($contract->install_zipcode)
                <div class="flex justify-between"><dt class="text-gray-500">CEP</dt><dd class="text-gray-900">{{ $contract->install_zipcode }}</dd></div>
                @endif
            </dl>
        </div>
        @endif

        @if($contract->observacao)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Observacao</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $contract->observacao }}</p>
        </div>
        @endif

        @if($contract->notes)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Observacoes</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $contract->notes }}</p>
        </div>
        @endif

        @if($contract->invoices->isNotEmpty())
        <div class="border-t border-gray-200">
            <div class="px-6 py-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Faturas</h3>
                <div class="space-y-2">
                    @foreach($contract->invoices as $invoice)
                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between text-sm">
                        <div>
                            <span class="font-medium">{{ str_pad($invoice->mes ?? 0, 2, '0', STR_PAD_LEFT) }}/{{ $invoice->ano ?? $invoice->due_date->format('Y') }}</span>
                            <span class="text-gray-500 ml-2">R$ {{ number_format($invoice->amount, 2, ',', '.') }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded text-xs font-medium
                            @if($invoice->status == 'paid') bg-green-100 text-green-700
                            @elseif($invoice->status == 'overdue') bg-red-100 text-red-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="mt-4 flex justify-end">
        <form method="POST" action="{{ route('crm.contracts.destroy', $contract) }}" onsubmit="return confirm('Tem certeza que deseja remover este contrato?')">
            @csrf @method('DELETE')
            <button type="submit" title="Remover Contrato" class="p-2 rounded-lg text-red-600 border border-red-200 hover:bg-red-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
        </form>
    </div>
</div>
@endsection
