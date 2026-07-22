@extends('core::layouts.master')

@section('title', 'Detalhes do Cupom')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Cupom {{ $coupon->code }}</h2>
        <div class="flex gap-3">
            <a href="{{ route('crm.hotspot-coupons.edit', $coupon) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">Editar</a>
            <a href="{{ route('crm.hotspot-coupons.index') }}" class="text-sm text-blue-600 hover:underline">Voltar</a>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Codigo</dt>
                <dd class="font-mono font-bold text-lg">{{ $coupon->code }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd>@if($coupon->status === 'active')<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativo</span>@elseif($coupon->status === 'used')<span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Usado</span>@else<span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Expirado</span>@endif</dd>
            </div>
            <div>
                <dt class="text-gray-500">Perfil</dt>
                <dd>{{ $coupon->profile ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Duracao</dt>
                <dd>{{ $coupon->duration_hours }} horas</dd>
            </div>
            <div>
                <dt class="text-gray-500">Valor</dt>
                <dd class="font-bold">R$ {{ number_format($coupon->price, 2, ',', '.') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Servidor</dt>
                <dd>{{ $coupon->server?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Criado em</dt>
                <dd>{{ $coupon->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Expira em</dt>
                <dd>{{ $coupon->expires_at?->format('d/m/Y H:i') ?? '-' }}</dd>
            </div>
            @if($coupon->used_at)
            <div>
                <dt class="text-gray-500">Usado em</dt>
                <dd>{{ $coupon->used_at->format('d/m/Y H:i') }}</dd>
            </div>
            @endif
            @if($coupon->client)
            <div>
                <dt class="text-gray-500">Cliente</dt>
                <dd>{{ $coupon->client->name }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection
