@extends('infra::layouts.master')

@section('title', 'Novo Usuario Provisionamento')

@section('content')
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
                            <option value="{{ $s->id }}" data-type="{{ $s->type }}" data-branch="{{ $s->branch_id }}">{{ $s->name }} ({{ $s->ip }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Escolhendo o cliente, a lista e filtrada pelo servidor da filial dele.</p>
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
                <select name="client_id" id="clientSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Nenhum</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" data-branch="{{ $c->branch_id }}" @selected(old('client_id')==$c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <div id="planInfo" class="hidden mt-2 text-sm"></div>
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Perfil</label>
                <p class="text-xs text-gray-500 mb-1">Se o cliente tiver contrato ativo com plano, o perfil sera gerado automaticamente com a banda contratada.</p>
                <select name="profile" id="profileSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Auto (usar plano do cliente)</option>
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
const clientSelect = document.getElementById('clientSelect');
const serverSelect = document.getElementById('serverSelect');

const serverOptions = Array.from(serverSelect.options).filter(o => o.value);

// A filial do cliente define o servidor: filtra a lista e ja deixa o
// servidor da filial selecionado quando ela tem um unico RB.
function filtrarServidoresPelaFilial() {
    const selected = serverSelect.value;
    const clientOption = clientSelect.options[clientSelect.selectedIndex];
    const branch = clientOption && clientOption.dataset.branch ? clientOption.dataset.branch : null;

    serverOptions.forEach(option => {
        option.hidden = branch !== null && option.dataset.branch !== branch;
    });

    if (branch !== null) {
        const daFilial = serverOptions.filter(o => !o.hidden);
        if (daFilial.length === 1) {
            serverSelect.value = daFilial[0].value;
            serverSelect.dispatchEvent(new Event('change'));
            return;
        }
    }

    if (selected && serverSelect.querySelector(`option[value="${selected}"]`).hidden) {
        serverSelect.value = '';
    }
}

clientSelect.addEventListener('change', function() {
    filtrarServidoresPelaFilial();

    const clientId = this.value;
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
                planInfo.innerHTML = '<span class="text-blue-700 bg-blue-50 border border-blue-200 rounded px-2 py-1 inline-block">Plano do contrato ativo: <strong>' +
                    data.plan.name + '</strong> - ' + Math.round(data.plan.download_speed / 1000) + 'M/' + Math.round(data.plan.upload_speed / 1000) + 'M</span>';
            } else {
                planInfo.classList.add('hidden');
                planInfo.innerHTML = '';
            }
        })
        .catch(() => {});
});

document.getElementById('serverSelect').addEventListener('change', function() {
    const serverId = this.value;
    const profileSelect = document.getElementById('profileSelect');
    const typeSelect = document.getElementById('typeSelect');
    profileSelect.innerHTML = '<option value="">Carregando...</option>';

    if (!serverId) {
        profileSelect.innerHTML = '<option value="">Auto (usar plano do cliente)</option>';
        return;
    }

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
                    profileSelect.innerHTML += `<option value="${name}">${name}</option>`;
                });
            }
        })
        .catch(err => {
            profileSelect.innerHTML = '<option value="">' + (err.message || 'Erro ao carregar perfis') + '</option>';
        });
});

document.getElementById('typeSelect').addEventListener('change', function() {
    serverSelect.dispatchEvent(new Event('change'));
});

if ('{{ old('client_id') }}') {
    filtrarServidoresPelaFilial();
    clientSelect.dispatchEvent(new Event('change'));
}
</script>
@endpush
@endsection
