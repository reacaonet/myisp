@extends('core::layouts.master')

@section('title', 'Nova CTO')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Nova CTO</h2>
        </div>
        <form method="POST" action="{{ route('ftth.ctos.store') }}" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Codigo *</label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="CTO0001">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Latitude *</label>
                    <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Longitude *</label>
                    <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Capacidade *</label>
                    <input type="number" name="capacity" value="{{ old('capacity', 8) }}" min="1" max="256" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Caixa de Emenda</label>
                <select name="caixa_emenda_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Nenhuma</option>
                    @foreach($caixas as $caixa)
                        <option value="{{ $caixa->id }}" @selected(old('caixa_emenda_id') == $caixa->id)>{{ $caixa->code }} - {{ $caixa->street }}</option>
                    @endforeach
                </select>
            </div>

            <div class="border-t border-gray-200 pt-4">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Endereco</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rua</label>
                        <input type="text" name="street" value="{{ old('street') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Numero</label>
                        <input type="text" name="number" value="{{ old('number') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        <input type="text" name="neighborhood" value="{{ old('neighborhood') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                        <input type="text" name="city" value="{{ old('city') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                        <input type="text" name="state" value="{{ old('state') }}" maxlength="2"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('ftth.ctos.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
