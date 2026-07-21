<?php

namespace Modules\Billing\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        return Invoice::with('client', 'contract.plan', 'payments')->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
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
        $validated['invoice_number'] = 'FAT-' . date('Ymd') . '-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT);

        $invoice = Invoice::create($validated);

        return response()->json($invoice->load('client', 'contract'), 201);
    }

    public function show($id)
    {
        return Invoice::with('client', 'contract.plan', 'payments')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
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
        $invoice = Invoice::findOrFail($id);
        $invoice->payments()->delete();
        $invoice->delete();

        return response()->noContent();
    }
}
