@extends('core::layouts.master')

@section('title', 'Equipamentos')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Equipamentos</h2>
        <a href="{{ route('crm.equipment.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Novo Equipamento</a>
    </div>

    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, modelo, serie, MAC..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipo</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="onu" @selected(request('type')=='onu')>ONU</option>
                    <option value="router" @selected(request('type')=='router')>Roteador</option>
                    <option value="switch" @selected(request('type')=='switch')>Switch</option>
                    <option value="access_point" @selected(request('type')=='access_point')>Access Point</option>
                    <option value="antenna" @selected(request('type')=='antenna')>Antena</option>
                    <option value="cable" @selected(request('type')=='cable')>Cabo</option>
                    <option value="other" @selected(request('type')=='other')>Outro</option>
                </select>
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="available" @selected(request('status')=='available')>Disponivel</option>
                    <option value="allocated" @selected(request('status')=='allocated')>Alocado</option>
                    <option value="maintenance" @selected(request('status')=='maintenance')>Manutencao</option>
                    <option value="defective" @selected(request('status')=='defective')>Defeituoso</option>
                    <option value="retired" @selected(request('status')=='retired')>Aposentado</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Filtrar</button>
            @if(request()->hasAny(['search','type','status']))
                <a href="{{ route('crm.equipment.index') }}" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Limpar</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Nome</th>
                    <th class="px-6 py-4 font-medium">Modelo</th>
                    <th class="px-6 py-4 font-medium">Tipo</th>
                    <th class="px-6 py-4 font-medium">Fabricante</th>
                    <th class="px-6 py-4 font-medium">Serie</th>
                    <th class="px-6 py-4 font-medium">Disponivel</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipment as $e)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">
                        <a href="{{ route('crm.equipment.show', $e) }}" class="text-blue-600 hover:underline">{{ $e->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $e->model ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $e->type_label }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $e->manufacturer?->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $e->serial_number ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $e->available_quantity }}/{{ $e->quantity }}</td>
                    <td class="px-6 py-4">
                        @if($e->status == 'available')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Disponivel</span>
                        @elseif($e->status == 'allocated')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Alocado</span>
                        @elseif($e->status == 'maintenance')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Manutencao</span>
                        @elseif($e->status == 'defective')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Defeituoso</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Aposentado</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('crm.equipment.show', $e) }}" title="Ver" class="p-1.5 rounded hover:bg-green-50 text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('crm.equipment.edit', $e) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('crm.equipment.destroy', $e) }}" onsubmit="return confirm('Remover equipamento {{ $e->name }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-gray-400">Nenhum equipamento cadastrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($equipment->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $equipment->links() }}</div>
    @endif
</div>
@endsection
