@extends('core::layouts.master')

@section('title', 'Detalhe da Movimentação')

@section('content')
<div class="max-w-xl">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Movimentação #{{ $movement->id }}</h2>
        <a href="{{ route('crm.stock-movements.index') }}" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm">Voltar</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Data/Hora</p>
                <p class="font-medium">{{ $movement->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tipo</p>
                @if($movement->type === 'entry')
                    <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">Entrada</span>
                @elseif($movement->type === 'exit')
                    <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-800">Saída</span>
                @elseif($movement->type === 'return')
                    <span class="px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Devolução</span>
                @elseif($movement->type === 'transfer')
                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800">Transferência</span>
                @elseif($movement->type === 'installation')
                    <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-800">Instalação</span>
                @endif
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Item</p>
                <p class="font-medium">{{ $movement->item->name }} ({{ $movement->item->sku }})</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Quantidade</p>
                <p class="text-2xl font-bold">{{ $movement->quantity }}</p>
            </div>
        </div>
        <div>
            <p class="text-sm text-gray-500">Local</p>
            <p class="font-medium">{{ $movement->location->name }} ({{ $movement->location->type === 'deposit' ? 'Depósito' : 'Técnico' }})</p>
        </div>
        @if($movement->reference)
        <div>
            <p class="text-sm text-gray-500">Referência</p>
            <p class="font-medium">{{ $movement->reference }}</p>
        </div>
        @endif
        @if($movement->notes)
        <div>
            <p class="text-sm text-gray-500">Observações</p>
            <p class="font-medium">{{ $movement->notes }}</p>
        </div>
        @endif
        <div>
            <p class="text-sm text-gray-500">Responsável</p>
            <p class="font-medium">{{ $movement->performer->name ?? '-' }}</p>
        </div>
    </div>
</div>
@endsection
