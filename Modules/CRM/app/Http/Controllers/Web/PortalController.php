<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Ticket;
use Modules\CRM\Models\TicketMessage;
use Modules\CRM\Models\Contract;
use Modules\Billing\Models\Invoice;

class PortalController extends Controller
{
    public function loginForm()
    {
        return view('crm::portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string',
        ]);

        if (Auth::guard('client')->attempt([
            'login' => $credentials['login'],
            'password' => $credentials['senha'],
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('crm.portal.dashboard'));
        }

        return back()->withErrors(['login' => 'Credenciais invalidas.'])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('crm.portal.login');
    }

    public function dashboard()
    {
        $client = Auth::guard('client')->user()->load([
            'addresses',
            'contracts.plan',
            'invoices' => fn($q) => $q->latest(),
            'serviceOrders' => fn($q) => $q->latest(),
        ]);

        $stats = [
            'total_contracts' => $client->contracts->count(),
            'active_contracts' => $client->contracts->where('status', 'active')->count(),
            'pending_invoices' => $client->invoices->whereIn('status', ['pending', 'overdue'])->count(),
            'pending_amount' => $client->invoices->whereIn('status', ['pending', 'overdue'])->sum('total'),
            'paid_invoices' => $client->invoices->where('status', 'paid')->count(),
            'open_os' => $client->serviceOrders->whereNotIn('status', ['closed', 'canceled'])->count(),
            'open_tickets' => Ticket::where('client_id', $client->id)->whereIn('status', ['open', 'in_progress'])->count(),
            'last_invoice' => $client->invoices->first(),
        ];

        return view('crm::portal.dashboard', compact('client', 'stats'));
    }

    public function invoices()
    {
        $client = Auth::guard('client')->user();
        $invoices = Invoice::where('client_id', $client->id)
            ->with('payments')
            ->latest('due_date')
            ->paginate(20);

        return view('crm::portal.invoices.index', compact('invoices'));
    }

    public function invoiceShow($id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with(['payments', 'contract.plan'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        return view('crm::portal.invoices.show', compact('invoice'));
    }

    public function invoiceReceipt($id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with(['payments', 'contract.plan'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($invoice->status !== 'paid') {
            return redirect()->route('crm.portal.invoices')
                ->with('error', 'Recibo disponivel apenas para faturas pagas.');
        }

        return view('crm::portal.invoices.receipt', compact('invoice'));
    }

    public function contracts()
    {
        $client = Auth::guard('client')->user()->load([
            'contracts.plan',
            'contracts.server',
        ]);

        return view('crm::portal.contracts.index', compact('client'));
    }

    public function contractShow($id)
    {
        $client = Auth::guard('client')->user();
        $contract = $client->contracts()->with(['plan', 'server'])->findOrFail($id);

        return view('crm::portal.contracts.show', compact('contract'));
    }

    public function serviceOrders()
    {
        $client = Auth::guard('client')->user()->load([
            'serviceOrders.technician',
        ]);

        return view('crm::portal.service_orders.index', compact('client'));
    }

    public function profile()
    {
        $client = Auth::guard('client')->user()->load('addresses');
        return view('crm::portal.profile.index', compact('client'));
    }

    public function updateProfile(Request $request)
    {
        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
        ]);

        $client->update($validated);

        return redirect()->route('crm.portal.profile')
            ->with('success', 'Dados atualizados com sucesso.');
    }

    public function changePassword(Request $request)
    {
        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'current_senha' => 'required|string',
            'new_senha' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_senha'], $client->senha)) {
            return back()->withErrors(['current_senha' => 'Senha atual incorreta.']);
        }

        $client->update(['senha' => $validated['new_senha']]);

        return redirect()->route('crm.portal.profile')
            ->with('success', 'Senha alterada com sucesso.');
    }

    public function tickets()
    {
        $client = Auth::guard('client')->user();

        $tickets = Ticket::where('client_id', $client->id)
            ->with('latestMessage')
            ->latest()
            ->paginate(20);

        return view('crm::portal.tickets.index', compact('tickets'));
    }

    public function ticketCreate()
    {
        $client = Auth::guard('client')->user()->load('activeContracts.plan');
        return view('crm::portal.tickets.create', compact('client'));
    }

    public function ticketStore(Request $request)
    {
        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:100',
            'contract_id' => 'nullable|exists:contracts,id',
        ]);

        $validated['client_id'] = $client->id;
        $validated['codigo'] = 'CHM-' . str_pad(Ticket::max('id') + 1, 5, '0', STR_PAD_LEFT);
        $validated['status'] = 'open';
        $validated['priority'] = 'low';

        $ticket = Ticket::create($validated);

        $ticket->messages()->create([
            'sender_type' => 'client',
            'sender_id' => $client->id,
            'message' => $validated['description'],
        ]);

        return redirect()->route('crm.portal.tickets.show', $ticket)
            ->with('success', 'Chamado aberto com sucesso. Codigo: ' . $ticket->codigo);
    }

    public function ticketShow($id)
    {
        $client = Auth::guard('client')->user();
        $ticket = Ticket::with(['contract.plan', 'messages'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        return view('crm::portal.tickets.show', compact('ticket'));
    }

    public function ticketReply(Request $request, $id)
    {
        $client = Auth::guard('client')->user();
        $ticket = Ticket::where('client_id', $client->id)->findOrFail($id);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $ticket->messages()->create([
            'sender_type' => 'client',
            'sender_id' => $client->id,
            'message' => $validated['message'],
        ]);

        return redirect()->route('crm.portal.tickets.show', $ticket)
            ->with('success', 'Mensagem enviada.');
    }
}
