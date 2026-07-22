@extends('core::layouts.master')

@section('title', 'Criar Backup MikroTik')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Criar Backup MikroTik</h2>
        <a href="{{ route('crm.mikrotik-backups.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('crm.mikrotik-backups.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik *</label>
                <select name="server_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Selecione...</option>
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" {{ old('server_id') == $server->id ? 'selected' : '' }}>
                            {{ $server->name }} ({{ $server->ip_address }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Backup</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="full" selected>Completo (export full)</option>
                    <option value="config">Configuracao (export)</option>
                </select>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('crm.mikrotik-backups.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Criar Backup</button>
        </div>
    </form>
</div>
@endsection
