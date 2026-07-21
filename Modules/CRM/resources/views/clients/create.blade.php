@extends('core::layouts.master')

@section('title', 'Novo Cliente')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Novo Cliente</h2>
        </div>
        <form method="POST" action="{{ route('crm.clients.store') }}" class="p-6 space-y-6">
            @csrf

            {{-- Dados Pessoais --}}
            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Dados Pessoais</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo</label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="individual" @selected(old('type') == 'individual')>Pessoa Fisica</option>
                        <option value="legal" @selected(old('type') == 'legal')>Pessoa Juridica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ *</label>
                    <input type="text" name="document" value="{{ old('document') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('document') border-red-500 @enderror">
                    @error('document') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RG/IE</label>
                    <input type="text" name="rg" value="{{ old('rg') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Insc. Estadual</label>
                    <input type="text" name="state_registration" value="{{ old('state_registration') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nascimento</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado Civil</label>
                    <input type="text" name="estado_civil" value="{{ old('estado_civil') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naturalidade</label>
                    <input type="text" name="naturalidade" value="{{ old('naturalidade') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pai</label>
                    <input type="text" name="pai" value="{{ old('pai') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mae</label>
                    <input type="text" name="mae" value="{{ old('mae') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            {{-- Contato --}}
            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Contato</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Celular</label>
                    <input type="text" name="cellphone" value="{{ old('cellphone') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            {{-- Acesso ao Portal --}}
            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Acesso ao Portal</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                    <input type="text" name="login" value="{{ old('login') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input type="password" name="senha" value="{{ old('senha') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            {{-- Endereco --}}
            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Endereco</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                    <input type="text" name="street" value="{{ old('street') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero</label>
                    <input type="text" name="number" value="{{ old('number') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text" name="complement" value="{{ old('complement') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                    <input type="text" name="referencia" value="{{ old('referencia') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                    <input type="text" name="neighborhood" value="{{ old('neighborhood') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                    <input type="text" name="zipcode" value="{{ old('zipcode') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                    <input type="text" name="state" value="{{ old('state') }}" maxlength="2" placeholder="SP"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            {{-- Configuracoes Fiscais --}}
            <h3 class="text-md font-semibold text-gray-800 border-b pb-2">Configuracoes</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Entrada</label>
                    <input type="date" name="data_entrada" value="{{ old('data_entrada', date('Y-m-d')) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Venc. Contrato</label>
                    <input type="date" name="vcto_contrato" value="{{ old('vcto_contrato') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CFOP</label>
                    <input type="text" name="cfop" value="{{ old('cfop', '5307') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grupo</label>
                    <select name="grupo" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="N" @selected(old('grupo') == 'N')>NORMAL</option>
                        <option value="V" @selected(old('grupo') == 'V')>VIP</option>
                        <option value="L" @selected(old('grupo') == 'L')>LOCACAO</option>
                        <option value="C" @selected(old('grupo') == 'C')>COMODATO</option>
                        <option value="FC" @selected(old('grupo') == 'FC')>FIDELIDADE COMODATO</option>
                        <option value="F" @selected(old('grupo') == 'F')>FUNCIONARIO</option>
                        <option value="CT" @selected(old('grupo') == 'CT')>CORTESIA</option>
                        <option value="I" @selected(old('grupo') == 'I')>INADIMPLENTE</option>
                        <option value="NE" @selected(old('grupo') == 'NE')>NEGATIVADO</option>
                        <option value="O" @selected(old('grupo') == 'O')>OUTROS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Assinante</label>
                    <select name="tipo_assinante" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="pf" @selected(old('tipo_assinante') == 'pf')>Pessoa Física</option>
                        <option value="pj" @selected(old('tipo_assinante') == 'pj')>Pessoa Jurídica</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Utilizacao</label>
                    <select name="tipo_utilizacao" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="residencial" @selected(old('tipo_utilizacao') == 'residencial')>Residencial</option>
                        <option value="comercial" @selected(old('tipo_utilizacao') == 'comercial')>Comercial</option>
                        <option value="institucional" @selected(old('tipo_utilizacao') == 'institucional')>Institucional</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="active" @selected(old('status', 'active') == 'active')>Ativo</option>
                        <option value="inactive" @selected(old('status') == 'inactive')>Inativo</option>
                        <option value="suspended" @selected(old('status') == 'suspended')>Suspenso</option>
                        <option value="canceled" @selected(old('status') == 'canceled')>Cancelado</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="nf" value="1" @checked(old('nf', false)) class="rounded border-gray-300">
                    <label class="text-sm text-gray-700">Emitir NFSe</label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('crm.clients.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
