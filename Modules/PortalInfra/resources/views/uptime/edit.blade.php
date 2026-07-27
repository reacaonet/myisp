@extends('infra::layouts.master')

@section('title', 'Editar Monitor')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Editar Monitor</h2>
        <a href="{{ route('infra.uptime.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>
    <form method="POST" action="{{ route('infra.uptime.update', $monitor) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf @method('PUT')
        @include('infra::uptime._form')
    </form>
</div>
@endsection
