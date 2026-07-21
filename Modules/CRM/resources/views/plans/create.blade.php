@extends('core::layouts.master')

@section('title', 'Novo Plano')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Novo Plano</h2>
        </div>
        <form method="POST" action="{{ route('crm.plans.store') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descricao</label>
                <textarea name="description" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Download (kbps) *</label>
                    <input type="number" name="download_speed" value="{{ old('download_speed', 10240) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload (kbps) *</label>
                    <input type="number" name="upload_speed" value="{{ old('upload_speed', 5120) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Taxa de Instalacao</label>
                    <input type="number" step="0.01" name="setup_fee" value="{{ old('setup_fee', 0) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciclo de Cobranca *</label>
                    <select name="billing_cycle" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="monthly">Mensal</option>
                        <option value="quarterly">Trimestral</option>
                        <option value="semiannual">Semestral</option>
                        <option value="annual">Anual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max. Conexoes Simultaneas</label>
                    <input type="number" name="max_simultaneous" value="{{ old('max_simultaneous') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pool PPPoE</label>
                    <input type="text" name="pool" value="{{ old('pool') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address List</label>
                    <input type="text" name="address_list" value="{{ old('address_list') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servidor</label>
                    <select name="server_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" @selected(old('server_id') == $server->id)>{{ $server->name }} ({{ $server->ip }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Servidor</label>
                    <select name="tipo_servidor" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="mikrotik" @selected(old('tipo_servidor') == 'mikrotik')>MikroTik</option>
                        <option value="ubnt" @selected(old('tipo_servidor') == 'ubnt')>Ubiquiti</option>
                        <option value="cisco" @selected(old('tipo_servidor') == 'cisco')>Cisco</option>
                        <option value="outro" @selected(old('tipo_servidor') == 'outro')>Outro</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interface</label>
                    <input type="text" name="interface" value="{{ old('interface') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max. Sessao (min)</label>
                    <input type="number" name="max_session_time" value="{{ old('max_session_time') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Police In</label>
                    <input type="text" name="police_in" value="{{ old('police_in') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Police Out</label>
                    <input type="text" name="police_out" value="{{ old('police_out') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">URL Anuncio</label>
                    <input type="text" name="url_advertise" value="{{ old('url_advertise') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Intervalo Anuncio (s)</label>
                    <input type="number" name="advertise_intervalo" value="{{ old('advertise_intervalo') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="has_pppoe" value="1" checked
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    PPPoE
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="has_hotspot" value="1"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Hotspot
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Ativo
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('crm.plans.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
