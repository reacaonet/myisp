@extends('infra::layouts.master')

@section('title', 'Provisionamento')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Provisionamento MikroTik</h2>
        <a href="{{ route('infra.provisioning.create') }}" title="Novo Usuario" class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg></a>
    </div>

    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Login do usuario..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="pppoe" @selected(request('type')=='pppoe')>PPPoE</option>
                    <option value="hotspot" @selected(request('type')=='hotspot')>Hotspot</option>
                </select>
            </div>
            <div class="w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Servidor</label>
                <select name="server_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    @foreach($servers as $s)
                        <option value="{{ $s->id }}" @selected(request('server_id')==$s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filtrar</button>
            @if(request()->hasAny(['search','type','server_id']))
                <a href="{{ route('infra.provisioning.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Limpar</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Login</th>
                    <th class="px-6 py-4 font-medium">Tipo</th>
                    <th class="px-6 py-4 font-medium">Servidor</th>
                    <th class="px-6 py-4 font-medium">Cliente</th>
                    <th class="px-6 py-4 font-medium">Acao</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium">Data</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900 font-mono">{{ $r->login }}</td>
                    <td class="px-6 py-4 text-gray-600">
                        @if($r->type == 'pppoe')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">PPPoE</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Hotspot</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $r->mikrotikServer?->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $r->client?->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ ucfirst($r->action) }}</td>
                    <td class="px-6 py-4">
                        @if($r->success)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Sucesso</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Erro</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-600 text-xs">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                        @if($r->action === 'add' && $r->success)
                            <form method="POST" action="{{ route('infra.provisioning.block', $r) }}" class="inline">
                                @csrf
                                <button type="submit" title="Bloquear" class="p-1.5 rounded hover:bg-yellow-50 text-yellow-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></button>
                            </form>
                            <form method="POST" action="{{ route('infra.provisioning.destroy', $r) }}" onsubmit="return confirm('Remover usuario {{ $r->login }} do MikroTik?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Remover" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">Nenhum registro de provisionamento.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $records->links() }}</div>
    @endif
</div>
@endsection
