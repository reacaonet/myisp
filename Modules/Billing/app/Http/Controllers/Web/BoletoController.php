<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentGateway;
use Modules\Billing\Services\PaymentService;
use Modules\Core\Models\SystemSetting;

class BoletoController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('client', 'contract.plan', 'gateway');

        if ($search = $request->get('search')) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('invoice_number', 'like', "%{$search}%");
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->orderBy('due_date')->paginate(20);
        $gateways = PaymentGateway::where('status', 'active')->get();

        $bankSettings = [
            'bank' => SystemSetting::get('bank_name', 'Banco do Brasil'),
            'agency' => SystemSetting::get('bank_agency', ''),
            'account' => SystemSetting::get('bank_account', ''),
            'company' => SystemSetting::get('company_name', 'Minha ISP'),
            'cnpj' => SystemSetting::get('company_document', ''),
        ];

        return view('billing::boletos.index', compact('invoices', 'gateways', 'bankSettings'));
    }

    public function print($id)
    {
        $invoice = Invoice::with('client', 'gateway')->findOrFail($id);

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

        return view('billing::boletos.print', compact('invoice', 'bankSettings', 'mpAccount'));
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

    public function generateBoleto(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $gateway = PaymentGateway::find($validated['gateway_id']);
        $service = PaymentService::getGateway($gateway->slug);

        if (!$service) {
            return back()->with('error', 'Gateway nao encontrado ou inativo.');
        }

        if (!$service->supportsBoleto()) {
            return back()->with('error', "O gateway {$gateway->name} nao suporta geração de boleto.");
        }

        $result = $service->generateBoleto($invoice);

        if ($result['success']) {
            return back()->with('success', 'Boleto gerado com sucesso via ' . $gateway->name . '.');
        }

        return back()->with('error', 'Erro ao gerar boleto: ' . ($result['error'] ?? json_encode($result) ?? 'Erro desconhecido'));
    }

    public function generatePix(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);

        $validated = $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $gateway = PaymentGateway::find($validated['gateway_id']);
        $service = PaymentService::getGateway($gateway->slug);

        if (!$service) {
            return back()->with('error', 'Gateway nao encontrado ou inativo.');
        }

        if (!$service->supportsPix()) {
            return back()->with('error', "O gateway {$gateway->name} nao suporta pagamento via PIX.");
        }

        $result = $service->generatePix($invoice);

        if ($result['success']) {
            return back()->with('success', 'PIX gerado com sucesso via ' . $gateway->name . '.');
        }

        return back()->with('error', 'Erro ao gerar PIX: ' . ($result['error'] ?? json_encode($result) ?? 'Erro desconhecido'));
    }

    public function refreshStatus($id)
    {
        $invoice = Invoice::findOrFail($id);

        if (!$invoice->gateway_id || !$invoice->boleto_numero) {
            return back()->with('error', 'Nenhum gateway vinculado a esta fatura.');
        }

        $service = PaymentService::forInvoice($invoice);

        if (!$service) {
            return back()->with('error', 'Gateway nao encontrado.');
        }

        $newStatus = $service->checkStatus($invoice);

        $invoice->update([
            'status' => $newStatus,
            'gateway_status' => $newStatus,
            'paid_date' => $newStatus === 'paid' ? now() : $invoice->paid_date,
        ]);

        return back()->with('success', "Status atualizado: {$newStatus}");
    }

    public function cancelPayment($id)
    {
        $invoice = Invoice::findOrFail($id);

        if (!$invoice->gateway_id || !$invoice->boleto_numero) {
            return back()->with('error', 'Nenhum gateway vinculado a esta fatura.');
        }

        $service = PaymentService::forInvoice($invoice);

        if (!$service) {
            return back()->with('error', 'Gateway nao encontrado.');
        }

        $result = $service->cancelPayment($invoice);

        if ($result) {
            $invoice->update(['status' => 'canceled', 'gateway_status' => 'cancelled']);
            return back()->with('success', 'Pagamento cancelado com sucesso.');
        }

        return back()->with('error', 'Erro ao cancelar pagamento.');
    }
}
