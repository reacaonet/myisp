@extends('core::layouts.master')

@section('title', 'Novo Contrato')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Novo Contrato</h2>
        </div>
        <form method="POST" action="{{ route('crm.contracts.store') }}" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                    <select name="client_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_id', $selectedClientId) == $c->id)>{{ $c->name }} - {{ $c->document }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plano *</label>
                    <select name="plan_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}" @selected(old('plan_id') == $p->id) data-price="{{ $p->price }}">{{ $p->name }} - R$ {{ number_format($p->price, 2, ',', '.') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pedido</label>
                    <input type="text" name="pedido" value="{{ old('pedido') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Ativacao *</label>
                    <input type="date" name="activation_date" value="{{ old('activation_date', date('Y-m-d')) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dia Vencimento *</label>
                    <input type="number" name="due_day" value="{{ old('due_day', 5) }}" min="1" max="31" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Cobranca *</label>
                    <select name="billing_type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="boleto">Boleto</option>
                        <option value="pix">PIX</option>
                        <option value="credit_card">Cartao de Credito</option>
                        <option value="debit_contract">Debito em Conta</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Conexao *</label>
                    <select name="tipo_conexao" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="pppoe">PPPoE</option>
                        <option value="hotspot">Hotspot</option>
                        <option value="iparp">IP/ARP</option>
                        <option value="dhcp">DHCP</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Situacao</label>
                    <select name="situacao" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Normal</option>
                        <option value="S">Suspenso</option>
                        <option value="I">Inadimplente</option>
                        <option value="C">Cancelado</option>
                        <option value="N">Novo</option>
                        <option value="F">Fidelizado</option>
                        <option value="D">Desativado</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Usuario PPPoE</label>
                    <input type="text" name="pppoe_user" value="{{ old('pppoe_user') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha PPPoE</label>
                    <input type="text" name="pppoe_password" value="{{ old('pppoe_password') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">WPA Key</label>
                    <input type="text" name="wpa_key" value="{{ old('wpa_key') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servidor</label>
                    <select name="server_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" @selected(old('server_id') == $server->id)>{{ $server->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik</label>
                    <select name="mikrotik_server_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Nenhum</option>
                        @foreach($mikrotikServers as $mk)
                            <option value="{{ $mk->id }}" @selected(old('mikrotik_server_id') == $mk->id)>{{ $mk->name }} ({{ $mk->ip }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Pool</label>
                    <input type="text" name="ip_pool" value="{{ old('ip_pool') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Roteamento</label>
                    <input type="text" name="route_ip" value="{{ old('route_ip') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IPv6</label>
                    <input type="text" name="ipv6" value="{{ old('ipv6') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MAC Address</label>
                    <input type="text" name="mac_address" value="{{ old('mac_address') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MAC Wireless</label>
                    <input type="text" name="mac_wireless" value="{{ old('mac_wireless') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Ubiquiti</label>
                    <input type="text" name="ip_ubnt" value="{{ old('ip_ubnt') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Porta Ubiquiti</label>
                    <input type="text" name="porta_ubnt" value="{{ old('porta_ubnt') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login Ubiquiti</label>
                    <input type="text" name="login_ubnt" value="{{ old('login_ubnt') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha Ubiquiti</label>
                    <input type="text" name="senha_ubnt" value="{{ old('senha_ubnt') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desconto (R$)</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Acrescimo (R$)</label>
                    <input type="number" step="0.01" name="acrescimo" value="{{ old('acrescimo', 0) }}" min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observacao</label>
                    <input type="text" name="observacao" value="{{ old('observacao') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="insento" value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Isento
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="autobloqueio" value="1" checked
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Auto-bloqueio
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="alterar_senha" value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Alterar Senha
                </label>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Endereco de Instalacao</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                        <input type="text" name="install_street" value="{{ old('install_street') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Numero</label>
                        <input type="text" name="install_number" value="{{ old('install_number') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="install_complement" value="{{ old('install_complement') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        <input type="text" name="install_neighborhood" value="{{ old('install_neighborhood') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                        <input type="text" name="install_city" value="{{ old('install_city') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                        <input type="text" name="install_state" value="{{ old('install_state') }}" maxlength="2"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                        <input type="text" name="install_zipcode" value="{{ old('install_zipcode') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('crm.contracts.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
