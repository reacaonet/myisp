@extends('infra::layouts.master')

@section('title', 'Novo Usuario Provisionamento')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Novo Usuario MikroTik</h2>
        </div>
        <form method="POST" action="{{ route('infra.provisioning.store') }}" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik *</label>
                    <select name="mikrotik_server_id" id="serverSelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($servers as $s)
                            <option value="{{ $s->id }}" data-type="{{ $s->type }}">{{ $s->name }} ({{ $s->ip }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" id="typeSelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="pppoe">PPPoE</option>
                        <option value="hotspot">Hotspot</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (opcional)</label>
                <select name="client_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Nenhum</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login *</label>
                    <input type="text" name="login" value="{{ old('login') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha *</label>
                    <input type="text" name="password" value="{{ old('password') }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Perfil *</label>
                <select name="profile" id="profileSelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Selecione o servidor primeiro...</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MAC Address</label>
                    <input type="text" name="mac" value="{{ old('mac') }}" placeholder="AA:BB:CC:DD:EE:FF" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Estatico</label>
                    <input type="text" name="ip" value="{{ old('ip') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('infra.provisioning.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Provisionar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('serverSelect').addEventListener('change', function() {
    const serverId = this.value;
    const profileSelect = document.getElementById('profileSelect');
    const typeSelect = document.getElementById('typeSelect');
    profileSelect.innerHTML = '<option value="">Carregando...</option>';

    if (!serverId) {
        profileSelect.innerHTML = '<option value="">Selecione o servidor primeiro...</option>';
        return;
    }

    fetch(`/crm/provisioning/profiles/${serverId}`)
        .then(r => r.json())
        .then(data => {
            profileSelect.innerHTML = '';
            const type = typeSelect.value;
            const profiles = type === 'pppoe' ? data.ppp_profiles : data.hotspot_profiles;

            if (profiles && profiles.length) {
                profiles.forEach(p => {
                    const name = p.name || p['.id'];
                    profileSelect.innerHTML += `<option value="${name}">${name}</option>`;
                });
            } else {
                profileSelect.innerHTML = '<option value="">Nenhum perfil encontrado</option>';
            }
        })
        .catch(() => {
            profileSelect.innerHTML = '<option value="">Erro ao carregar perfis</option>';
        });
});

document.getElementById('typeSelect').addEventListener('change', function() {
    document.getElementById('serverSelect').dispatchEvent(new Event('change'));
});
</script>
@endpush
@endsection
