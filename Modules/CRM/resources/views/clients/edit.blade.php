@extends('core::layouts.master')

@section('title', 'Editar Cliente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar Cliente: {{ $client->name }}</h2>
        </div>
        <form method="POST" action="{{ route('crm.clients.update', $client) }}" class="p-6 space-y-6">
            @csrf @method('PUT')

            @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Dados Pessoais</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $client->name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('name') border-red-500 @enderror">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $client->codigo) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="individual" @selected(old('type', $client->type) == 'individual')>Pessoa Fisica</option>
                        <option value="legal" @selected(old('type', $client->type) == 'legal')>Pessoa Juridica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
                    <input type="text" name="document" value="{{ old('document', $client->document) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RG/IE</label>
                    <input type="text" name="rg" value="{{ old('rg', $client->rg) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Insc. Estadual</label>
                    <input type="text" name="state_registration" value="{{ old('state_registration', $client->state_registration) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nascimento</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $client->birth_date?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado Civil</label>
                    <input type="text" name="estado_civil" value="{{ old('estado_civil', $client->estado_civil) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naturalidade</label>
                    <input type="text" name="naturalidade" value="{{ old('naturalidade', $client->naturalidade) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pai</label>
                    <input type="text" name="pai" value="{{ old('pai', $client->pai) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mae</label>
                    <input type="text" name="mae" value="{{ old('mae', $client->mae) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Contato</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Celular</label>
                    <input type="text" name="cellphone" value="{{ old('cellphone', $client->cellphone) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Acesso ao Portal</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                    <input type="text" name="login" value="{{ old('login', $client->login) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password" name="senha" value="{{ old('senha') }}" placeholder="Deixe em branco para manter"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            @php $addr = $client->addresses->first(); @endphp
            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Endereco</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                    <input type="text" name="street" value="{{ old('street', $addr->street ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero</label>
                    <input type="text" name="number" value="{{ old('number', $addr->number ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text" name="complement" value="{{ old('complement', $addr->complement ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                    <input type="text" name="referencia" value="{{ old('referencia', $addr->referencia ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                    <input type="text" name="neighborhood" value="{{ old('neighborhood', $addr->neighborhood ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                    <input type="text" name="zipcode" value="{{ old('zipcode', $addr->zipcode ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                    <input type="text" name="city" value="{{ old('city', $addr->city ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                    <input type="text" name="state" value="{{ old('state', $addr->state ?? '') }}" maxlength="2"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Configuracoes</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Entrada</label>
                    <input type="date" name="data_entrada" value="{{ old('data_entrada', $client->data_entrada?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Venc. Contrato</label>
                    <input type="date" name="vcto_contrato" value="{{ old('vcto_contrato', $client->vcto_contrato?->format('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CFOP</label>
                    <input type="text" name="cfop" value="{{ old('cfop', $client->cfop) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                    <select name="grupo" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach(['N'=>'NORMAL','V'=>'VIP','L'=>'LOCACAO','C'=>'COMODATO','FC'=>'FIDELIDADE COMODATO','F'=>'FUNCIONARIO','CT'=>'CORTESIA','I'=>'INADIMPLENTE','NE'=>'NEGATIVADO','O'=>'OUTROS'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('grupo', $client->grupo) == $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Assinante</label>
                    <select name="tipo_assinante" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="pf" @selected(old('tipo_assinante', $client->tipo_assinante) == 'pf')>Pessoa Física</option>
                        <option value="pj" @selected(old('tipo_assinante', $client->tipo_assinante) == 'pj')>Pessoa Jurídica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Utilizacao</label>
                    <select name="tipo_utilizacao" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="residencial" @selected(old('tipo_utilizacao', $client->tipo_utilizacao) == 'residencial')>Residencial</option>
                        <option value="comercial" @selected(old('tipo_utilizacao', $client->tipo_utilizacao) == 'comercial')>Comercial</option>
                        <option value="institucional" @selected(old('tipo_utilizacao', $client->tipo_utilizacao) == 'institucional')>Institucional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="active" @selected(old('status', $client->status) == 'active')>Ativo</option>
                        <option value="inactive" @selected(old('status', $client->status) == 'inactive')>Inativo</option>
                        <option value="suspended" @selected(old('status', $client->status) == 'suspended')>Suspenso</option>
                        <option value="canceled" @selected(old('status', $client->status) == 'canceled')>Cancelado</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="nf" value="1" @checked(old('nf', $client->nf)) class="rounded border-gray-300">
                    <label class="text-sm text-gray-700">Emitir NFSe</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes', $client->notes) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('crm.clients.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
