@extends('core::layouts.master')

@section('title', 'Cupons Hotspot')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Cupons Hotspot</h2>
        <div class="flex gap-3">
            <a href="{{ route('crm.hotspot-coupons.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">+ Novo Cupom</a>
            <form method="POST" action="{{ route('crm.hotspot-coupons.generate-batch') }}" class="inline">
                @csrf
                <input type="hidden" name="profile" value="default">
                <input type="hidden" name="duration_hours" value="24">
                <input type="hidden" name="price" value="0">
                <input type="hidden" name="quantity" value="10">
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">Gerar Lote (10)</button>
            </form>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex gap-4 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">Todos</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Usado</option>
                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirado</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
    </form>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Total</p>
            <p class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl shadow-sm border border-green-200 p-4">
            <p class="text-xs text-green-600 uppercase font-medium">Ativos</p>
            <p class="text-xl font-bold text-green-700">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-200 p-4">
            <p class="text-xs text-blue-600 uppercase font-medium">Usados</p>
            <p class="text-xl font-bold text-blue-700">{{ $stats['used'] }}</p>
        </div>
        <div class="bg-gray-50 rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase">Expirados</p>
            <p class="text-xl font-bold text-gray-600">{{ $stats['expired'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Codigo</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Perfil</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Duracao</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Valor</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Servidor</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Expira</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($coupons as $coupon)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs font-medium">{{ $coupon->code }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $coupon->profile ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">{{ $coupon->duration_hours }}h</td>
                    <td class="px-4 py-3 text-right">R$ {{ number_format($coupon->price, 2, ',', '.') }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $coupon->server?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        @if($coupon->status === 'active')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Ativo</span>
                        @elseif($coupon->status === 'used')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Usado</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-500">Expirado</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $coupon->expires_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('crm.hotspot-coupons.show', $coupon) }}" class="text-blue-600 hover:text-blue-800 text-xs">Ver</a>
                            <a href="{{ route('crm.hotspot-coupons.edit', $coupon) }}" class="text-blue-600 hover:text-blue-800 text-xs">Editar</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Nenhum cupom encontrado</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $coupons->withQueryString()->links() }}</div>
</div>
@endsection
