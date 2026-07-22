@extends('core::layouts.master')

@section('title', 'Editar Equipamento')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Editar Equipamento</h2>
        </div>
        <form method="POST" action="{{ route('crm.equipment.update', $equipment) }}" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $equipment->name) }}" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="onu" @selected(old('type', $equipment->type)=='onu')>ONU</option>
                        <option value="router" @selected(old('type', $equipment->type)=='router')>Roteador</option>
                        <option value="switch" @selected(old('type', $equipment->type)=='switch')>Switch</option>
                        <option value="access_point" @selected(old('type', $equipment->type)=='access_point')>Access Point</option>
                        <option value="antenna" @selected(old('type', $equipment->type)=='antenna')>Antena</option>
                        <option value="cable" @selected(old('type', $equipment->type)=='cable')>Cabo</option>
                        <option value="other" @selected(old('type', $equipment->type)=='other')>Outro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                    <input type="text" name="model" value="{{ old('model', $equipment->model) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fabricante</label>
                    <select name="manufacturer_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Selecione...</option>
                        @foreach($manufacturers as $m)
                            <option value="{{ $m->id }}" @selected(old('manufacturer_id', $equipment->manufacturer_id)==$m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                    <input type="text" name="supplier" value="{{ old('supplier', $equipment->supplier) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Numero de Serie</label>
                    <input type="text" name="serial_number" value="{{ old('serial_number', $equipment->serial_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $equipment->barcode) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">MAC Address</label>
                    <input type="text" name="mac_address" value="{{ old('mac_address', $equipment->mac_address) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereco IP</label>
                    <input type="text" name="ip_address" value="{{ old('ip_address', $equipment->ip_address) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nota Fiscal</label>
                    <input type="text" name="invoice_number" value="{{ old('invoice_number', $equipment->invoice_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade *</label>
                    <input type="number" name="quantity" value="{{ old('quantity', $equipment->quantity) }}" min="1" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Custo (R$)</label>
                    <input type="number" name="cost" value="{{ old('cost', $equipment->cost) }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preco de Venda (R$)</label>
                    <input type="number" name="sale_price" value="{{ old('sale_price', $equipment->sale_price) }}" step="0.01" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Aquisicao</label>
                    <input type="date" name="purchase_date" value="{{ old('purchase_date', optional($equipment->purchase_date)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Garantia ate</label>
                    <input type="date" name="warranty_until" value="{{ old('warranty_until', optional($equipment->warranty_until)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="available" @selected(old('status', $equipment->status)=='available')>Disponivel</option>
                        <option value="allocated" @selected(old('status', $equipment->status)=='allocated')>Alocado</option>
                        <option value="maintenance" @selected(old('status', $equipment->status)=='maintenance')>Manutencao</option>
                        <option value="defective" @selected(old('status', $equipment->status)=='defective')>Defeituoso</option>
                        <option value="retired" @selected(old('status', $equipment->status)=='retired')>Aposentado</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observacoes</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notes', $equipment->notes) }}</textarea>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('crm.equipment.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Salvar</button>
            </div>
        </form>
    </div>
</div>
@endsection
