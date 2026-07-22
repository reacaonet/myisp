@extends('core::layouts.master')

@section('title', 'Novo Monitor Uptime')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Novo Monitor Uptime</h2>
        <a href="{{ route('crm.uptime.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>
    <form method="POST" action="{{ route('crm.uptime.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        @include('crm::uptime._form')
    </form>
</div>
@endsection
