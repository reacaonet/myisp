@extends('infra::layouts.master')

@section('title', 'Gerar Rede FTTH')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Gerar Rede Automaticamente</h2>
            <p class="text-sm text-gray-500 mt-1">Insira coordenadas geograficas (lat, lng) para gerar CTOs a cada 250m e caixas de emenda automaticas.</p>
        </div>
        <form method="POST" action="{{ route('infra.ftth.generate.run') }}" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Rua *</label>
                    <input type="text" name="street_name" value="{{ old('street_name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Rua das Flores">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prefixo (opcional)</label>
                    <input type="text" name="prefix" value="{{ old('prefix') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" placeholder="FLN">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Coordenadas (lat, lng por linha) *</label>
                <textarea name="coordinates" rows="12" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm font-mono"
                          placeholder="-27.5953000, -48.5480000
-27.5954000, -48.5481000
-27.5955000, -48.5482000
-27.5956000, -48.5483000
-27.5957000, -48.5484000">{{ old('coordinates') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Uma coordenada por linha, formato: latitude, longitude. Obtenha do Overpass API ou Google Maps.</p>
            </div>

            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-600">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-blue-800 mb-2">Como funciona?</h3>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>1. O sistema percorre as coordenadas calculando distancia com Haversine</li>
                    <li>2. A cada 250m, uma CTO e criada nessa posicao</li>
                    <li>3. A cada 4 CTOs, uma Caixa de Emenda e criada no centro geografico</li>
                    <li>4. As CTOs sao automaticamente vinculadas a caixa mais proxima</li>
                </ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('infra.dashboard') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Gerar Rede</button>
            </div>
        </form>
    </div>
</div>
@endsection
