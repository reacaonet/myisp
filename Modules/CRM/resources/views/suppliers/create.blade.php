@extends('core::layouts.master')

@section('title', 'Novo Fornecedor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Novo Fornecedor</h2>
        <a href="{{ route('crm.suppliers.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>
    <form method="POST" action="{{ route('crm.suppliers.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        @include('crm::suppliers._form')
    </form>
</div>
@endsection
