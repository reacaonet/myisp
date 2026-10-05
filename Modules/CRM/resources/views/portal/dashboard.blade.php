@extends('crm::portal.layouts.master')

@section('title', 'Dashboard')

@php
    $pendingInvoices = $stats['pending_invoices'];
    $hasPending = $pendingInvoices > 0;

    // A fatura mais recente nao e necessariamente a que esta em aberto: o
    // cliente pode ter pago a ultima e ainda estar devendo uma anterior. So
    // oferecemos os botoes de pagamento quando a ultima fatura tambem esta
    // pendente, senao a tela mostraria "vencimento" de uma fatura ja paga.
    $lastInvoice = $stats['last_invoice'];
    $lastInvoiceOpen = $hasPending && $lastInvoice && $lastInvoice->status !== 'paid';

    // `status` do contrato/OS e o enum active/closed/canceled e `situacao` e a
    // etapa O/A/F. O badge antigo comparava `status == 'open'`, valor que nunca
    // existe nesse enum: toda OS caia no ramo amarelo e exibia "Active".
    $osLabels = [
        'O' => ['Aberta', 'bg-blue-100 text-blue-700'],
        'A' => ['Em andamento', 'bg-amber-100 text-amber-700'],
        'F' => ['Concluida', 'bg-green-100 text-green-700'],
    ];
    $openServiceOrders = $client->serviceOrders->whereNotIn('status', ['closed', 'canceled']);
@endphp

@section('content')
{{-- Cabecalho enxuto, sem caixa vazia ocupando a tela toda. --}}
<div class="mb-5">
    <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Ola, {{ \Illuminate\Support\Str::before($client->name, ' ') }}</h2>
    <p class="mt-1 text-sm text-gray-500">
        {{ $client->document }}
        @if($client->email)<span class="hidden sm:inline"> &middot; {{ $client->email }}</span>@endif
        @if($client->cellphone ?? $client->phone)<span class="hidden sm:inline"> &middot; {{ $client->cellphone ?? $client->phone }}</span>@endif
    </p>
</div>

{{-- Prioridade: a situacao financeira vem primeiro, e o estado "em dia" e
     explicito. Antes o card da ultima fatura so existia quando havia divida, e
     a segunda coluna do grid ficava estreita sem nenhum aviso. --}}
@if($hasPending)
    <div class="mb-5 overflow-hidden rounded-xl border {{ $lastInvoice && $lastInvoice->status === 'overdue' ? 'border-red-300' : 'border-amber-300' }} bg-white shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-gray-100 px-4 py-4 sm:px-5">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Em aberto</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 sm:text-4xl">
                    R$ {{ number_format($stats['pending_amount'], 2, ',', '.') }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $pendingInvoices }} {{ $pendingInvoices === 1 ? 'fatura pendente' : 'faturas pendentes' }}
                </p>
            </div>
            @if($lastInvoice && $lastInvoice->status === 'overdue')
                <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-red-700">
                    {{ $lastInvoiceOpen ? 'Vencida' : 'Com pendencia vencida' }}
                </span>
            @endif
        </div>

        @if($lastInvoiceOpen)
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5">
                <div class="min-w-0 text-sm">
                    <span class="font-semibold text-gray-900">{{ $lastInvoice->invoice_number }}</span>
                    <span class="text-gray-500"> &middot; vencimento {{ $lastInvoice->due_date->format('d/m/Y') }}</span>
                    @if($lastInvoice->due_date->isPast())
                        <span class="text-red-600"> &middot; {{ $lastInvoice->due_date->diffForHumans() }}</span>
                    @endif
                </div>
                <div class="flex w-full gap-2 sm:w-auto">
                    <a href="{{ route('crm.portal.invoices.pay', $lastInvoice) }}"
                       class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 sm:flex-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Pagar
                    </a>
                    <a href="{{ route('crm.portal.invoices.boleto', $lastInvoice) }}"
                       class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 sm:flex-none">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Boleto
                    </a>
                </div>
            </div>
        @endif

        <div class="px-4 py-3 text-center sm:px-5">
            <a href="{{ route('crm.portal.invoices') }}" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline">
                Ver todas as faturas
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
@else
    <div class="mb-5 flex items-start gap-4 rounded-xl border border-green-200 bg-white px-4 py-4 shadow-sm sm:px-5">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100">
            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-gray-900">Voce esta em dia</p>
            <p class="mt-0.5 text-sm text-gray-500">
                Nenhuma fatura em aberto.
                @if($stats['paid_invoices'] > 0)
                    {{ $stats['paid_invoices'] }} {{ $stats['paid_invoices'] === 1 ? 'fatura paga' : 'faturas pagas' }} no historico.
                @endif
            </p>
        </div>
        <a href="{{ route('crm.portal.invoices') }}" class="hidden shrink-0 text-sm font-medium text-blue-600 hover:underline sm:block">Ver faturas</a>
    </div>
