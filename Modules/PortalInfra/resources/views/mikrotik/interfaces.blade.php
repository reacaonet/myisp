@extends('infra::layouts.master')

@section('title', 'Interfaces MikroTik')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Interfaces MikroTik</h2>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('infra.mikrotik.interfaces') }}">
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servidor MikroTik</label>
                    <select name="server_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" @selected($selectedServer && $selectedServer->id === $server->id)>
                                {{ $server->name }} ({{ $server->ip }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Conectar
                </button>
            </div>
        </form>
    </div>

    @if($selectedServer)
    @if(!empty($resource))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">CPU</div>
            <div class="text-lg font-bold text-gray-900">{{ $resource['cpu'] ?? 'N/A' }}%</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">Memoria Livre</div>
            <div class="text-lg font-bold text-gray-900">{{ number_format(($resource['free-memory'] ?? 0) / 1048576, 1) }} MB</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">Disco Livre</div>
            <div class="text-lg font-bold text-gray-900">{{ number_format(($resource['free-hdd-space'] ?? 0) / 1048576, 1) }} MB</div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">Uptime</div>
            <div class="text-lg font-bold text-gray-900">{{ $resource['uptime'] ?? 'N/A' }}</div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">{{ $selectedServer->name }} — {{ count($interfaces) }} interfaces</h3>
            <a href="{{ route('infra.mikrotik.interfaces', ['server_id' => $selectedServer->id]) }}"
               class="text-sm text-blue-600 hover:underline">Atualizar</a>
        </div>

        @if(count($interfaces) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Nome</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Tipo</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Disabled</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">RX Rate</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">TX Rate</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">RX Bytes</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">TX Bytes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($interfaces as $iface)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $iface['name'] ?? '' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $iface['type'] ?? '' }}</td>
                        <td class="px-4 py-3">
                            @if(($iface['running'] ?? '') === 'true' || ($iface['running'] ?? '') === true)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">Up</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">Down</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if(($iface['disabled'] ?? '') === 'true' || ($iface['disabled'] ?? '') === true)
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">Sim</span>
                            @else
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">Nao</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">{{ $iface['rx-rate'] ?? '' }}</td>
                        <td class="px-4 py-3 text-xs">{{ $iface['tx-rate'] ?? '' }}</td>
                        <td class="px-4 py-3 text-xs">{{ number_format(($iface['rx-byte'] ?? 0) / 1048576, 2) }} MB</td>
                        <td class="px-4 py-3 text-xs">{{ number_format(($iface['tx-byte'] ?? 0) / 1048576, 2) }} MB</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">Nenhuma interface encontrada.</div>
        @endif
    </div>
    @endif
</div>
@endsection
