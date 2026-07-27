@extends('infra::layouts.master')

@section('title', 'Cupons Gerados em Lote')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ $coupons->count() }} Cupons Gerados</h2>
        <a href="{{ route('infra.hotspot-coupons.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <p class="text-sm text-gray-600">Imprima esta pagina para distribuir os cupons.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($coupons as $coupon)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-xs text-gray-500 mb-2">Hotspot Access</p>
            <p class="font-mono font-bold text-lg mb-2">{{ $coupon->code }}</p>
            <div class="border-t border-gray-200 pt-2 mt-2">
                <p class="text-xs text-gray-500">Perfil: {{ $coupon->profile ?? 'default' }}</p>
                <p class="text-xs text-gray-500">Duracao: {{ $coupon->duration_hours }}h</p>
                @if($coupon->price > 0)
                <p class="text-xs text-gray-500">Valor: R$ {{ number_format($coupon->price, 2, ',', '.') }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
