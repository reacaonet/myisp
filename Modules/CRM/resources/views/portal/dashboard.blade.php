<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Cliente - {{ $client->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="max-w-4xl mx-auto py-8 px-4 space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Bem-vindo, {{ $client->name }}</h2>
                <p class="text-sm text-gray-500">{{ $client->document }} &middot; {{ $client->email ?? '-' }}</p>
            </div>
            <form method="POST" action="{{ route('crm.portal.logout') }}">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Sair</button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Contratos</p>
                <p class="text-2xl font-bold text-gray-900">{{ $client->contracts->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Faturas Pendentes</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $client->invoices->whereIn('status', ['pending', 'overdue'])->count() }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Ordens de Servico</p>
                <p class="text-2xl font-bold text-gray-900">{{ $client->serviceOrders->count() }}</p>
            </div>
        </div>

        @if($client->contracts->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Meus Contratos</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($client->contracts as $contract)
                <div class="p-6 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $contract->plan->name }}</p>
                        <p class="text-sm text-gray-500">
                            Ativado em {{ $contract->activation_date->format('d/m/Y') }}
                            &middot; Vencimento dia {{ $contract->due_day }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-900">R$ {{ number_format($contract->plan->price - $contract->discount, 2, ',', '.') }}</p>
                        @include('crm::clients._status_badge', ['status' => $contract->status])
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($client->invoices->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Minhas Faturas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                            <th class="px-6 py-3 font-medium">Numero</th>
                            <th class="px-6 py-3 font-medium">Referencia</th>
                            <th class="px-6 py-3 font-medium">Vencimento</th>
                            <th class="px-6 py-3 font-medium">Valor</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                            @if($client->invoices->firstWhere('link_boleto'))
                            <th class="px-6 py-3 font-medium">Boleto</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->invoices as $inv)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-gray-900">{{ $inv->invoice_number }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ str_pad($inv->mes ?? 0, 2, '0', STR_PAD_LEFT) }}/{{ $inv->ano ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $inv->due_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-gray-900 font-medium">R$ {{ number_format($inv->total ?? $inv->amount, 2, ',', '.') }}</td>
                            <td class="px-6 py-3">@include('crm::clients._status_badge', ['status' => $inv->status])</td>
                            @if($loop->first && $inv->link_boleto)
                            <td class="px-6 py-3">
                                <a href="{{ $inv->link_boleto }}" target="_blank" class="text-blue-600 hover:underline text-xs">Baixar</a>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($client->serviceOrders->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Ordens de Servico</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($client->serviceOrders as $os)
                <div class="p-6 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $os->codigo }} - {{ $os->servico ?? $os->tipo_servico }}</p>
                        <p class="text-sm text-gray-500">{{ $os->emissao?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs font-medium
                        @if($os->status == 'closed') bg-green-100 text-green-700
                        @elseif($os->status == 'canceled') bg-red-100 text-red-700
                        @else bg-blue-100 text-blue-700 @endif">
                        {{ ucfirst($os->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</body>
</html>
