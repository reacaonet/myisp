@extends('infra::layouts.master')

@section('title', 'Sessoes Ativas PPPoE')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Sessoes Ativas PPPoE</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('infra.mikrotik.pppoe-active') }}">
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
            <h3 class="font-semibold text-gray-900">
                {{ $selectedServer->name }} — {{ count($activeSessions) }} sessoes ativas
            </h3>
            <a href="{{ route('infra.mikrotik.pppoe-active', ['server_id' => $selectedServer->id]) }}"
               class="text-sm text-blue-600 hover:underline">Atualizar</a>
        </div>

        @if(count($activeSessions) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">.ID</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Nome</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Servico</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Caller ID</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">IP</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Uptime</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Acoes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($activeSessions as $session)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $session['.id'] ?? '' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $session['name'] ?? '' }}</td>
                        <td class="px-4 py-3">{{ $session['service'] ?? '' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $session['caller-id'] ?? '' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $session['address'] ?? '' }}</td>
                        <td class="px-4 py-3">{{ $session['uptime'] ?? '' }}</td>
                        <td class="px-4 py-3">
                            <form method="POST" action="{{ route('infra.mikrotik.kick-pppoe', $selectedServer->id) }}" class="inline"
                                  onsubmit="return confirm('Deseja desconectar esta sessao?')">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $session['.id'] ?? '' }}">
                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700">
                                    Desconectar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">Nenhuma sessao PPPoE ativa neste servidor.</div>
        @endif
    </div>
    @endif
</div>
@endsection
