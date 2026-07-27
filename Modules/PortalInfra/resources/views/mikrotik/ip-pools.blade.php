@extends('infra::layouts.master')

@section('title', 'IP Pools')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">IP Pools (MikroTik)</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('infra.mikrotik.ip-pools') }}">
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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-900">{{ $selectedServer->name }} — {{ count($pools) }} pools</h3>
                </div>
                @if(count($pools) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">.ID</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Nome</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Ranges</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-500">Acoes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($pools as $pool)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs">{{ $pool['.id'] ?? '' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $pool['name'] ?? '' }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $pool['ranges'] ?? '' }}</td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="{{ route('infra.mikrotik.ip-pools.destroy', $selectedServer->id) }}" class="inline"
                                          onsubmit="return confirm('Deseja remover este pool?')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="pool_name" value="{{ $pool['name'] ?? '' }}">
                                        <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-xs font-medium hover:bg-red-700">
                                            Remover
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-gray-500">Nenhum IP Pool encontrado.</div>
                @endif
            </div>
        </div>

        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Criar IP Pool</h3>
                <form method="POST" action="{{ route('infra.mikrotik.ip-pools.store') }}">
                    @csrf
                    <input type="hidden" name="server_id" value="{{ $selectedServer->id }}">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                            <input type="text" name="name" required placeholder="ex: pool-wifi"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ old('name') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ranges *</label>
                            <input type="text" name="ranges" required placeholder="ex: 192.168.1.10-192.168.1.200"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ old('ranges') }}">
                        </div>
                    </div>
                    <button type="submit" class="mt-4 w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Criar Pool
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
