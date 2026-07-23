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
use Modules\Billing\Models\PaymentGateway;
use Modules\Billing\Services\PaymentService;
use Modules\Core\Models\SystemSetting;

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

    public function invoicePaymentForm($id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with(['payments', 'contract.plan'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return redirect()->route('crm.portal.invoices.show', $invoice)
                ->with('error', 'Esta fatura ja foi paga.');
        }

        $gateways = PaymentGateway::where('status', 'active')
            ->where(function ($q) {
                $q->where('supports_pix', true)->orWhere('supports_boleto', true);
            })
            ->get();

        return view('crm::portal.invoices.pay', compact('invoice', 'gateways'));
    }

    public function invoicePay(Request $request, $id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with('client')
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Esta fatura ja foi paga.');
        }

        $validated = $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
            'payment_method' => 'required|in:pix,boleto',
        ]);

        $gateway = PaymentGateway::findOrFail($validated['gateway_id']);

        if ($validated['payment_method'] === 'pix' && !$gateway->supports_pix) {
            return back()->with('error', 'Este gateway nao suporta PIX.');
        }

        if ($validated['payment_method'] === 'boleto' && !$gateway->supports_boleto) {
            return back()->with('error', 'Este gateway nao suporta Boleto.');
        }

        $service = PaymentService::getGateway($gateway->slug);
        if (!$service) {
            return back()->with('error', 'Gateway de pagamento indisponivel.');
        }

        $invoice->update(['gateway_id' => $gateway->id]);

        if ($validated['payment_method'] === 'pix') {
            $result = $service->generatePix($invoice);
        } else {
            $result = $service->generateBoleto($invoice);
        }

        if (!isset($result['success']) || !$result['success']) {
            return back()->with('error', $result['error'] ?? 'Erro ao gerar pagamento.');
        }

        $invoice->refresh();

        return view('crm::portal.invoices.pay-result', compact('invoice', 'result', 'validated'));
    }

    public function invoiceBoleto($id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with(['client', 'gateway', 'contract.plan'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        $bankSettings = [
            'bank' => SystemSetting::get('bank_name', 'Banco do Brasil'),
            'agency' => SystemSetting::get('bank_agency', ''),
            'account' => SystemSetting::get('bank_account', ''),
            'company' => SystemSetting::get('company_name', 'Minha ISP'),
            'cnpj' => SystemSetting::get('company_document', ''),
        ];

        $mpAccount = null;
        if ($invoice->gateway && $invoice->gateway->slug === 'mercado-pago') {
            $mpAccount = $this->fetchMpAccountInfo($invoice->gateway);
        }

        return view('crm::portal.invoices.boleto', compact('invoice', 'bankSettings', 'mpAccount'));
    }

    public function invoiceGenerateBoleto(Request $request, $id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with('client')
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Esta fatura ja foi paga.');
        }

        $validated = $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $gateway = PaymentGateway::findOrFail($validated['gateway_id']);

        if (!$gateway->supports_boleto) {
            return back()->with('error', 'Este gateway nao suporta boleto.');
        }

        $service = PaymentService::getGateway($gateway->slug);
        if (!$service) {
            return back()->with('error', 'Gateway de pagamento indisponivel.');
        }

        $invoice->update(['gateway_id' => $gateway->id]);
        $result = $service->generateBoleto($invoice);

        if (!isset($result['success']) || !$result['success']) {
            return back()->with('error', $result['error'] ?? 'Erro ao gerar boleto.');
        }

        return redirect()->route('crm.portal.invoices.boleto', $invoice)
            ->with('success', 'Boleto gerado com sucesso.');
    }

    public function invoiceGeneratePix(Request $request, $id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with('client')
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Esta fatura ja foi paga.');
        }

        $validated = $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $gateway = PaymentGateway::findOrFail($validated['gateway_id']);

        if (!$gateway->supports_pix) {
            return back()->with('error', 'Este gateway nao suporta PIX.');
        }

        $service = PaymentService::getGateway($gateway->slug);
        if (!$service) {
            return back()->with('error', 'Gateway de pagamento indisponivel.');
        }

        $invoice->update(['gateway_id' => $gateway->id]);
        $result = $service->generatePix($invoice);

        if (!isset($result['success']) || !$result['success']) {
            return back()->with('error', $result['error'] ?? 'Erro ao gerar PIX.');
        }

        $invoice->refresh();

        return view('crm::portal.invoices.pay-result', compact('invoice', 'result', 'validated'));
    }

    public function invoiceCancelBoleto($id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with(['client', 'gateway'])
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Nao e possivel cancelar uma fatura ja paga.');
        }

        if (!$invoice->gateway_id || !$invoice->boleto_numero) {
            return back()->with('error', 'Nenhum pagamento ativo para cancelar.');
        }

        $service = PaymentService::forInvoice($invoice);
        if ($service) {
            $result = $service->cancelPayment($invoice);
            if (!$result) {
                return back()->with('error', 'Erro ao cancelar pagamento no gateway.');
            }
        }

        $invoice->update([
            'status' => 'canceled',
            'gateway_status' => 'cancelled',
            'gateway_id' => null,
            'boleto_numero' => null,
            'link_boleto' => null,
            'gateway_payment_url' => null,
            'gateway_qr_code' => null,
            'pix_copy_paste' => null,
            'barcode' => null,
            'digitable_line' => null,
            'transaction_id' => null,
        ]);

        return back()->with('success', 'Pagamento cancelado com sucesso.');
    }

    public function invoiceDeleteBoleto($id)
    {
        $client = Auth::guard('client')->user();
        $invoice = Invoice::with('client')
            ->where('client_id', $client->id)
            ->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Nao e possivel excluir o boleto de uma fatura ja paga.');
        }

        $invoice->update([
            'gateway_id' => null,
            'boleto_numero' => null,
            'link_boleto' => null,
            'gateway_payment_url' => null,
            'gateway_qr_code' => null,
            'pix_copy_paste' => null,
            'barcode' => null,
            'digitable_line' => null,
            'transaction_id' => null,
            'gateway_status' => null,
        ]);

        return back()->with('success', 'Dados do pagamento removidos. Gere um novo boleto ou PIX quando desejar.');
    }

    private function fetchMpAccountInfo($gateway): ?array
    {
        $token = $gateway->config['access_token'] ?? null;
        if (!$token) return null;

        $cacheKey = 'mp_account_' . md5($token);
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        $ch = curl_init('https://api.mercadopago.com/users/me');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
        ]);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!isset($response['id'])) return null;

        $info = [
            'name' => trim(($response['first_name'] ?? '') . ' ' . ($response['last_name'] ?? '')),
            'document_number' => $response['identification']['number'] ?? null,
            'document_type' => $response['identification']['type'] ?? null,
            'email' => $response['email'] ?? null,
            'phone' => $response['phone']['number'] ?? null,
            'address' => trim(($response['address']['address'] ?? '') . ' - ' . ($response['address']['city'] ?? '') . '/' . ($response['address']['state'] ?? '')),
            'zip_code' => $response['address']['zip_code'] ?? null,
        ];

        cache()->put($cacheKey, $info, 3600);
        return $info;
    }
}
