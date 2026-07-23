@extends('core::layouts.master')

@section('title', 'Detalhes do Cupom')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Cupom {{ $coupon->code }}</h2>
        <div class="flex items-center gap-1">
            <a href="{{ route('crm.hotspot-coupons.edit', $coupon) }}" title="Editar" class="p-2 rounded-lg text-blue-600 border border-blue-200 hover:bg-blue-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            <a href="{{ route('crm.hotspot-coupons.index') }}" title="Voltar" class="p-2 rounded-lg text-gray-600 border border-gray-300 hover:bg-gray-50"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg></a>
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
