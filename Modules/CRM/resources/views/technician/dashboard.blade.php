@extends('crm::technician.layouts.master')

@section('title', 'Dashboard')

@php
    $todayOrders = $serviceOrders->filter(fn($os) => $os->data_agendamento && $os->data_agendamento->isToday())
        ->sortBy(fn($os) => $os->hora_agendamento ?: '99:99');

    $inProgressOrders = $serviceOrders->filter(fn($os) => $os->situacao === 'A');

    // `situacao` e a etapa do atendimento (O/A/F) e `status` e o estado do
    // registro (active/closed/canceled). Os dois canais aparecem lado a lado na
    // tela antiga, sem rotulo, e era impossible saber qual olhar. Aqui cada um
    // tem nome proprio.
    $etapaLabels = ['O' => 'Aberta', 'A' => 'Em andamento', 'F' => 'Finalizada'];
    $etapaClasses = [
        'O' => 'bg-blue-100 text-blue-700',
        'A' => 'bg-amber-100 text-amber-700',
        'F' => 'bg-green-100 text-green-700',
    ];
    $statusLabels = ['active' => 'Ativo', 'closed' => 'Encerrado', 'canceled' => 'Cancelado'];
    $statusClasses = [
        'active' => 'bg-gray-100 text-gray-700',
        'closed' => 'bg-green-100 text-green-700',
        'canceled' => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')
{{-- Cabecalho enxuto: nome e contato numa linha, sem caixa vazia ocupando a
     tela toda. A data ja aparece no relogio do topo. --}}
<div class="mb-5">
    <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Ola, {{ \Illuminate\Support\Str::before($technician->name, ' ') }}</h2>
    <p class="mt-1 text-sm text-gray-500">
        {{ $technician->cargo ?? 'Tecnico' }}
        @if($technician->email)<span class="hidden sm:inline"> &middot; {{ $technician->email }}</span>@endif
        @if($technician->cellphone ?? $technician->phone)<span class="hidden sm:inline"> &middot; {{ $technician->cellphone ?? $technician->phone }}</span>@endif
    </p>
</div>

{{-- Prioridade: o que o tecnico precisa fazer agora fica acima de tudo, com
     acao direta. A versao antiga escondia isso abaixo de quatro cards iguais e
     de um grid de atalhos que so repetia a sidebar. --}}
@if($inProgressOrders->isNotEmpty())
<div class="mb-5 overflow-hidden rounded-xl border border-amber-300 bg-white shadow-sm">
    <div class="flex items-center gap-3 border-b border-amber-200 bg-amber-50 px-4 py-3 sm:px-5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100">
            <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="font-semibold text-amber-900">Em andamento agora</h3>
            <p class="text-xs text-amber-700">{{ $inProgressOrders->count() === 1 ? '1 ordem em execucao' : $inProgressOrders->count().' ordens em execucao' }}</p>

        </div>
    </div>
    <ul class="divide-y divide-gray-100">
        @foreach($inProgressOrders as $os)
        <li>
            <a href="{{ route('technician.portal.service-orders.show', $os) }}" class="flex items-center gap-3 px-4 py-3.5 transition hover:bg-amber-50/60 sm:px-5">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-900">
                        {{ $os->codigo }}
                        <span class="font-normal text-gray-500">&middot; {{ $os->servico ?? $os->tipo_servico ?? 'Servico' }}</span>
                    </p>
                    <p class="mt-0.5 truncate text-sm text-gray-500">{{ $os->client?->name ?? 'Cliente nao informado' }}</p>
                </div>
                <span class="hidden shrink-0 rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white transition hover:bg-blue-700 sm:inline-flex">Continuar</span>
                <svg class="h-5 w-5 shrink-0 text-gray-400 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif

{{-- Os tres filtros apontam para `situacao`, nunca para `status`: o enum de
     registro (active/closed/canceled) nao tem valor "aberta" nem "em
     andamento", entao filtrar por ele devolvia lista vazia. Os atalhos antigos
     faziam exatamente isso. --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <a href="{{ route('technician.portal.service-orders') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Minhas OS</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total_assigned'] }}</p>
    </a>
    <a href="{{ route('technician.portal.service-orders', ['situacao' => 'O']) }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Em aberto</p>
        <p class="mt-1 text-2xl font-bold text-blue-600">{{ $stats['open'] }}</p>
    </a>
    <a href="{{ route('technician.portal.service-orders', ['situacao' => 'A']) }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-amber-300 hover:shadow">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Em andamento</p>
        <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['in_progress'] }}</p>
    </a>
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Concluidas hoje</p>
        <p class="mt-1 text-2xl font-bold text-green-600">{{ $stats['completed_today'] }}</p>
    </div>
</div>

@if($todayOrders->isNotEmpty())
<div class="mb-5 rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5">
        <h3 class="font-semibold text-gray-800">Agenda de hoje</h3>
        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">{{ $todayOrders->count() }}</span>
    </div>
    <ul class="divide-y divide-gray-100">
        @foreach($todayOrders as $os)
        <li>
            <a href="{{ route('technician.portal.service-orders.show', $os) }}" class="flex items-center gap-4 px-4 py-3.5 transition hover:bg-gray-50 sm:px-5">
                <div class="w-16 shrink-0 text-center">
                    <p class="text-sm font-bold text-gray-900">{{ $os->hora_agendamento ?? '--:--' }}</p>
                </div>
                <div class="min-w-0 flex-1 border-l border-gray-100 pl-4">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ $os->codigo }} &middot; {{ $os->servico ?? $os->tipo_servico ?? 'Servico' }}</p>
                    <p class="mt-0.5 truncate text-sm text-gray-500">{{ $os->client?->name ?? 'Cliente nao informado' }}</p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $etapaClasses[$os->situacao] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $etapaLabels[$os->situacao] ?? 'Finalizada' }}
                </span>
            </a>
        </li>
        @endforeach
    </ul>
