@extends('infra::layouts.master')

@section('title', 'Logs do MikroTik')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Logs do MikroTik</h2>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('infra.mikrotik.logs') }}">
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">{{ $selectedServer->name }} — {{ count($logs) }} entradas</h3>
            <a href="{{ route('infra.mikrotik.logs', ['server_id' => $selectedServer->id]) }}"
               class="text-sm text-blue-600 hover:underline">Atualizar</a>
        </div>

        @if(count($logs) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Data/Hora</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Topico</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Mensagem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs whitespace-nowrap">{{ $log['time'] ?? '' }}</td>
                        <td class="px-4 py-3">
                            @php $topics = $log['topics'] ?? ''; @endphp
                            @if(str_contains($topics, 'error') || str_contains($topics, 'critical'))
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-medium">{{ $topics }}</span>
                            @elseif(str_contains($topics, 'warning'))
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-medium">{{ $topics }}</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">{{ $topics }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-700">{{ $log['message'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">Nenhum log encontrado.</div>
        @endif
    </div>
    @endif
</div>
@endsection
