@extends('core::layouts.master')

@section('title', 'Editar Permissoes - ' . $user->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Permissoes de {{ $user->name }}</h2>
        <a href="{{ route('core.permissions.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <form method="POST" action="{{ route('core.permissions.update', $user) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf @method('PUT')

        <div class="space-y-3">
            @foreach($permissions as $key => $perm)
            <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100">
                <input type="checkbox" name="perm_{{ $key }}" value="1" {{ $perm['granted'] ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600">
                <span class="text-sm font-medium text-gray-700">{{ $perm['label'] }}</span>
            </label>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('core.permissions.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar Permissoes</button>
        </div>
    </form>
</div>
@endsection