</div>
@endif

<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5">
        <h3 class="font-semibold text-gray-800">Todas as ordens de servico</h3>
        <a href="{{ route('technician.portal.service-orders') }}" class="shrink-0 text-sm font-medium text-blue-600 hover:underline">Ver lista</a>
    </div>

    @if($serviceOrders->isEmpty())
        <div class="px-4 py-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <p class="mt-3 text-sm font-medium text-gray-700">Nenhuma OS atribuida</p>
            <p class="mx-auto mt-1 max-w-xs text-sm text-gray-500">Quando o atendimento designar uma ordem de servico, ela aparece aqui.</p>
        </div>
    @else
        {{-- Mobile: cards. Uma tabela de 7 colunas virava scroll horizontal e o
             técnico lia codigo e cliente fora da tela. --}}
        <ul class="divide-y divide-gray-100 md:hidden">
            @foreach($serviceOrders as $os)
            <li>
                <a href="{{ route('technician.portal.service-orders.show', $os) }}" class="block px-4 py-4 transition active:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <p class="font-semibold text-gray-900">{{ $os->codigo }}</p>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ $etapaClasses[$os->situacao] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $etapaLabels[$os->situacao] ?? 'Finalizada' }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-700">{{ $os->servico ?? $os->tipo_servico ?? 'Servico' }}</p>
                    <p class="mt-0.5 truncate text-sm text-gray-500">{{ $os->client?->name ?? 'Cliente nao informado' }}</p>
                    <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
                        @if($os->data_agendamento)
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $os->data_agendamento->format('d/m/Y') }}@if($os->hora_agendamento) {{ $os->hora_agendamento }}@endif
                        </span>
                        @endif
                        <span class="inline-flex items-center gap-1 rounded px-1.5 py-0.5 {{ $statusClasses[$os->status] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $statusLabels[$os->status] ?? ucfirst((string) $os->status) }}
                        </span>
                    </div>
                </a>
            </li>
            @endforeach
        </ul>

        {{-- Desktop: tabela --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-5 py-3">Codigo</th>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">Servico</th>
                        <th class="px-5 py-3">Agendamento</th>
                        <th class="px-5 py-3">Etapa</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Acao</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($serviceOrders as $os)
                    <tr class="transition hover:bg-gray-50">
                        <td class="whitespace-nowrap px-5 py-3 text-sm font-semibold text-gray-900">{{ $os->codigo }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600">{{ $os->client?->name ?? '-' }}</td>
                        <td class="px-5 py-3 text-sm text-gray-600">{{ $os->servico ?? $os->tipo_servico ?? '-' }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-600">
                            {{ $os->data_agendamento?->format('d/m/Y') ?? '-' }}
                            @if($os->hora_agendamento)<span class="text-gray-400">{{ $os->hora_agendamento }}</span>@endif
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-block rounded-full px-2.5 py-1 text-xs font-medium {{ $etapaClasses[$os->situacao] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $etapaLabels[$os->situacao] ?? 'Finalizada' }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-block rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses[$os->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$os->status] ?? ucfirst((string) $os->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('technician.portal.service-orders.show', $os) }}" title="Ver OS" aria-label="Ver OS {{ $os->codigo }}" class="inline-flex rounded-lg p-2 text-blue-600 transition hover:bg-blue-50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
