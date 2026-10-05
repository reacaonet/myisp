@extends('core::layouts.master')

@section('title', 'Painel da Franquia')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Painel da Franquia</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $company?->name ?? 'Nenhuma empresa vinculada' }}
                @if($company?->is_franchise)
                    <span class="ml-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-xs font-medium">franquia</span>
                @endif
            </p>
        </div>
        <a href="{{ route('core.franchisee.clients') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
            Ver todos os clientes
        </a>
    </div>

    @if(! $company)
        <div class="mb-4 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
            Este usuario ainda nao esta vinculado a uma empresa, entao nao ha dados de franquia para exibir.
            O superadmin define a empresa no cadastro do usuario, em <strong>Usuarios</strong> &rarr; <strong>Franquia de atuacao</strong>.
        </div>
    @elseif(\Modules\Core\Services\TenantContext::isCrossTenant())
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-sm">
            Visualizando como <strong>superadmin</strong>: os numeros abaixo somam a rede inteira, nao apenas uma franquia.
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['Clientes', $stats['clients_total'], 'clientes cadastrados'],
            ['Clientes ativos', $stats['clients_active'], 'com status ativo'],
            ['Contratos ativos', $stats['contracts_active'], 'contratos em vigor'],
            ['Sem contrato', $stats['clients_without_contract'], 'clientes sem contrato'],
            ['Chamados abertos', $stats['tickets_open'], 'abertos e em andamento'],
            ['Faturas em aberto', $stats['invoices_open'], 'pendentes e vencidas'],
            ['Valor em aberto', 'R$ '.number_format($stats['invoices_open_total'], 2, ',', '.'), 'pendente e vencido'],
        ] as [$label, $value, $hint])
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <p class="text-xs font-medium uppercase text-gray-500">{{ $label }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $value }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Clientes recentes</h3>
                <a href="{{ route('core.franchisee.clients') }}" class="text-sm text-blue-600 hover:underline">ver todos</a>
            </div>

            @if($recentClients->isEmpty())
                <p class="px-4 py-8 text-center text-sm text-gray-500">Nenhum cliente cadastrado.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-gray-500">Nome</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-500">Filial</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentClients as $client)
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="px-4 py-2 text-gray-900">{{ $client->name }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $client->branch?->name ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $client->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $client->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Chamados em aberto</h3>
            </div>

            @if($openTickets->isEmpty())
                <p class="px-4 py-8 text-center text-sm text-gray-500">Nenhum chamado em aberto.</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-gray-500">Chamado</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-500">Cliente</th>
                            <th class="text-left px-4 py-2 font-medium text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($openTickets as $ticket)
                            <tr class="border-b border-gray-100 last:border-0">
                                <td class="px-4 py-2 text-gray-900">#{{ $ticket->id }} {{ \Illuminate\Support\Str::limit($ticket->subject, 40) }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $ticket->client?->name ?? '-' }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $ticket->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection