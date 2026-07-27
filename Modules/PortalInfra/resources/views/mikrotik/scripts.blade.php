@extends('infra::layouts.master')

@section('title', 'Gerador de Scripts MikroTik')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Gerador de Scripts MikroTik</h2>
        <a href="{{ route('infra.mikrotik-servers.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="font-semibold text-gray-800">Configure os parametros e gere o script para copiar e colar na RB</h3>
            <p class="text-sm text-gray-500 mt-1">O script configurura a RB de forma automatica para trabalhar com o MyISP</p>
        </div>
        <form method="POST" action="{{ route('infra.mikrotik.scripts.generate') }}" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik *</label>
                    <select name="server_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" @selected(old('server_id')==$server->id)>{{ $server->name }} ({{ $server->ip }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Configuracao *</label>
                    <select name="script_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="complete" @selected(old('script_type')=='complete')">Completa (PPPoE + Hotspot + Firewall + DHCP)</option>
                        <option value="pppoe" @selected(old('script_type')=='pppoe')">Apenas PPPoE</option>
                        <option value="hotspot" @selected(old('script_type')=='hotspot')">Apenas Hotspot</option>
                        <option value="firewall" @selected(old('script_type')=='firewall')">Apenas Firewall/NAT</option>
                        <option value="dhcp" @selected(old('script_type')=='dhcp')">Apenas DHCP</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h4 class="font-medium text-gray-800 mb-4">Rede</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Interface WAN</label>
                        <input type="text" name="wan_interface" value="{{ old('wan_interface', 'ether1') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IP da LAN</label>
                        <input type="text" name="lan_ip" value="{{ old('lan_ip', '192.168.1.1') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mascara</label>
                        <input type="text" name="lan_mask" value="{{ old('lan_mask', '24') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h4 class="font-medium text-gray-800 mb-4">IP Pool</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Pool</label>
                        <input type="text" name="pool_name" value="{{ old('pool_name', 'pool-hotspot') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IP Inicial</label>
                        <input type="text" name="pool_start" value="{{ old('pool_start', '192.168.1.10') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IP Final</label>
                        <input type="text" name="pool_end" value="{{ old('pool_end', '192.168.1.250') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h4 class="font-medium text-gray-800 mb-4">PPPoE</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Servico PPPoE</label>
                        <input type="text" name="pppoe_service_name" value="{{ old('pppoe_service_name', 'pppoe-service') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">MTU</label>
                        <input type="number" name="mtu_lan" value="{{ old('mtu_lan', '1500') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h4 class="font-medium text-gray-800 mb-4">Hotspot</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Hotspot</label>
                        <input type="text" name="hotspot_name" value="{{ old('hotspot_name', 'hotspot1') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Banda (Up/Down)</label>
                        <div class="flex gap-2">
                            <input type="text" name="bandwidth_up" value="{{ old('bandwidth_up', '10M') }}" placeholder="Upload" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <input type="text" name="bandwidth_down" value="{{ old('bandwidth_down', '50M') }}" placeholder="Download" class="w-1/2 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h4 class="font-medium text-gray-800 mb-4">DNS / NTP / Seguranca</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DNS Servers</label>
                        <input type="text" name="dns_servers" value="{{ old('dns_servers', '8.8.8.8, 8.8.4.4') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NTP Server</label>
                        <input type="text" name="ntp_server" value="{{ old('ntp_server', 'pool.ntp.org') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha Admin</label>
                        <input type="text" name="admin_password" value="{{ old('admin_password') }}" placeholder="Deixe vazio para nao alterar" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('infra.mikrotik-servers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                    Gerar Script
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
