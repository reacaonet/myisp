@extends('core::layouts.master')

@section('title', 'Configuracoes do Sistema')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Configuracoes do Sistema</h2>
        <a href="{{ route('core.settings.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Nova Configuracao</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('core.settings.update') }}">
        @csrf @method('PUT')

        @php
            $labels = [
                'company_name' => ['label' => 'Razao Social', 'placeholder' => 'Razao social da empresa'],
                'company_fantasy' => ['label' => 'Nome Fantasia', 'placeholder' => 'Nome fantasia da empresa'],
                'company_document' => ['label' => 'CNPJ', 'placeholder' => '00.000.000/0001-00'],
                'company_state_registration' => ['label' => 'Inscricao Estadual', 'placeholder' => 'IE ou ISENTO'],
                'company_municipal_registration' => ['label' => 'Inscricao Municipal', 'placeholder' => 'IM'],
                'company_phone' => ['label' => 'Telefone', 'placeholder' => '(00) 0000-0000'],
                'company_cellphone' => ['label' => 'Celular', 'placeholder' => '(00) 00000-0000'],
                'company_email' => ['label' => 'Email', 'placeholder' => 'contato@empresa.com.br'],
                'company_website' => ['label' => 'Site', 'placeholder' => 'https://www.empresa.com.br'],
                'company_address' => ['label' => 'Endereco', 'placeholder' => 'Rua, numero - Bairro'],
                'company_city' => ['label' => 'Cidade', 'placeholder' => 'Sao Paulo'],
                'company_state' => ['label' => 'Estado', 'placeholder' => 'SP'],
                'company_zip' => ['label' => 'CEP', 'placeholder' => '00000-000'],
                'bank_name' => ['label' => 'Banco', 'placeholder' => 'Banco do Brasil'],
                'bank_code' => ['label' => 'Codigo do Banco', 'placeholder' => '001'],
                'bank_agency' => ['label' => 'Agencia', 'placeholder' => '0000-0'],
                'bank_account' => ['label' => 'Conta', 'placeholder' => '00000-0'],
                'bank_account_type' => ['label' => 'Tipo de Conta', 'placeholder' => 'Corrente ou Poupanca'],
                'bank_cedente' => ['label' => 'Cedente', 'placeholder' => 'Nome do cedente'],
                'bank_cnpj_cedente' => ['label' => 'CNPJ do Cedente', 'placeholder' => '00.000.000/0001-00'],
                'bank_carteira' => ['label' => 'Carteira', 'placeholder' => '17 ou 109'],
                'bank_convenio' => ['label' => 'Convenio', 'placeholder' => 'Numero do convenio'],
                'boleto_layout' => ['label' => 'Layout do Boleto', 'placeholder' => 'boleto ou carteira'],
                'boleto_instrucoes' => ['label' => 'Instrucoes no Boleto', 'placeholder' => 'Instrucoes para o pagador'],
                'default_gateway' => ['label' => 'Gateway Padrao', 'placeholder' => 'mercado-pago, asaas ou gerencianet'],
            ];
        @endphp

        @foreach($settings as $group => $items)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-4 border-b border-gray-200 flex items-center gap-2">
                @if($group === 'company')
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @elseif($group === 'billing')
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                @else
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @endif
                <h3 class="font-semibold text-gray-900 uppercase text-sm">
                    {{ $group === 'company' ? 'Dados da Empresa' : ($group === 'billing' ? 'Financeiro / Banco' : ucfirst($group)) }}
                </h3>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($items as $setting)
                <div class="{{ in_array($setting->key, ['company_address', 'boleto_instrucoes']) ? 'md:col-span-2' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $labels[$setting->key]['label'] ?? ucfirst(str_replace('_', ' ', $setting->key)) }}
                    </label>
                    @if($setting->type === 'textarea')
                        <textarea name="settings[{{ $setting->key }}]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">{{ $setting->value }}</textarea>
                    @elseif($setting->type === 'boolean')
                        <select name="settings[{{ $setting->key }}]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Sim</option>
                            <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Nao</option>
                        </select>
                    @elseif($setting->type === 'password')
                        <input type="password" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">
                    @elseif($setting->type === 'number')
                        <input type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">
                    @else
                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="{{ $labels[$setting->key]['placeholder'] ?? '' }}">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Configuracoes</button>
        </div>
    </form>
</div>
@endsection