@endif

{{-- Exatamente 4 tiles: eram 5 num grid de 4 colunas e o quinto ficava sozinho
     numa linha, sem contexto. --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
    <a href="{{ route('crm.portal.contracts') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-purple-300 hover:shadow">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Contratos</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total_contracts'] }}</p>
        <p class="mt-0.5 text-xs text-gray-400">{{ $stats['active_contracts'] }} ativos</p>
    </a>
    <a href="{{ route('crm.portal.invoices') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-amber-300 hover:shadow">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Faturas pendentes</p>
        <p class="mt-1 text-2xl font-bold {{ $hasPending ? 'text-amber-600' : 'text-gray-400' }}">{{ $pendingInvoices }}</p>
        <p class="mt-0.5 text-xs text-gray-400">R$ {{ number_format($stats['pending_amount'], 2, ',', '.') }}</p>
    </a>
    <a href="{{ route('crm.portal.service-orders') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-orange-300 hover:shadow">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Ordens de servico</p>
        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $client->serviceOrders->count() }}</p>
        <p class="mt-0.5 text-xs text-gray-400">{{ $stats['open_os'] }} em aberto</p>
    </a>
    <a href="{{ route('crm.portal.tickets') }}" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-red-300 hover:shadow">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Chamados abertos</p>
        <p class="mt-1 text-2xl font-bold {{ $stats['open_tickets'] > 0 ? 'text-red-600' : 'text-gray-400' }}">{{ $stats['open_tickets'] }}</p>
        <p class="mt-0.5 text-xs text-gray-400">
            {{ $stats['open_tickets'] === 1 ? '1 em andamento' : $stats['open_tickets'].' em andamento' }}
        </p>
    </a>
</div>

{{-- Nao existe rota de detalhe de OS no portal do cliente (so o indice), entao
     os itens sao informativos e o acesso fica no link do cabecalho. --}}
<div class="rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5">
        <h3 class="font-semibold text-gray-800">Ordens de servico</h3>
        <a href="{{ route('crm.portal.service-orders') }}" class="shrink-0 text-sm font-medium text-blue-600 hover:underline">Ver todas</a>
    </div>

    @if($openServiceOrders->isEmpty())
        <div class="px-4 py-12 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
                <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <p class="mt-3 text-sm font-medium text-gray-700">Nenhuma ordem de servico em aberto</p>
            <p class="mx-auto mt-1 max-w-xs text-sm text-gray-500">Se precisar de um atendimento, abra um chamado.</p>
            <a href="{{ route('crm.portal.tickets.create') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Abrir chamado
            </a>
        </div>
    @else
        <ul class="divide-y divide-gray-100">
            @foreach($openServiceOrders as $os)
            <li class="flex items-start gap-4 px-4 py-4 sm:px-5">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $os->codigo }}
                        <span class="font-normal text-gray-500">&middot; {{ $os->servico ?? $os->tipo_servico ?? 'Servico' }}</span>
                    </p>
                    <p class="mt-0.5 text-sm text-gray-500">
                        @if($os->data_agendamento)
                            Agendada para {{ $os->data_agendamento->format('d/m/Y') }}
                        @elseif($os->emissao)
                            Aberta em {{ $os->emissao->format('d/m/Y') }}
                        @else
                            Sem data informada
                        @endif
                    </p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium {{ ($osLabels[$os->situacao] ?? ['Concluida', 'bg-green-100 text-green-700'])[1] }}">
                    {{ ($osLabels[$os->situacao] ?? ['Concluida', 'bg-green-100 text-green-700'])[0] }}
                </span>
            </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
