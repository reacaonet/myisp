<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\CashBookEntry;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Services\MikrotikService;
use Modules\Billing\Mail\PaymentConfirmed;
use Modules\Billing\Mail\InvoiceGenerated;
use Illuminate\Support\Facades\Mail;

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
            'blocked' => Invoice::where('auto_blocked', true)->count(),
        ];

        return view('billing::invoices.index', compact('invoices', 'stats'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('billing::invoices.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'acrescimo' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'in:pending,paid,overdue,canceled',
            'payment_method' => 'nullable|in:pix,boleto,credit_card,cash,debit_contract,other',
            'paid_date' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['discount'] ??= 0;
        $validated['acrescimo'] ??= 0;
        $validated['total'] = $validated['amount'] - $validated['discount'] + $validated['acrescimo'];
        $validated['invoice_number'] = 'FAT-' . date('Ymd') . '-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT);

        if ($validated['status'] === 'paid' && !$validated['paid_date']) {
            $validated['paid_date'] = now();
        }

        $invoice = Invoice::create($validated);

        if ($invoice->client && $invoice->client->email) {
            try {
                Mail::to($invoice->client->email)->send(new InvoiceGenerated($invoice));
            } catch (\Exception $e) {
            }
        }

        return redirect()->route('billing.invoices.show', $invoice)
            ->with('success', 'Fatura criada com sucesso.');
    }

    public function show($id)
    {
        $invoice = Invoice::with(['client.addresses', 'contract.plan', 'contract.server', 'payments'])->findOrFail($id);
        return view('billing::invoices.show', compact('invoice'));
    }

    public function edit($id)
    {
        $invoice = Invoice::with('client', 'contract')->findOrFail($id);
        $clients = Client::orderBy('name')->get();
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
            'acrescimo' => 'nullable|numeric|min:0',
            'due_date' => 'date',
            'status' => 'in:pending,paid,overdue,canceled',
            'payment_method' => 'nullable|in:pix,boleto,credit_card,cash,debit_contract,other',
            'paid_date' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['amount']) || isset($validated['discount']) || isset($validated['acrescimo'])) {
            $validated['total'] = ($validated['amount'] ?? $invoice->amount) - ($validated['discount'] ?? $invoice->discount) + ($validated['acrescimo'] ?? $invoice->acrescimo);
        }

        $oldStatus = $invoice->status;
        $invoice->update($validated);

        if ($oldStatus !== 'paid' && $invoice->status === 'paid') {
            $this->tryUnblockContract($invoice);
        }

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

        CashBookEntry::create([
            'type' => 'entrada',
            'amount' => $validated['amount'],
            'description' => "Pagamento fatura {$invoice->invoice_number}",
            'category' => 'pagamento',
            'entry_date' => $validated['payment_date'],
            'reference' => $validated['transaction_id'] ?? null,
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'invoice_id' => $invoice->id,
        ]);

        $totalPaid = $invoice->payments()->sum('amount');
        if ($totalPaid >= $invoice->total) {
            $invoice->update([
                'status' => 'paid',
                'paid_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $validated['transaction_id'] ?? $invoice->transaction_id,
                'auto_blocked' => false,
            ]);

            $this->tryUnblockContract($invoice);
        }

        if ($invoice->client && $invoice->client->email) {
            try {
                Mail::to($invoice->client->email)->send(new PaymentConfirmed($invoice));
            } catch (\Exception $e) {
            }
        }

        return redirect()->route('billing.invoices.show', $invoice)
            ->with('success', 'Pagamento registrado com sucesso.');
    }

    public function block($id)
    {
        $invoice = Invoice::findOrFail($id);
        $contract = $invoice->contract;

        if (!$contract || !$contract->server) {
            return back()->with('error', 'Contrato ou servidor MikroTik nao encontrado.');
        }

        try {
            $service = new MikrotikService();
            $service->connect($contract->server);

            if ($contract->tipo_conexao === 'pppoe' && $contract->pppoe_user) {
                $service->disconnectPppoeActive($contract->pppoe_user);
            } elseif ($contract->tipo_conexao === 'hotspot' && $contract->pppoe_user) {
                $service->disconnectHotspotActive($contract->pppoe_user);
            }

            $service->disconnect();

            $invoice->update([
                'status' => 'overdue',
                'blocked_at' => now(),
                'auto_blocked' => false,
                'motivo' => 'Bloqueio manual pelo administrador',
            ]);

            $contract->update(['status' => 'suspended']);

            return back()->with('success', "Cliente {$contract->client?->name} bloqueado com sucesso.");

        } catch (\Exception $e) {
            return back()->with('error', "Erro ao bloquear: " . $e->getMessage());
        }
    }

    public function unblock($id)
    {
        $invoice = Invoice::findOrFail($id);
        $contract = $invoice->contract;

        if (!$contract) {
            return back()->with('error', 'Contrato nao encontrado.');
        }

        $invoice->update([
            'auto_blocked' => false,
            'blocked_at' => null,
            'motivo' => null,
        ]);

        if ($contract->status === 'suspended') {
            $contract->update(['status' => 'active']);
        }

        return back()->with('success', "Cliente {$contract->client?->name} desbloqueado com sucesso.");
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
            if (!$contract->plan) continue;

            $exists = Invoice::where('contract_id', $contract->id)
                ->whereMonth('due_date', now()->month)
                ->whereYear('due_date', now()->year)
                ->where('avulso', false)
                ->exists();

            if ($exists) continue;

            $dueDay = min($contract->due_day, 28);
            $dueDate = now()->day($dueDay);

            if ($dueDate->isPast()) {
                $dueDate = $dueDate->addMonth();
            }

            $amount = $contract->plan->price;
            $discount = $contract->discount ?? 0;
            $acrescimo = $contract->acrescimo ?? 0;

            Invoice::create([
                'client_id' => $contract->client_id,
                'contract_id' => $contract->id,
                'invoice_number' => 'FAT-' . $dueDate->format('Ymd') . '-' . str_pad(Invoice::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'amount' => $amount,
                'discount' => $discount,
                'acrescimo' => $acrescimo,
                'total' => $amount - $discount + $acrescimo,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);

            $count++;
        }

        return redirect()->route('billing.invoices.index')
            ->with('success', "{$count} faturas geradas a partir dos contratos ativos.");
    }

    private function tryUnblockContract(Invoice $invoice): void
    {
        $contract = $invoice->contract;
        if (!$contract) return;

        if ($contract->status === 'suspended') {
            $hasOtherOverdue = Invoice::where('contract_id', $contract->id)
                ->where('id', '!=', $invoice->id)
                ->whereIn('status', ['pending', 'overdue'])
                ->exists();

            if (!$hasOtherOverdue) {
                $contract->update(['status' => 'active']);

                if ($contract->server) {
                    try {
                        $service = new MikrotikService();
                        $service->connect($contract->server);
                        if ($contract->tipo_conexao === 'pppoe' && $contract->pppoe_user) {
                            $service->disconnectPppoeActive($contract->pppoe_user);
                        } elseif ($contract->tipo_conexao === 'hotspot' && $contract->pppoe_user) {
                            $service->disconnectHotspotActive($contract->pppoe_user);
                        }
                        $service->disconnect();
                    } catch (\Exception $e) {
                        // silent fail for MikroTik disconnect
                    }
                }
            }
        }
    }
}
