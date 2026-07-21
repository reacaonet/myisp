<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('client', 'contract.plan');

        if ($search = $request->get('search')) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('invoice_number', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->latest()->paginate(15);

        $stats = [
            'pending' => Invoice::where('status', 'pending')->sum('total'),
            'overdue' => Invoice::where('status', 'overdue')->sum('total'),
            'paid' => Invoice::where('status', 'paid')->sum('total'),
        ];

        return view('billing::invoices.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        return view('billing::invoices.create', compact('clients'));
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

        if ($validated['status'] === 'paid' && !$validated['paid_date']) {
            $validated['paid_date'] = now();
        }

        return redirect()->route('billing.invoices.show', $invoice)
            ->with('success', 'Fatura criada com sucesso.');
    }

    public function show($id)
    {
        $invoice = Invoice::with(['client.addresses', 'contract.plan', 'payments'])->findOrFail($id);
        return view('billing::invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::with('client', 'contract')->findOrFail($id);
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $contracts = Contract::where('client_id', $invoice->client_id)->with('plan')->get();
        return view('billing::invoices.edit', compact('invoice', 'clients', 'contracts'));
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

        return redirect()->route('billing.invoices.show', $invoice)
            ->with('success', 'Fatura atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->payments()->delete();
        $invoice->delete();

        return redirect()->route('billing.invoices.index')
            ->with('success', 'Fatura removida com sucesso.');
    }

    public function registerPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:pix,boleto,credit_card,cash,debit_contract,other',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = $invoice->payments()->create($validated);

        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update([
                'status' => 'paid',
                'paid_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'] ?? $invoice->transaction_id,
            ]);
        }

        return redirect()->route('billing.invoices.show', $invoice)
            ->with('success', 'Pagamento registrado com sucesso.');
    }

    public function receipt($id)
    {
        $invoice = Invoice::with(['client', 'contract.plan', 'payments'])->findOrFail($id);

        if ($invoice->status !== 'paid') {
            return redirect()->route('billing.invoices.show', $invoice)
                ->with('error', 'Recibo disponivel apenas para faturas pagas.');
        }

        return view('billing::invoices.receipt', compact('invoice'));
    }

    public function generateFromContracts()
    {
        $contracts = Contract::with('plan')->where('status', 'active')->get();
        $count = 0;

        foreach ($contracts as $contract) {
            $exists = Invoice::where('contract_id', $contract->id)
                ->whereMonth('due_date', now()->month)
                ->whereYear('due_date', now()->year)
                ->exists();

            if ($exists) continue;

            $dueDay = min($contract->due_day, 28);
            $dueDate = now()->day($dueDay);

            if ($dueDate->isPast()) {
                $dueDate = $dueDate->addMonth();
            }

            $amount = $contract->plan->price;
            $discount = $contract->discount;

            Invoice::create([
                'client_id' => $contract->client_id,
                'contract_id' => $contract->id,
                'invoice_number' => 'FAT-' . $dueDate->format('Ymd') . '-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'amount' => $amount,
                'discount' => $discount,
                'total' => $amount - $discount,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);

            $count++;
        }

        return redirect()->route('billing.invoices.index')
            ->with('success', "{$count} faturas geradas a partir dos contratos ativos.");
    }
}
