<?php

namespace Modules\Billing\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Core\Services\TenantContext;

class PaymentController extends Controller
{
    public function index()
    {
        return $this->scoped()->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:pix,boleto,credit_card,cash,debit_contract,other',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // A fatura precisa sair do mesmo tenant de quem esta pagando. Sem
        // esta busca um usuario de uma franquia baixava um pagamento de
        // qualquer empresa e ainda alterava o status da fatura alheia.
        $invoice = $this->scopedInvoices()->find($validated['invoice_id']);

        if (! $invoice) {
            return response()->json(['message' => 'Fatura nao encontrada.'], 404);
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $validated['transaction_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $totalPaid = $invoice->payments()->sum('amount') + $validated['amount'];

        if ($totalPaid >= $invoice->total) {
            $invoice->update([
                'status' => 'paid',
                'paid_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'] ?? $invoice->transaction_id,
            ]);
        }

        return response()->json($payment->load('invoice'), 201);
    }

    public function show($id)
    {
        return $this->scoped()->findOrFail($id);
    }

    public function destroy($id)
    {
        $payment = $this->scoped()->findOrFail($id);
        $payment->delete();

        return response()->noContent();
    }

    /**
     * Payment nao tem company_id proprio: o escopo vem da fatura. Um global
     * scope aqui esconderia o pagamento de quem o registrou.
     */
    private function scoped(): Builder
    {
        $query = Payment::with('invoice.client');

        if (! TenantContext::isCrossTenant()) {
            $query->whereIn(
                'invoice_id',
                $this->scopedInvoices()->select('invoices.id')
            );
        }

        return $query;
    }

    private function scopedInvoices(): Builder
    {
        // `forTenant` corta tambem por filial: com so `company_id` o pagamento
        // das faturas das outras lojas da mesma empresa aparecia.
        return Invoice::query()->forTenant();
    }
}
