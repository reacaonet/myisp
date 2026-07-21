<?php

namespace Modules\Billing\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::with('invoice.client')->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:pix,boleto,credit_card,cash,debit_contract,other',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = Payment::create($validated);

        $invoice = Invoice::find($validated['invoice_id']);
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
        return Payment::with('invoice.client')->findOrFail($id);
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->noContent();
    }
}
