@extends('core::layouts.master')

@section('title', 'Lista de Enderecos (Address List)')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Firewall Address List</h2>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('crm.mikrotik.address-list') }}">
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
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Lista</label>
                    <input type="text" name="list_name" value="{{ old('list_name', $listName) }}" placeholder="ex: blocked_sites"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Carregar
                </button>
            </div>
        </form>
    </div>

    @if($selectedServer)
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">{{ $selectedServer->name }} — Lista: {{ $listName }} ({{ count($addressList) }} itens)</h3>
            <a href="{{ route('crm.mikrotik.address-list', ['server_id' => $selectedServer->id, 'list_name' => $listName]) }}"
               class="text-sm text-blue-600 hover:underline">Atualizar</a>
        </div>

        @if(count($addressList) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">IP / Endereco</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Lista</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500">Comentario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($addressList as $entry)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $entry['address'] ?? '' }}</td>
                        <td class="px-4 py-3">{{ $entry['list'] ?? '' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $entry['comment'] ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-gray-500">Nenhum endereco encontrado nesta lista.</div>
        @endif
    </div>
    @endif
</div>
@endsection
