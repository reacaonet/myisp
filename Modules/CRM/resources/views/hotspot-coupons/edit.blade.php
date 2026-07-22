@extends('core::layouts.master')

@section('title', 'Editar Cupom')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Editar Cupom</h2>
        <a href="{{ route('crm.hotspot-coupons.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>
    <form method="POST" action="{{ route('crm.hotspot-coupons.update', $coupon) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf @method('PUT')
        @include('crm::hotspot-coupons._form')
    </form>
</div>
@endsection
