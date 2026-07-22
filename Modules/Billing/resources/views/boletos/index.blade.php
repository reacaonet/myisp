@extends('core::layouts.master')

@section('title', 'Boletos e Cobrancas')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Boletos e Cobrancas</h2>
        <a href="{{ route('billing.gateways.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-medium hover:bg-gray-700">Gerenciar Gateways</a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-4 mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">{{ session('error') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex gap-4 items-end">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por cliente ou numero da fatura..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos os status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Atrasada</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paga</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Filtrar</button>
        </div>
    </form>

    @if($gateways->count() > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">Gateways Ativos</h3>
        <div class="flex gap-3">
            @foreach($gateways as $gw)
            <span class="px-3 py-1 text-xs rounded-full bg-white border border-blue-200 text-blue-700">
                {{ $gw->name }}
                @if($gw->supports_boleto) Boleto @endif
                @if($gw->supports_pix) PIX @endif
                @if($gw->supports_credit_card) Cartao @endif
            </span>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-yellow-800">Nenhum gateway configurado. <a href="{{ route('billing.gateways.create') }}" class="underline font-medium">Adicionar gateway</a> para gerar cobrancas online.</p>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Cliente</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Fatura</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Vencimento</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Valor</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Gateway</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-500">Acoes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium">{{ $invoice->client->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $invoice->invoice_number }}</td>
                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 font-medium">R$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            {{ $invoice->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                            {{ $invoice->status === 'canceled' ? 'bg-gray-100 text-gray-500' : '' }}">
                            {{ $invoice->status === 'pending' ? 'Pendente' : '' }}
                            {{ $invoice->status === 'paid' ? 'Paga' : '' }}
                            {{ $invoice->status === 'overdue' ? 'Atrasada' : '' }}
                            {{ $invoice->status === 'canceled' ? 'Cancelada' : '' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($invoice->gateway)
                            <span class="text-xs text-gray-600">{{ $invoice->gateway->name }}</span>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1 flex-wrap">
                            <button onclick="openGatewayModal({{ $invoice->id }}, 'boleto')" class="text-blue-600 hover:underline text-xs">Gerar Boleto</button>
                            <button onclick="openGatewayModal({{ $invoice->id }}, 'pix')" class="text-emerald-600 hover:underline text-xs">Gerar PIX</button>
                            @if($invoice->boleto_numero)
                                <a href="{{ route('billing.boleto.print', $invoice) }}" target="_blank" class="text-gray-500 hover:underline text-xs">Imprimir</a>
                                <a href="{{ route('billing.boleto.refresh-status', $invoice) }}" class="text-gray-500 hover:underline text-xs">Sincronizar</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Nenhuma fatura pendente encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $invoices->withQueryString()->links() }}
    </div>
</div>

<div id="gatewayModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4" id="modalTitle">Selecionar Gateway</h3>
        <form id="gatewayForm" method="POST">
            @csrf
            <input type="hidden" name="gateway_id" id="selectedGateway">

            <div class="space-y-2 mb-6">
                @forelse($gateways as $gw)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer gateway-option" data-gateway="{{ $gw->id }}" data-type="">
                    <input type="radio" name="gateway_select" value="{{ $gw->id }}" class="text-blue-600">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $gw->name }}</p>
                        <p class="text-xs text-gray-500">
                            @if($gw->supports_boleto) Boleto @endif
                            @if($gw->supports_pix) PIX @endif
                            @if($gw->supports_credit_card) Cartao @endif
                        </p>
                    </div>
                </label>
                @empty
                <p class="text-sm text-gray-500">Nenhum gateway disponivel.</p>
                @endforelse
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeGatewayModal()" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentType = '';

function openGatewayModal(invoiceId, type) {
    currentType = type;
    const modal = document.getElementById('gatewayModal');
    const form = document.getElementById('gatewayForm');
    const title = document.getElementById('modalTitle');

    form.action = '/faturas/' + invoiceId + '/' + (type === 'boleto' ? 'gerar-boleto' : 'gerar-pix');
    title.textContent = type === 'boleto' ? 'Gerar Boleto' : 'Gerar PIX';

    document.querySelectorAll('.gateway-option').forEach(el => {
        el.style.display = 'none';
        const gw = JSON.parse(el.dataset.gateway);
        const radio = el.querySelector('input[type=radio]');
        radio.checked = false;
    });

    @foreach($gateways as $gw)
        @if($type === 'boleto' && $gw->supports_boleto)
            document.querySelector('.gateway-option[data-gateway="{{ $gw->id }}"]').style.display = 'flex';
        @endif
        @if($type === 'pix' && $gw->supports_pix)
            document.querySelector('.gateway-option[data-gateway="{{ $gw->id }}"]').style.display = 'flex';
        @endif
    @endforeach

    modal.classList.remove('hidden');
}

function closeGatewayModal() {
    document.getElementById('gatewayModal').classList.add('hidden');
}

document.querySelectorAll('input[name="gateway_select"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.getElementById('selectedGateway').value = this.value;
    });
});
</script>
@endsection
