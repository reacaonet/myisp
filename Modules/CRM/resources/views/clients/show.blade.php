@extends('core::layouts.master')

@section('title', $client->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $client->name }}</h2>
                <p class="text-sm text-gray-500">{{ $client->document }} &middot; {{ $client->email ?? '-' }} &middot; {{ $client->cellphone ?? $client->phone ?? '-' }}</p>
            </div>
            <div class="flex items-center gap-2">
                @include('crm::clients._status_badge', ['status' => $client->status])
                <a href="{{ route('crm.clients.edit', $client) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">Editar</a>
            </div>
        </div>

        <div x-data="{ tab: 'dados' }">
            <div class="border-b border-gray-200 px-6 overflow-x-auto">
                <nav class="flex gap-6 text-sm whitespace-nowrap">
                    <button @click="tab = 'dados'" :class="tab === 'dados' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Dados do Cliente</button>
                    <button @click="tab = 'contratos'" :class="tab === 'contratos' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Assinaturas ({{ $client->contracts->count() }})</button>
                    <button @click="tab = 'faturas'" :class="tab === 'faturas' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Faturas ({{ $client->invoices->count() }})</button>
                    <button @click="tab = 'os'" :class="tab === 'os' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Ordens de Servico ({{ $client->serviceOrders->count() }})</button>
                    <button @click="tab = 'recibos'" :class="tab === 'recibos' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700'" class="pb-3 font-medium transition">Recibos</button>
                </nav>
            </div>

            {{-- Tab 1: Dados do Cliente --}}
            <div x-show="tab === 'dados'" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Dados Pessoais</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Codigo</dt><dd class="text-gray-900">{{ $client->codigo ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Documento</dt><dd class="text-gray-900 font-medium">{{ $client->document }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">RG/IE</dt><dd class="text-gray-900">{{ $client->rg ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Tipo</dt><dd class="text-gray-900">{{ $client->type == 'individual' ? 'Pessoa Fisica' : 'Pessoa Juridica' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Nascimento</dt><dd class="text-gray-900">{{ $client->birth_date?->format('d/m/Y') ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Estado Civil</dt><dd class="text-gray-900">{{ $client->estado_civil ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Naturalidade</dt><dd class="text-gray-900">{{ $client->naturalidade ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Pai</dt><dd class="text-gray-900">{{ $client->pai ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Mae</dt><dd class="text-gray-900">{{ $client->mae ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Insc. Estadual</dt><dd class="text-gray-900">{{ $client->state_registration ?? '-' }}</dd></div>
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
                            <div class="flex justify-between"><dt class="text-gray-500">NFSe</dt><dd class="text-gray-900">{{ $client->nf ? 'Sim' : 'Nao' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">CFOP</dt><dd class="text-gray-900">{{ $client->cfop ?? '-' }}</dd></div>
                        </dl>
                    </div>

                    <div class="md:col-span-2">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Endereco</h3>
                        @php $addr = $client->addresses->first(); @endphp
                        @if($addr)
                        <dl class="grid grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Logradouro</dt><dd class="text-gray-900">{{ $addr->street }}, {{ $addr->number }}</dd></div>
                            @if($addr->complement)<div class="flex justify-between"><dt class="text-gray-500">Complemento</dt><dd class="text-gray-900">{{ $addr->complement }}</dd></div>@endif
                            @if($addr->referencia)<div class="flex justify-between"><dt class="text-gray-500">Referencia</dt><dd class="text-gray-900">{{ $addr->referencia }}</dd></div>@endif
                            <div class="flex justify-between"><dt class="text-gray-500">Bairro</dt><dd class="text-gray-900">{{ $addr->neighborhood }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Cidade/UF</dt><dd class="text-gray-900">{{ $addr->city }}/{{ $addr->state }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">CEP</dt><dd class="text-gray-900">{{ $addr->zipcode }}</dd></div>
                        </dl>
                        @else
                        <p class="text-sm text-gray-400">Nenhum endereco cadastrado.</p>
                        @endif
                    </div>

                    @if($client->notes)
                    <div class="md:col-span-2">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Observacoes</h3>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $client->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tab 2: Assinaturas / Contratos --}}
            <div x-show="tab === 'contratos'" class="p-6">
                @if($client->contracts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 font-medium">Pedido</th>
                                <th class="px-4 py-3 font-medium">Plano</th>
                                <th class="px-4 py-3 font-medium">Servidor</th>
                                <th class="px-4 py-3 font-medium">Valor</th>
                                <th class="px-4 py-3 font-medium">Vencimento</th>
                                <th class="px-4 py-3 font-medium">Situacao</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->contracts as $contract)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-gray-900">{{ $contract->pedido ?? $contract->id }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $contract->plan->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $contract->server?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-900 font-medium">R$ {{ number_format($contract->plan->price - $contract->discount + $contract->acrescimo, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-gray-600">Dia {{ $contract->due_day }}</td>
                                <td class="px-4 py-3">
                                    @php $situacaoLabels = ['' => 'Normal', 'S' => 'Suspenso', 'I' => 'Inadimplente', 'C' => 'Cancelado', 'N' => 'Novo', 'F' => 'Fidelizado', 'D' => 'Desativado']; @endphp
                                    <span class="text-xs font-medium
                                        @if($contract->situacao == 'S' || $contract->situacao == 'I') text-red-600
                                        @elseif($contract->situacao == 'C' || $contract->situacao == 'D') text-gray-500
                                        @elseif($contract->situacao == 'N' || $contract->situacao == 'F') text-blue-600
                                        @else text-green-600 @endif">
                                        {{ $situacaoLabels[$contract->situacao] ?? 'Normal' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">@include('crm::clients._status_badge', ['status' => $contract->status])</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('crm.contracts.show', $contract) }}" class="text-blue-600 hover:underline text-sm">Detalhes</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-8">Nenhum contrato encontrado.</p>
                @endif
                <div class="mt-4">
                    <a href="{{ route('crm.contracts.create', ['client_id' => $client->id]) }}" class="text-sm text-blue-600 hover:underline">+ Novo Contrato</a>
                </div>
            </div>

            {{-- Tab 3: Faturas --}}
            <div x-show="tab === 'faturas'" class="p-6">
                @if($client->invoices->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 font-medium">Fatura</th>
                                <th class="px-4 py-3 font-medium">Referencia</th>
                                <th class="px-4 py-3 font-medium">Valor</th>
                                <th class="px-4 py-3 font-medium">Vencimento</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium">Pagamento</th>
                                <th class="px-4 py-3 font-medium text-right">Boleto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->invoices as $inv)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-gray-900">{{ $inv->invoice_number }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ str_pad($inv->mes ?? 0, 2, '0', STR_PAD_LEFT) }}/{{ $inv->ano ?? $inv->due_date->format('Y') }}</td>
                                <td class="px-4 py-3 text-gray-900 font-medium">R$ {{ number_format($inv->total ?? $inv->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $inv->due_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">@include('crm::clients._status_badge', ['status' => $inv->status])</td>
                                <td class="px-4 py-3 text-gray-600">{{ $inv->paid_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($inv->link_boleto)
                                    <a href="{{ $inv->link_boleto }}" target="_blank" class="text-blue-600 hover:underline text-sm">Boleto</a>
                                    @else
                                    <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-8">Nenhuma fatura encontrada.</p>
                @endif
            </div>

            {{-- Tab 4: Ordens de Servico --}}
            <div x-show="tab === 'os'" class="p-6">
                @if($client->serviceOrders->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 font-medium">Ordem</th>
                                <th class="px-4 py-3 font-medium">Servico</th>
                                <th class="px-4 py-3 font-medium">Tecnico</th>
                                <th class="px-4 py-3 font-medium">Emissao</th>
                                <th class="px-4 py-3 font-medium">Situacao</th>
                                <th class="px-4 py-3 font-medium">Status</th>
                                <th class="px-4 py-3 font-medium text-right">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->serviceOrders as $os)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-gray-900">{{ $os->codigo }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $os->servico ?? $os->tipo_servico ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $os->technician?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $os->emissao?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php $osSituacao = ['O' => 'Orcamento', 'I' => 'Instalado', 'NI' => 'Nao Instalado', 'M' => 'Manutencao', 'R' => 'Recuperacao', 'A' => 'Aprovado', 'CS' => 'Cancelado', 'C' => 'Concluido']; @endphp
                                    <span class="text-xs font-medium
                                        @if(in_array($os->situacao, ['CS'])) text-red-600
                                        @elseif(in_array($os->situacao, ['I', 'C'])) text-green-600
                                        @elseif($os->situacao == 'M') text-yellow-600
                                        @else text-blue-600 @endif">
                                        {{ $osSituacao[$os->situacao] ?? $os->situacao }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium
                                        @if($os->status == 'closed') bg-green-100 text-green-700
                                        @elseif($os->status == 'canceled') bg-red-100 text-red-700
                                        @else bg-blue-100 text-blue-700 @endif">
                                        {{ ucfirst($os->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('crm.service-orders.show', $os) }}" class="text-blue-600 hover:underline text-sm">Detalhes</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-8">Nenhuma ordem de servico encontrada.</p>
                @endif
            </div>

            {{-- Tab 5: Recibos (paid invoices) --}}
            <div x-show="tab === 'recibos'" class="p-6">
                @php $receipts = $client->invoices->where('status', 'paid'); @endphp
                @if($receipts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                                <th class="px-4 py-3 font-medium">Fatura</th>
                                <th class="px-4 py-3 font-medium">Referencia</th>
                                <th class="px-4 py-3 font-medium">Valor</th>
                                <th class="px-4 py-3 font-medium">Vencimento</th>
                                <th class="px-4 py-3 font-medium">Pagamento</th>
                                <th class="px-4 py-3 font-medium">Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipts as $rec)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-gray-900">{{ $rec->invoice_number }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ str_pad($rec->mes ?? 0, 2, '0', STR_PAD_LEFT) }}/{{ $rec->ano ?? $rec->due_date->format('Y') }}</td>
                                <td class="px-4 py-3 text-gray-900 font-medium">R$ {{ number_format($rec->total ?? $rec->amount, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $rec->due_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $rec->paid_date?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $rec->avulso ? 'Avulsa' : 'Mensalidade' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-400 text-center py-8">Nenhum recibo encontrado.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
