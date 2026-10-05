@extends('crm::portal.layouts.master')

@section('title', 'Solicitar mudanca de endereco')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <p class="font-medium">Revise os campos abaixo.</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Endereco atual</h3>
            <p class="text-xs text-gray-500">O endereco em uso hoje.</p>
        </div>
        <div class="p-6">
            @if($currentAddress)
                <p class="text-sm text-gray-900">{{ $currentAddress->full_address }}</p>
            @else
                <p class="text-sm text-gray-500">Nenhum endereco cadastrado.</p>
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('crm.portal.profile.address.store') }}">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Novo endereco</h3>
                <p class="text-xs text-gray-500">
                    A mudanca entra como chamado e so passa a valer depois que o suporte aprovar.
                </p>
            </div>

            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro *</label>
                    <input type="text" name="street" value="{{ old('street') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('street') border-red-500 @enderror">
                    @error('street') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Numero</label>
                        <input type="text" name="number" value="{{ old('number') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Referencia</label>
                        <input type="text" name="referencia" value="{{ old('referencia') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="complement" value="{{ old('complement') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro *</label>
                    <input type="text" name="neighborhood" value="{{ old('neighborhood') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('neighborhood') border-red-500 @enderror">
                    @error('neighborhood') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade *</label>
                        <input type="text" name="city" value="{{ old('city') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('city') border-red-500 @enderror">
                        @error('city') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF *</label>
                        <input type="text" name="state" value="{{ old('state') }}" maxlength="2" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm uppercase @error('state') border-red-500 @enderror">
                        @error('state') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP *</label>
                        <input type="text" name="zipcode" value="{{ old('zipcode') }}" maxlength="9" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm @error('zipcode') border-red-500 @enderror">
                        @error('zipcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <textarea name="reason" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="Opcional. Conte porque precisa mudar o endereco.">{{ old('reason') }}</textarea>
                    @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('crm.portal.profile') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Enviar solicitacao</button>
        </div>
    </form>
</div>
@endsection
