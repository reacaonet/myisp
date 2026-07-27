@extends('infra::layouts.master')

@section('title', 'Editar Servidor MikroTik')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar Servidor MikroTik</h2>
        </div>
        <form method="POST" action="{{ route('infra.mikrotik-servers.update', $server) }}" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input type="text" name="name" value="{{ old('name', $server->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP *</label>
                    <input type="text" name="ip" value="{{ old('ip', $server->ip) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Porta API *</label>
                    <input type="number" name="port" value="{{ old('port', $server->port) }}" required min="1" max="65535" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login *</label>
                    <input type="text" name="login" value="{{ old('login', $server->login) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password" name="senha" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Deixe vazio para manter">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="both" @selected(old('type', $server->type)=='both')>PPPoE + Hotspot</option>
                    <option value="pppoe" @selected(old('type', $server->type)=='pppoe')>Apenas PPPoE</option>
                    <option value="hotspot" @selected(old('type', $server->type)=='hotspot')>Apenas Hotspot</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $server->is_active)) class="rounded border-gray-300">
                <label class="text-sm text-gray-700">Ativo</label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes', $server->notes) }}</textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('infra.mikrotik-servers.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
