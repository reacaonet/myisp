@extends('core::layouts.master')

@section('title', $equipment->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-800">{{ $equipment->name }}</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('crm.equipment.edit', $equipment) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Editar</a>
                <a href="{{ route('crm.equipment.index') }}" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800">Voltar</a>
            </div>
        </div>
        <div class="p-6 grid grid-cols-2 gap-6 text-sm">
            <div>
                <span class="text-gray-500">Tipo</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->type_label }}</p>
            </div>
            <div>
                <span class="text-gray-500">Status</span>
                <p class="mt-1">
                    @if($equipment->status == 'available')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Disponivel</span>
                    @elseif($equipment->status == 'allocated')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Alocado</span>
                    @elseif($equipment->status == 'maintenance')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Manutencao</span>
                    @elseif($equipment->status == 'defective')
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Defeituoso</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Aposentado</span>
                    @endif
                </p>
            </div>
            <div>
                <span class="text-gray-500">Modelo</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->model ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Fabricante</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->manufacturer?->name ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Numero de Serie</span>
                <p class="font-medium text-gray-900 mt-1 font-mono">{{ $equipment->serial_number ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Barcode</span>
                <p class="font-medium text-gray-900 mt-1 font-mono">{{ $equipment->barcode ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">MAC Address</span>
                <p class="font-medium text-gray-900 mt-1 font-mono">{{ $equipment->mac_address ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Endereco IP</span>
                <p class="font-medium text-gray-900 mt-1 font-mono">{{ $equipment->ip_address ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Fornecedor</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->supplier ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Nota Fiscal</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->invoice_number ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Quantidade</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->available_quantity }} disponivel{{ $equipment->available_quantity != 1 ? 'is' : '' }} de {{ $equipment->quantity }}</p>
            </div>
            <div>
                <span class="text-gray-500">Custo / Venda</span>
                <p class="font-medium text-gray-900 mt-1">
                    @if($equipment->cost) R$ {{ number_format($equipment->cost, 2, ',', '.') }} @else - @endif
                    /
                    @if($equipment->sale_price) R$ {{ number_format($equipment->sale_price, 2, ',', '.') }} @else - @endif
                </p>
            </div>
            <div>
                <span class="text-gray-500">Aquisicao</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->purchase_date?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Garantia</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->warranty_until?->format('d/m/Y') ?? '-' }}</p>
            </div>
            @if($equipment->notes)
            <div class="col-span-2">
                <span class="text-gray-500">Observacoes</span>
                <p class="font-medium text-gray-900 mt-1">{{ $equipment->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Alocacoes</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-4 font-medium">Cliente</th>
                        <th class="px-6 py-4 font-medium">Contrato</th>
                        <th class="px-6 py-4 font-medium">Qtd</th>
                        <th class="px-6 py-4 font-medium">Serie Usada</th>
                        <th class="px-6 py-4 font-medium">Data</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipment->assignments as $a)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $a->client?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $a->contract?->id ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $a->quantity }}</td>
                        <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $a->serial_number_used ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $a->assigned_at?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($a->status == 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Ativo</span>
                            @elseif($a->status == 'returned')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Devolvido</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Substituido</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">Nenhuma alocacao registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
