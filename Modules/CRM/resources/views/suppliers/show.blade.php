@extends('core::layouts.master')

@section('title', $supplier->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ $supplier->name }}</h2>
        <div class="flex items-center gap-1">
            <a href="{{ route('crm.suppliers.edit', $supplier) }}" title="Editar" class="p-2 rounded-lg text-blue-600 border border-blue-200 hover:bg-blue-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            <a href="{{ route('crm.suppliers.index') }}" title="Voltar" class="p-2 rounded-lg text-gray-600 border border-gray-300 hover:bg-gray-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></a>
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
