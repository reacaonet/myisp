@php
    $classes = match($status) {
        'paid' => 'bg-green-100 text-green-700',
        'overdue' => 'bg-red-100 text-red-700',
        'canceled' => 'bg-gray-100 text-gray-500',
        'pending' => 'bg-yellow-100 text-yellow-700',
        default => 'bg-gray-100 text-gray-600',
    };
    $labels = ['paid' => 'Pago', 'overdue' => 'Vencido', 'canceled' => 'Cancelado', 'pending' => 'Pendente'];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ $labels[$status] ?? $status }}
</span>
