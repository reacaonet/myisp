@extends('core::layouts.master')

@section('title', 'Novo Gateway de Pagamento')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Novo Gateway de Pagamento</h2>
        <a href="{{ route('billing.gateways.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="POST" action="{{ route('billing.gateways.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ old('name') }}" placeholder="Ex: Mercado Pago Producao">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Plataforma *</label>
                    <select name="slug" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        <option value="mercado-pago" {{ old('slug') === 'mercado-pago' ? 'selected' : '' }}>Mercado Pago</option>
                        <option value="asaas" {{ old('slug') === 'asaas' ? 'selected' : '' }}>Asaas</option>
                        <option value="gerencianet" {{ old('slug') === 'gerencianet' ? 'selected' : '' }}>Gerencianet (Efi)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Ativo</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Metodos de Pagamento</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="supports_boleto" value="1" {{ old('supports_boleto', 1) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm">Boleto</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="supports_pix" value="1" {{ old('supports_pix', 1) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm">PIX</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="supports_credit_card" value="1" {{ old('supports_credit_card') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm">Cartao</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="supports_recurrence" value="1" {{ old('supports_recurrence') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                        <span class="text-sm">Recorrencia</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Configuracao (JSON)</label>
                <textarea name="config" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono" placeholder='{
    "access_token": "APP_USR-xxx",
    "sandbox": true
}'>{{ old('config') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">
                    <strong>Mercado Pago:</strong> access_token, sandbox, payer_email<br>
                    <strong>Asaas:</strong> api_key, sandbox<br>
                    <strong>Gerencianet:</strong> client_id, client_secret, sandbox
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: Conta de producao verificada em 01/2026">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('billing.gateways.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Criar Gateway</button>
        </div>
    </form>
</div>
@endsection
