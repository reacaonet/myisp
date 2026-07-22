@extends('core::layouts.master')

@section('title', $supplier->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h2>
        <div class="flex gap-3">
            <a href="{{ route('crm.suppliers.edit', $supplier) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">Editar</a>
            <a href="{{ route('crm.suppliers.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd>@if($supplier->is_active)<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativo</span>@else<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Inativo</span>@endif</dd>
            </div>
            <div>
                <dt class="text-gray-500">Razao Social</dt>
                <dd>{{ $supplier->company_name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">CNPJ/CPF</dt>
                <dd>{{ $supplier->document ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Telefone</dt>
                <dd>{{ $supplier->phone ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Email</dt>
                <dd>{{ $supplier->email ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Contato</dt>
                <dd>{{ $supplier->contact_person ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Categoria</dt>
                <dd>{{ $supplier->category ?? '-' }}</dd>
            </div>
            @if($supplier->notes)
            <div class="col-span-2">
                <dt class="text-gray-500">Observacoes</dt>
                <dd class="bg-gray-50 rounded-lg p-3">{{ $supplier->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection
