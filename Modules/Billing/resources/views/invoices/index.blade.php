@extends('core::layouts.master')

@section('title', 'Faturas')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">A Receber (Pendente)</p>
        <p class="text-2xl font-bold text-yellow-600 mt-1">R$ {{ number_format($stats['pending'], 2, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Vencido</p>
        <p class="text-2xl font-bold text-red-600 mt-1">R$ {{ number_format($stats['overdue'], 2, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-500">Recebido</p>
        <p class="text-2xl font-bold text-green-600 mt-1">R$ {{ number_format($stats['paid'], 2, ',', '.') }}</p>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-lg font-semibold text-gray-800">Faturas</h2>
        <div class="flex gap-3 flex-wrap">
            <button type="submit" form="bulk-invoices-form" onclick="return checkInvoiceBulkSelection()"
                style="background-color:#dc2626;color:#ffffff;font-weight:600;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;"
                onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                Excluir Selecionadas
            </button>
            <a href="{{ route('billing.invoices.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nova Fatura
            </a>
            <form method="POST" action="{{ route('billing.invoices.generate') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                    Gerar Faturas
                </button>
            </form>
        </div>
    </div>

    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[220px]">
                <label class="block text-xs text-gray-500 mb-1" for="filter-search">Busca</label>
                <input type="text" id="filter-search" name="search" value="{{ request('search') }}"
                       placeholder="Cliente, numero da fatura, boleto ou transacao..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-status">Status</label>
                <select id="filter-status" name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="this.form.submit()">
                    <option value="">Todos</option>
                    @foreach(\Modules\Billing\Services\InvoiceListFilter::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-payment-method">Forma de pagamento</label>
                <select id="filter-payment-method" name="payment_method" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todas</option>
                    @foreach(\Modules\Billing\Services\InvoiceListFilter::PAYMENT_METHODS as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_method') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-branch">Filial</label>
                <select id="filter-branch" name="branch_id" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todas</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((int) request('branch_id') === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-due-from">Vencimento de</label>
                <input type="date" id="filter-due-from" name="due_from" value="{{ request('due_from') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-due-to">Vencimento ate</label>
                <input type="date" id="filter-due-to" name="due_to" value="{{ request('due_to') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-paid-from">Pago de</label>
                <input type="date" id="filter-paid-from" name="paid_from" value="{{ request('paid_from') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-paid-to">Pago ate</label>
                <input type="date" id="filter-paid-to" name="paid_to" value="{{ request('paid_to') }}"
                       class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-total-min">Valor minimo</label>
                <input type="number" step="0.01" min="0" id="filter-total-min" name="total_min" value="{{ request('total_min') }}"
                       class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-total-max">Valor maximo</label>
                <input type="number" step="0.01" min="0" id="filter-total-max" name="total_max" value="{{ request('total_max') }}"
                       class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-blocked">Bloqueio</label>
                <select id="filter-blocked" name="blocked" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="sim" @selected(request('blocked') === 'sim')>Bloqueadas</option>
                    <option value="nao" @selected(request('blocked') === 'nao')>Nao bloqueadas</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1" for="filter-avulso">Tipo</label>
                <select id="filter-avulso" name="avulso" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="sim" @selected(request('avulso') === 'sim')>Avulsas</option>
                    <option value="nao" @selected(request('avulso') === 'nao')>Contrato</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Filtrar</button>
                @if(request()->hasAny(['search', 'status', 'payment_method', 'branch_id', 'due_from', 'due_to', 'paid_from', 'paid_to', 'total_min', 'total_max', 'blocked', 'avulso']))
                    <a href="{{ route('billing.invoices.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">Limpar</a>
                @endif
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('billing.invoices.bulk-destroy') }}" id="bulk-invoices-form">
        @csrf
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 bg-gray-50 border-b border-gray-200">
                    <th class="px-4 py-4 font-medium w-10">
                        <input type="checkbox" id="select-all-invoices" class="rounded border-gray-300 text-blue-600">
                    </th>
                    <th class="px-6 py-4 font-medium">Fatura</th>
                    <th class="px-6 py-4 font-medium">Cliente</th>
                    <th class="px-6 py-4 font-medium">Valor</th>
                    <th class="px-6 py-4 font-medium">Vencimento</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-4">
                        <input type="checkbox" name="ids[]" value="{{ $invoice->id }}" form="bulk-invoices-form"
                               class="invoice-checkbox rounded border-gray-300 text-blue-600">
                    </td>
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $invoice->invoice_number }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('billing.invoices.show', $invoice) }}" class="text-blue-600 hover:underline font-medium">{{ $invoice->client?->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-gray-900 font-medium">R$ {{ number_format($invoice->total, 2, ',', '.') }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $invoice->due_date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">@include('billing::partials._status_badge', ['status' => $invoice->status])</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-0.5">
                            <a href="{{ route('billing.invoices.show', $invoice) }}" title="Detalhes" class="p-1.5 rounded hover:bg-gray-100 text-gray-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                            <a href="{{ route('billing.invoices.edit', $invoice) }}" title="Editar" class="p-1.5 rounded hover:bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
                            <form method="POST" action="{{ route('billing.invoices.destroy', $invoice) }}" onsubmit="return confirm('Remover fatura {{ $invoice->invoice_number }}?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" title="Excluir" class="p-1.5 rounded hover:bg-red-50 text-red-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-gray-400">Nenhuma fatura encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-6 border-t border-gray-200 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <button type="submit" form="bulk-invoices-form" onclick="return checkInvoiceBulkSelection()"
                style="background-color:#dc2626;color:#ffffff;font-weight:600;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;"
                onmouseover="this.style.backgroundColor='#b91c1c'" onmouseout="this.style.backgroundColor='#dc2626'">
                Excluir Selecionadas
            </button>
            <span class="text-sm text-gray-400" id="invoices-selected-count">Selecione faturas para excluir em massa</span>
        </div>
        @if($invoices->hasPages())
            <div>{{ $invoices->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all-invoices');
    const checkboxes = document.querySelectorAll('.invoice-checkbox');
    const counter = document.getElementById('invoices-selected-count');

    function update() {
        const checked = document.querySelectorAll('.invoice-checkbox:checked');
        counter.textContent = checked.length > 0
            ? checked.length + ' fatura(s) selecionada(s)'
            : 'Selecione faturas para excluir em massa';
    }

    window.checkInvoiceBulkSelection = function () {
        const checked = document.querySelectorAll('.invoice-checkbox:checked');
        if (checked.length === 0) {
            alert('Selecione ao menos uma fatura.');
            return false;
        }
        return confirm('Excluir ' + checked.length + ' fatura(s)? Os pagamentos vinculados tambem serao removidos. Esta acao nao pode ser desfeita.');
    };

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
            update();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', update));
});
</script>
@endpush