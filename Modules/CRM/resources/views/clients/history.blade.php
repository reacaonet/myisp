@extends('core::layouts.master')

@section('title', 'Historico - ' . $client->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $client->name }}</h2>
                <p class="text-sm text-gray-500">{{ $client->document }} &middot; {{ $client->email ?? '-' }} &middot; {{ $client->cellphone ?? $client->phone ?? '-' }}</p>
            </div>
            <a href="{{ route('crm.clients.show', $client) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">Voltar</a>
        </div>

        <div x-data="{ tab: 'dados' }">
            <div class="border-b border-gray-200 px-6">
                <nav class="flex gap-6 text-sm">
                    <button @click="tab = 'dados'" :class="tab === 'dados' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Dados</button>
                    <button @click="tab = 'contratos'" :class="tab === 'contratos' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Contratos ({{ $client->contracts->count() }})</button>
                    <button @click="tab = 'faturas'" :class="tab === 'faturas' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Faturas ({{ $client->invoices->count() }})</button>
                    <button @click="tab = 'os'" :class="tab === 'os' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Ordens de Servico ({{ $client->serviceOrders->count() }})</button>
                </nav>
            </div>

            <div class="p-6">
                <div x-show="tab === 'dados'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Dados Pessoais</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Codigo</dt><dd class="text-gray-900">{{ $client->codigo ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Documento</dt><dd class="text-gray-900">{{ $client->document }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">RG</dt><dd class="text-gray-900">{{ $client->rg ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Tipo</dt><dd class="text-gray-900">{{ $client->type == 'individual' ? 'Pessoa Fisica' : 'Pessoa Juridica' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Nascimento</dt><dd class="text-gray-900">{{ $client->birth_date?->format('d/m/Y') ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Estado Civil</dt><dd class="text-gray-900">{{ $client->estado_civil ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Naturalidade</dt><dd class="text-gray-900">{{ $client->naturalidade ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Pai</dt><dd class="text-gray-900">{{ $client->pai ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Mae</dt><dd class="text-gray-900">{{ $client->mae ?? '-' }}</dd></div>
                            </dl>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Contato & Acesso</h3>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Email</dt><dd class="text-gray-900">{{ $client->email ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Telefone</dt><dd class="text-gray-900">{{ $client->phone ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Celular</dt><dd class="text-gray-900">{{ $client->cellphone ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Login</dt><dd class="text-gray-900">{{ $client->login ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Grupo</dt><dd class="text-gray-900">{{ $client->grupo ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Data Entrada</dt><dd class="text-gray-900">{{ $client->data_entrada?->format('d/m/Y') ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Venc. Contrato</dt><dd class="text-gray-900">{{ $client->vcto_contrato?->format('d/m/Y') ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Tipo Assinante</dt><dd class="text-gray-900">{{ ['pf' => 'Pessoa Fisica', 'pj' => 'Pessoa Juridica'][$client->tipo_assinante] ?? $client->tipo_assinante ?? '-' }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Tipo Utilizacao</dt><dd class="text-gray-900">{{ ['residencial' => 'Residencial', 'comercial' => 'Comercial', 'institucional' => 'Institucional'][$client->tipo_utilizacao] ?? $client->tipo_utilizacao ?? '-' }}</dd></div>
                            </dl>

                            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 mt-6">Endereco</h3>
                            @php $addr = $client->addresses->first(); @endphp
                            @if($addr)
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Logradouro</dt><dd class="text-gray-900">{{ $addr->street }}, {{ $addr->number }}</dd></div>
                                @if($addr->complement)<div class="flex justify-between"><dt class="text-gray-500">Complemento</dt><dd class="text-gray-900">{{ $addr->complement }}</dd></div>@endif
                                @if($addr->referencia)<div class="flex justify-between"><dt class="text-gray-500">Referencia</dt><dd class="text-gray-900">{{ $addr->referencia }}</dd></div>@endif
                                <div class="flex justify-between"><dt class="text-gray-500">Bairro</dt><dd class="text-gray-900">{{ $addr->neighborhood }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Cidade/UF</dt><dd class="text-gray-900">{{ $addr->city }}/{{ $addr->state }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">CEP</dt><dd class="text-gray-900">{{ $addr->zipcode }}</dd></div>
                            </dl>
                            @else
                            <p class="text-sm text-gray-400">Nenhum endereco.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'contratos'">
                    @if($client->contracts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($client->contracts as $contract)
                        <a href="{{ route('crm.contracts.show', $contract) }}" class="block bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $contract->plan->name }}</p>
                                    <p class="text-sm text-gray-500">Ativado em {{ $contract->activation_date->format('d/m/Y') }} &middot; Dia {{ $contract->due_day }} &middot; R$ {{ number_format($contract->plan->price - $contract->discount, 2, ',', '.') }}</p>
                                </div>
                                @include('crm::clients._status_badge', ['status' => $contract->status])
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-8">Nenhum contrato encontrado.</p>
                    @endif
                </div>

                <div x-show="tab === 'faturas'">
                    @if($client->invoices->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                                    <th class="px-4 py-3 font-medium">#</th>
                                    <th class="px-4 py-3 font-medium">Referencia</th>
                                    <th class="px-4 py-3 font-medium">Vencimento</th>
                                    <th class="px-4 py-3 font-medium">Valor</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium">Pagamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($client->invoices as $inv)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-gray-900">{{ $inv->invoice_number ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ str_pad($inv->mes ?? 0, 2, '0', STR_PAD_LEFT) }}/{{ $inv->ano ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $inv->due_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-gray-900 font-medium">R$ {{ number_format($inv->total ?? $inv->amount, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">@include('crm::clients._status_badge', ['status' => $inv->status])</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $inv->paid_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-8">Nenhuma fatura encontrada.</p>
                    @endif
                </div>

                <div x-show="tab === 'os'">
                    @if($client->serviceOrders->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($client->serviceOrders as $os)
                        <a href="{{ route('crm.service-orders.show', $os) }}" class="block bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $os->codigo }} - {{ $os->servico ?? $os->tipo_servico }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ $os->emissao?->format('d/m/Y') ?? '-' }}
                                        @if($os->technician) &middot; {{ $os->technician->name }} @endif
                                        @if($os->preco > 0) &middot; R$ {{ number_format($os->preco, 2, ',', '.') }} @endif
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($os->status == 'closed') bg-green-100 text-green-700
                                    @elseif($os->status == 'canceled') bg-red-100 text-red-700
                                    @else bg-blue-100 text-blue-700 @endif">
                                    {{ ucfirst($os->status) }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400 text-center py-8">Nenhuma ordem de servico encontrada.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
