@extends('infra::layouts.master')

@section('title', 'Editar Usuario Provisionamento')

@section('content')
@php
    $params = $record->params ?? [];
@endphp
<div class="max-w-2xl mx-auto">
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar Usuario MikroTik</h2>
            <p class="text-sm text-gray-500 mt-1">Registro #{{ $record->id }} - {{ ucfirst($record->action) }}</p>
        </div>
        <form method="POST" action="{{ route('infra.provisioning.update', $record) }}" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik *</label>
                    <select name="mikrotik_server_id" id="serverSelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($servers as $s)
                            <option value="{{ $s->id }}" @selected(old('mikrotik_server_id', $record->mikrotik_server_id) == $s->id)>{{ $s->name }} ({{ $s->ip }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" id="typeSelect" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="pppoe" @selected(old('type', $record->type) == 'pppoe')>PPPoE</option>
                        <option value="hotspot" @selected(old('type', $record->type) == 'hotspot')>Hotspot</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente (opcional)</label>
                <select name="client_id" id="clientSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Nenhum</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $record->client_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <div id="planInfo" class="hidden mt-2 text-sm"></div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login *</label>
                    <input type="text" name="login" value="{{ old('login', $record->login) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" disabled>
                    <p class="text-xs text-gray-400 mt-1">O login nao pode ser alterado.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="text" name="password" value="{{ old('password', $params['password'] ?? '') }}" placeholder="Deixe um asterisco para manter a senha" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Perfil (ou plano do contrato do cliente)</label>
                <p class="text-xs text-gray-500 mb-1">Se o cliente tiver contrato ativo com plano, o perfil sera gerado automaticamente com a banda contratada.</p>
                <select name="profile" id="profileSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Auto (usar plano do cliente)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MAC Address</label>
                    <input type="text" name="mac" value="{{ old('mac', $params['mac'] ?? '') }}" placeholder="AA:BB:CC:DD:EE:FF" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Estatico</label>
                    <input type="text" name="ip" value="{{ old('ip', $params['ip'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('infra.provisioning.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Alteracoes</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function loadProfiles() {
    const serverId = document.getElementById('serverSelect').value;
    const profileSelect = document.getElementById('profileSelect');
    const typeSelect = document.getElementById('typeSelect');

    if (!serverId) {
        profileSelect.innerHTML = '<option value="">Selecione o servidor primeiro...</option>';
        return;
    }

    profileSelect.innerHTML = '<option value="">Carregando...</option>';

    fetch(`{{ route('infra.provisioning.profiles', ['server_id' => '__SERVER_ID__']) }}`.replace('__SERVER_ID__', serverId))
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) throw new Error(data.error || 'Erro ao carregar perfis');

            profileSelect.innerHTML = '<option value="">Auto (usar plano do cliente)</option>';
            const type = typeSelect.value;
            const profiles = type === 'pppoe' ? data.ppp_profiles : data.hotspot_profiles;

            if (profiles && profiles.length) {
                profiles.forEach(p => {
                    const name = p.name || p['.id'];
                    const selected = '{{ old('profile', $params['profile'] ?? '') }}' === name ? 'selected' : '';
                    profileSelect.innerHTML += `<option value="${name}" ${selected}>${name}</option>`;
                });
            }
        })
        .catch(err => {
            profileSelect.innerHTML = '<option value="">' + (err.message || 'Erro ao carregar perfis') + '</option>';
        });
}

function loadPlanInfo() {
    const clientId = document.getElementById('clientSelect').value;
    const planInfo = document.getElementById('planInfo');

    if (!clientId) {
        planInfo.classList.add('hidden');
        planInfo.innerHTML = '';
        return;
    }

    fetch(`{{ route('infra.provisioning.client-plan', ['client_id' => '__CLIENT_ID__']) }}`.replace('__CLIENT_ID__', clientId))
        .then(r => r.json())
        .then(data => {
            if (data.plan) {
                planInfo.classList.remove('hidden');
                planInfo.innerHTML = '<span class="text-blue-700 bg-blue-50 border border-blue-200 rounded px-2 py-1 inline-block">Plano: <strong>' +
                    data.plan.name + '</strong> - ' + Math.round(data.plan.download_speed / 1000) + 'M/' + Math.round(data.plan.upload_speed / 1000) + 'M</span>';
            } else {
                planInfo.classList.add('hidden');
                planInfo.innerHTML = '';
            }
        })
        .catch(() => {});
}

document.getElementById('serverSelect').addEventListener('change', loadProfiles);
document.getElementById('typeSelect').addEventListener('change', loadProfiles);
document.getElementById('clientSelect').addEventListener('change', loadPlanInfo);

loadProfiles();
loadPlanInfo();
</script>
@endpush
@endsection