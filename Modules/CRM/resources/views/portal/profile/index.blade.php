@extends('crm::portal.layouts.master')

@section('title', 'Dados Pessoais')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Dados do Cadastro</h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Nome</dt>
                    <dd class="font-medium text-gray-900">{{ $client->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Documento</dt>
                    <dd class="font-medium text-gray-900">{{ $client->document }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Login</dt>
                    <dd class="font-medium text-gray-900">{{ $client->login }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Tipo</dt>
                    <dd class="font-medium text-gray-900">{{ $client->type == 'individual' ? 'Pessoa Física' : 'Pessoa Jurídica' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Status</dt>
                    <dd>@include('crm::clients._status_badge', ['status' => $client->status])</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Data de Cadastro</dt>
                    <dd class="font-medium text-gray-900">{{ $client->created_at->format('d/m/Y') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Atualizar Contato</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('crm.portal.profile.update') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $client->email) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('email') border-red-500 @enderror">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
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
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Contato</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Alterar Senha</h3>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('crm.portal.profile.password') }}" class="space-y-4 max-w-md">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha Atual</label>
                    <input type="password" name="current_senha" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('current_senha') border-red-500 @enderror">
                    @error('current_senha') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                    <input type="password" name="new_senha" required minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('new_senha') border-red-500 @enderror">
                    @error('new_senha') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                    <input type="password" name="new_senha_confirmation" required minlength="6"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Alterar Senha</button>
            </form>
        </div>
    </div>
</div>
@endsection
