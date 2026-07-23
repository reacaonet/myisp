@extends('core::layouts.master')

@section('title', 'Regras NAT / Firewall')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Regras NAT / Firewall</h2>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('crm.mikrotik.nat-rules') }}">
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
            <h3 class="font-semibold text-gray-900">{{ $selectedServer->name }} — {{ count($natRules) }} regras NAT</h3>
            <a href="{{ route('crm.mikrotik.nat-rules', ['server_id' => $selectedServer->id]) }}"
               class="text-sm text-blue-600 hover:underline">Atualizar</a>
        </div>

        @if(count($natRules) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Chain</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Action</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Src Address</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Dst Address</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Protocol</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">To Addresses</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">To Ports</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Comentario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($natRules as $rule)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $rule['chain'] ?? '' }}</td>
                        <td class="px-4 py-3">
                            @php $action = $rule['action'] ?? ''; @endphp
                            @if($action === 'dst-nat')
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-medium">dst-nat</span>
                            @elseif($action === 'src-nat')
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">src-nat</span>
                            @elseif($action === 'masquerade')
                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-medium">masquerade</span>
                            @else
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs font-medium">{{ $action }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $rule['src-address'] ?? '' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $rule['dst-address'] ?? '' }}</td>
                        <td class="px-4 py-3">{{ $rule['protocol'] ?? '' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $rule['to-addresses'] ?? '' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $rule['to-ports'] ?? '' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $rule['comment'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">Nenhuma regra NAT encontrada.</div>
        @endif
    </div>
    @endif
</div>
@endsection
