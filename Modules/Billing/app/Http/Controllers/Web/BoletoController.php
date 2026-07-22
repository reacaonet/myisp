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

        return view('billing::boletos.print', compact('invoice', 'bankSettings'));
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

        return back()->with('error', 'Erro ao gerar boleto: ' . ($result['error'] ?? 'Erro desconhecido'));
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

        return back()->with('error', 'Erro ao gerar PIX: ' . ($result['error'] ?? 'Erro desconhecido'));
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
