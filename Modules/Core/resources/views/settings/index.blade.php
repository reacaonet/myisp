@extends('core::layouts.master')

@section('title', 'Configuracoes do Sistema')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Configuracoes do Sistema</h2>
        <a href="{{ route('core.settings.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Nova Configuracao</a>
    </div>

    <form method="POST" action="{{ route('core.settings.update') }}">
        @csrf @method('PUT')

        @foreach($settings as $group => $items)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900 uppercase text-sm">{{ ucfirst($group) }}</h3>
            </div>
            <div class="p-4 space-y-4">
                @foreach($items as $setting)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $setting->key }}</label>
                    @if($setting->type === 'textarea')
                        <textarea name="settings[{{ $setting->key }}]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ $setting->value }}</textarea>
                    @elseif($setting->type === 'boolean')
                        <select name="settings[{{ $setting->key }}]" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Sim</option>
                            <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Nao</option>
                        </select>
                    @elseif($setting->type === 'password')
                        <input type="password" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @elseif($setting->type === 'number')
                        <input type="number" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @else
                        <input type="text" name="settings[{{ $setting->key }}]" value="{{ $setting->value }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Configuracoes</button>
        </div>
    </form>
</div>
@endsection
