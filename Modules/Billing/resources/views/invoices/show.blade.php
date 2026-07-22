@extends('core::layouts.master')

@section('title', "Fatura {$invoice->invoice_number}")

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Fatura {{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-gray-500">Criada em {{ $invoice->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-2">
                @include('billing::partials._status_badge', ['status' => $invoice->status])
                <a href="{{ route('billing.invoices.edit', $invoice) }}" class="px-4 py-2 text-sm font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50">Editar</a>
                @if($invoice->status === 'paid')
                <a href="{{ route('billing.invoices.receipt', $invoice) }}" target="_blank" class="px-4 py-2 text-sm font-medium text-green-600 border border-green-200 rounded-lg hover:bg-green-50">Imprimir Recibo</a>
                @endif
                @if(!in_array($invoice->status, ['paid', 'canceled']))
                <form method="POST" action="{{ route('billing.invoices.block', $invoice) }}" onsubmit="return confirm('Bloquear este cliente no MikroTik?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Bloquear</button>
                </form>
                @endif
                @if($invoice->auto_blocked)
                <form method="POST" action="{{ route('billing.invoices.unblock', $invoice) }}" onsubmit="return confirm('Desbloquear este cliente?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-yellow-600 border border-yellow-200 rounded-lg hover:bg-yellow-50">Desbloquear</button>
                </form>
                @endif
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Cliente</h3>
                <p class="font-medium text-gray-900">{{ $invoice->client->name }}</p>
                <p class="text-sm text-gray-500">{{ $invoice->client->document }}</p>
                @php $addr = $invoice->client->addresses->first(); @endphp
                @if($addr)
                <p class="text-sm text-gray-500 mt-1">{{ $addr->street }}, {{ $addr->number }} - {{ $addr->neighborhood }}, {{ $addr->city }}/{{ $addr->state }}</p>
                @endif
                <a href="{{ route('crm.clients.show', $invoice->client) }}" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Ver cliente</a>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Detalhes</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Vencimento</dt>
                        <dd class="text-gray-900">{{ $invoice->due_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Valor</dt>
                        <dd class="text-gray-900">R$ {{ number_format($invoice->amount, 2, ',', '.') }}</dd>
                    </div>
                    @if($invoice->discount > 0)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Desconto</dt>
                        <dd class="text-green-600">- R$ {{ number_format($invoice->discount, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    @if($invoice->acrescimo > 0)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Acrescimo</dt>
                        <dd class="text-red-600">+ R$ {{ number_format($invoice->acrescimo, 2, ',', '.') }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between border-t pt-1">
                        <dt class="font-medium text-gray-700">Total</dt>
                        <dd class="font-bold text-gray-900">R$ {{ number_format($invoice->total, 2, ',', '.') }}</dd>
                    </div>
                    @if($invoice->paid_date)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Pago em</dt>
                        <dd class="text-gray-900">{{ $invoice->paid_date->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($invoice->payment_method)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Metodo</dt>
                        <dd class="text-gray-900">{{ strtoupper($invoice->payment_method) }}</dd>
                    </div>
                    @endif
                    @if($invoice->transaction_id)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Transacao</dt>
                        <dd class="text-gray-900 font-mono text-xs">{{ $invoice->transaction_id }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        @if($invoice->contract)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Contrato</h3>
            <p class="text-sm">{{ $invoice->contract->plan->name }} - Cliente desde {{ $invoice->contract->activation_date->format('d/m/Y') }}</p>
        </div>
        @endif

        @if($invoice->notes)
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Observacoes</h3>
            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-4">{{ $invoice->notes }}</p>
        </div>
        @endif

        @if($invoice->payments->isNotEmpty())
        <div class="border-t border-gray-200">
            <div class="p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Pagamentos</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b border-gray-200">
                            <th class="pb-2 font-medium">Data</th>
                            <th class="pb-2 font-medium">Valor</th>
                            <th class="pb-2 font-medium">Metodo</th>
                            <th class="pb-2 font-medium">Transacao</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                        <tr class="border-b border-gray-100">
                            <td class="py-2">{{ $payment->payment_date->format('d/m/Y') }}</td>
                            <td class="py-2 font-medium">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                            <td class="py-2">{{ strtoupper($payment->payment_method) }}</td>
                            <td class="py-2 font-mono text-xs">{{ $payment->transaction_id ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($invoice->auto_blocked)
        <div class="border-t border-gray-200 p-6 bg-red-50">
            <h3 class="text-sm font-semibold text-red-700 uppercase mb-2">Bloqueio Automatico</h3>
            <p class="text-sm text-red-600">Cliente bloqueado em {{ $invoice->blocked_at?->format('d/m/Y H:i') }} - {{ $invoice->motivo }}</p>
        </div>
        @endif

        @if($invoice->status !== 'paid')
        <div class="border-t border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Registrar Pagamento</h3>
            <form method="POST" action="{{ route('billing.invoices.payment', $invoice) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                @csrf
                <div>
                    <input type="number" step="0.01" name="amount" placeholder="Valor" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" value="{{ $invoice->total - $invoice->payments->sum('amount') }}">
                </div>
                <div>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <select name="payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="pix">PIX</option>
                        <option value="boleto">Boleto</option>
                        <option value="credit_card">Cartao</option>
                        <option value="cash">Dinheiro</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Pagar</button>
            </form>
        </div>
        @endif
    </div>

    <div class="mt-4 flex justify-end">
        <form method="POST" action="{{ route('billing.invoices.destroy', $invoice) }}" onsubmit="return confirm('Remover fatura {{ $invoice->invoice_number }}?')">
            @csrf @method('DELETE')
            <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Remover Fatura</button>
        </form>
    </div>
</div>
@endsection
