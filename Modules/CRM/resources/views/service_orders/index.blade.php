@extends('core::layouts.master')

@section('title', 'Ordens de Servico')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Ordens de Servico</h2>
        <div class="flex gap-3">
            <form method="GET" class="flex gap-2">
                <select name="situacao" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <option value="O" @selected(request('situacao') == 'O')>Orcamento</option>
                    <option value="I" @selected(request('situacao') == 'I')>Instalado</option>
                    <option value="NI" @selected(request('situacao') == 'NI')>Instalacao</option>
                    <option value="M" @selected(request('situacao') == 'M')>Manutencao</option>
                    <option value="R" @selected(request('situacao') == 'R')>Recuperacao</option>
                    <option value="A" @selected(request('situacao') == 'A')>Aprovado</option>
                    <option value="CS" @selected(request('situacao') == 'CS')>Cancelamento</option>
                    <option value="C" @selected(request('situacao') == 'C')>Cancelada</option>
                </select>
                <input type="text" name="search" placeholder="Buscar..." value="{{ request('search') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm w-48">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Buscar</button>
            </form>
            <a href="{{ route('crm.service-orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova OS
            </a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-4 font-medium">Codigo</th>
                    <th class="px-6 py-4 font-medium">Cliente</th>
                    <th class="px-6 py-4 font-medium">Servico</th>
                    <th class="px-6 py-4 font-medium">Situacao</th>
                    <th class="px-6 py-4 font-medium">Tecnico</th>
                    <th class="px-6 py-4 font-medium">Data</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $order->codigo }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('crm.service-orders.show', $order) }}" class="text-blue-600 hover:underline font-medium">{{ $order->client->name ?? 'N/D' }}</a>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $order->servico ?? '-' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $sLabels = ['O'=>'Orcamento','I'=>'Instalado','NI'=>'Instalacao','M'=>'Manutencao','R'=>'Recuperacao','A'=>'Aprovado','CS'=>'Cancelamento','C'=>'Cancelada'];
                            $sColors = ['O'=>'bg-yellow-100 text-yellow-700','I'=>'bg-green-100 text-green-700','NI'=>'bg-blue-100 text-blue-700','M'=>'bg-purple-100 text-purple-700','R'=>'bg-indigo-100 text-indigo-700','A'=>'bg-teal-100 text-teal-700','CS'=>'bg-red-100 text-red-700','C'=>'bg-gray-100 text-gray-500'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sColors[$order->situacao] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $sLabels[$order->situacao] ?? $order->situacao }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $order->technician->name ?? '-' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $order->emissao?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('crm.service-orders.show', $order) }}" title="Detalhes" class="p-1.5 rounded hover:bg-gray-100 text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('crm.service-orders.edit', $order) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('crm.service-orders.destroy', $order) }}" onsubmit="return confirm('Remover OS {{ $order->codigo }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Nenhuma ordem de servico encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="p-6 border-t border-gray-200">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
