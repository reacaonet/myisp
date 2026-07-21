@php
$classes = [
    'active' => 'bg-green-100 text-green-800',
    'inactive' => 'bg-gray-100 text-gray-600',
    'suspended' => 'bg-yellow-100 text-yellow-800',
    'canceled' => 'bg-red-100 text-red-800',
];
$labels = [
    'active' => 'Ativo',
    'inactive' => 'Inativo',
    'suspended' => 'Suspenso',
    'canceled' => 'Cancelado',
];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes[$status] ?? 'bg-gray-100 text-gray-600' }}">
    {{ $labels[$status] ?? $status }}
</span>
