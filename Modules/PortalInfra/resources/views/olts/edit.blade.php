@extends('infra::layouts.master')

@section('title', 'Editar OLT')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar OLT</h2>
        </div>
        <form method="POST" action="{{ route('infra.olts.update', $olt) }}" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input type="text" name="name" value="{{ old('name', $olt->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                    <input type="text" name="brand" value="{{ old('brand', $olt->brand) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" name="model" value="{{ old('model', $olt->model) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP *</label>
                    <input type="text" name="ip" value="{{ old('ip', $olt->ip) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mascara</label>
                    <input type="text" name="subnet_mask" value="{{ old('subnet_mask', $olt->subnet_mask) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="gpon" @selected(old('type', $olt->type)=='gpon')>GPON</option>
                        <option value="epon" @selected(old('type', $olt->type)=='epon')>EPON</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Portas PON</label>
                    <input type="number" name="pon_ports" value="{{ old('pon_ports', $olt->pon_ports) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                    <input type="text" name="mgmt_login" value="{{ old('mgmt_login', $olt->mgmt_login) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password" name="mgmt_password" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Deixe vazio para manter">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Porta SNMP</label>
                    <input type="number" name="snmp_port" value="{{ old('snmp_port', $olt->snmp_port) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Community SNMP</label>
                    <input type="text" name="snmp_community" value="{{ old('snmp_community', $olt->snmp_community) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Regiao</label>
                <input type="text" name="olt_region" value="{{ old('olt_region', $olt->olt_region) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4 items-center">
                <div>
                    <input type="number" name="used_pon_ports" value="{{ old('used_pon_ports', $olt->used_pon_ports) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <label class="block text-xs text-gray-500 mt-1">Portas PON em uso</label>
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $olt->is_active)) class="rounded border-gray-300">
                    <span class="text-sm text-gray-700">Ativa</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes', $olt->notes) }}</textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('infra.olts.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection