@extends('core::layouts.master')

@section('title', "OS {$order->codigo}")

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">OS {{ $order->codigo }}</h2>
                <p class="text-sm text-gray-500">Aberta em {{ $order->emissao?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div class="flex items-center gap-1">
                @if($order->situacao === 'O')
                <form method="POST" action="{{ route('crm.service-orders.start', $order) }}" class="inline">
                    @csrf
                    <button type="submit" title="Iniciar OS" class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
                </form>
                @endif

                @if($order->situacao === 'A')
                <form method="POST" action="{{ route('crm.service-orders.complete', $order) }}" class="inline">
                    @csrf
                    <button type="submit" title="Concluir OS" class="p-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button>
                </form>
                @endif

                <a href="{{ route('crm.service-orders.edit', $order) }}" title="Editar" class="p-2 rounded-lg text-blue-600 border border-blue-200 hover:bg-blue-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Cliente</h3>
                <p class="font-medium text-gray-900">{{ $order->client->name ?? 'N/D' }}</p>
                <p class="text-sm text-gray-500">{{ $order->client->document ?? '' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Detalhes</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Situacao</dt>
                        <dd><span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $order->situacao === 'O' ? 'bg-blue-100 text-blue-700' : ($order->situacao === 'A' ? 'bg-yellow-100 text-yellow-700' : ($order->situacao === 'F' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700')) }}">{{ $order->situacao === 'O' ? 'Aberta' : ($order->situacao === 'A' ? 'Em Andamento' : ($order->situacao === 'F' ? 'Finalizada' : $order->situacao)) }}</span></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Servico</dt>
                        <dd class="text-gray-900">{{ $order->servico ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tecnico</dt>
                        <dd class="text-gray-900">{{ $order->technician->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Valor</dt>
                        <dd class="text-gray-900">R$ {{ number_format($order->preco, 2, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Timeline --}}
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4">Progresso</h3>
            <div class="flex items-center justify-between">
                <div class="flex flex-col items-center flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $order->emissao ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <p class="text-xs font-medium text-gray-600 mt-2">Abertura</p>
                    <p class="text-xs text-gray-400">{{ $order->emissao?->format('d/m') ?? '-' }}</p>
                </div>
                <div class="flex-1 h-0.5 {{ $order->situacao !== 'O' ? 'bg-blue-500' : 'bg-gray-200' }}"></div>
                <div class="flex flex-col items-center flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ in_array($order->situacao, ['A', 'F']) ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-xs font-medium text-gray-600 mt-2">Em Andamento</p>
                    @if($order->saida)
                    <p class="text-xs text-gray-400">{{ $order->saida->format('d/m') }}</p>
                    @endif
                </div>
                <div class="flex-1 h-0.5 {{ $order->situacao === 'F' ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                <div class="flex flex-col items-center flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $order->situacao === 'F' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-xs font-medium text-gray-600 mt-2">Finalizada</p>
                    @if($order->situacao === 'F')
                    <p class="text-xs text-gray-400">{{ $order->updated_at->format('d/m') }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if($order->problema)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Problema</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $order->problema }}</p>
        </div>
        @endif

        @if($order->diagnostico)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Diagnostico</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $order->diagnostico }}</p>
        </div>
        @endif

        @if($order->solucao)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Solucao</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $order->solucao }}</p>
        </div>
        @endif

        @if(!$order->technician_id)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Atribuir Tecnico</h3>
            <form method="POST" action="{{ route('crm.service-orders.assign', $order) }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Selecione o Tecnico</label>
                    <select name="technician_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Selecione...</option>
                        @php
                        $technicians = \Modules\CRM\Models\Technician::where('is_active', true)->orderBy('name')->get();
                        @endphp
                        @foreach($technicians as $tech)
                        <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" title="Atribuir" class="p-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg></button>
            </form>
        </div>
        @endif
    </div>

    <div class="mt-4 flex justify-end">
        <form method="POST" action="{{ route('crm.service-orders.destroy', $order) }}" onsubmit="return confirm('Remover OS {{ $order->codigo }}?')">
            @csrf @method('DELETE')
            <button type="submit" title="Remover OS" class="p-2 rounded-lg text-red-600 border border-red-200 hover:bg-red-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
        </form>
    </div>
</div>
@endsection
