@extends('core::layouts.master')

@section('title', 'Bloqueio de Sites')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Bloqueio de Sites (Firewall Address List)</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Bloquear Site</h3>
            <form method="POST" action="{{ route('crm.site-blocking.block') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik</label>
                        <select name="server_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Selecione...</option>
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}">{{ $server->name }} ({{ $server->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IP ou Dominio</label>
                        <input type="text" name="address" required placeholder="ex: 8.8.8.8 ou facebook.com"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ old('address') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Lista</label>
                        <input type="text" name="list_name" value="{{ old('list_name', 'blocked_sites') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <button type="submit" class="mt-4 w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Bloquear</button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Desbloquear Site</h3>
            <form method="POST" action="{{ route('crm.site-blocking.unblock') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik</label>
                        <select name="server_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Selecione...</option>
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}">{{ $server->name }} ({{ $server->ip_address }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">IP ou Dominio</label>
                        <input type="text" name="address" required placeholder="IP ou dominio a desbloquear"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ old('address') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Lista</label>
                        <input type="text" name="list_name" value="{{ old('list_name', 'blocked_sites') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>
                <button type="submit" class="mt-4 w-full px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Desbloquear</button>
            </form>
        </div>
    </div>

    @if(isset($blockedSites) && count($blockedSites) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6 overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold text-gray-900">Sites Bloqueados ({{ count($blockedSites) }})</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Servidor</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">IP / Dominio</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Lista</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($blockedSites as $site)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $site['server'] ?? 'N/A' }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $site['address'] }}</td>
                    <td class="px-4 py-3">{{ $site['list'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
