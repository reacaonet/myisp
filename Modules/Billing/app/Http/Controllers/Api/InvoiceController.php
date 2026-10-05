<?php

namespace Modules\Billing\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;

class InvoiceController extends Controller
{
    public function index()
    {
        return Invoice::scoped()
            ->with('client', 'contract.plan', 'payments')
            ->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', $this->scopedClientRule()],
            'contract_id' => 'nullable|exists:contracts,id',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'in:pending,paid,overdue,canceled',
            'payment_method' => 'nullable|in:pix,boleto,credit_card,cash,debit_contract,other',
            'paid_date' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['discount'] ??= 0;
        $validated['total'] = $validated['amount'] - $validated['discount'];
        $client = Client::scoped()->find($validated['client_id']);
        $companyId = $client?->company_id ?? TenantContext::companyId() ?? 0;
        $validated['invoice_number'] = Invoice::nextNumber($companyId, $validated['due_date'] ?? now());

        $invoice = Invoice::create($validated);

        return response()->json($invoice->load('client', 'contract'), 201);
    }

    public function show($id)
    {
        return Invoice::scoped()
            ->with('client', 'contract.plan', 'payments')
            ->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findScopedOrFail((int) $id);

        $validated = $request->validate([
            'client_id' => [$this->scopedClientRule()],
            'contract_id' => 'nullable|exists:contracts,id',
            'amount' => 'numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'due_date' => 'date',
            'status' => 'in:pending,paid,overdue,canceled',
            'payment_method' => 'nullable|in:pix,boleto,credit_card,cash,debit_contract,other',
            'paid_date' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['amount']) || isset($validated['discount'])) {
            $validated['total'] = ($validated['amount'] ?? $invoice->amount) - ($validated['discount'] ?? $invoice->discount);
        }

        $invoice->update($validated);

        return response()->json($invoice->load('client', 'contract'));
    }

    public function destroy($id)
    {
        $invoice = Invoice::findScopedOrFail((int) $id);
        $invoice->payments()->delete();
        $invoice->delete();

        return response()->noContent();
    }

    /**
     * A listagem vinha sem filtro nenhum e o `client_id` aceitava qualquer id.
     * Agora a fatura nasce e continua presa a um cliente da filial do usuario.
     */
    private function scopedClientRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! $value) {
                $fail('Escolha um cliente.');

                return;
            }

            if (! Client::scoped()->whereKey($value)->exists()) {
                $fail('Cliente invalido.');
            }
        };
    }
}
