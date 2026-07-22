<?php

namespace Modules\Billing\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\CashBookEntry;
use Modules\Billing\Models\PaymentGateway;
use Modules\CRM\Services\MikrotikService;

class WebhookController extends Controller
{
    public function mercadoPago(Request $request)
    {
        $gateway = PaymentGateway::where('slug', 'mercado-pago')->where('status', 'active')->first();
        if (!$gateway) {
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        $accessToken = $gateway->getConfigValue('access_token');
        $authorizationHeader = $request->header('Authorization');

        if ($authorizationHeader !== 'Bearer ' . $accessToken) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $paymentId = $request->input('data.id') ?? $request->input('payment_id');
        if (!$paymentId) {
            return response()->ok();
        }

        $response = $this->fetchPaymentFromMP($paymentId, $accessToken);
        if (!$response || isset($response['error'])) {
            return response()->ok();
        }

        $mpStatus = $response['status'] ?? 'unknown';
        $paymentIdStr = (string) ($response['id'] ?? $paymentId);

        if ($mpStatus === 'approved') {
            $invoice = Invoice::where('boleto_numero', $paymentIdStr)->first();
            if ($invoice && $invoice->status !== 'paid') {
                $totalPaid = $invoice->payments()->sum('amount') + ($response['transaction_amount'] ?? $invoice->total);
                $paymentDate = isset($response['date_approved']) ? \Carbon\Carbon::parse($response['date_approved'])->toDateString() : now()->toDateString();

                $invoice->payments()->create([
                    'amount' => $response['transaction_amount'] ?? $invoice->total,
                    'payment_date' => $paymentDate,
                    'payment_method' => 'pix',
                    'transaction_id' => $paymentIdStr,
                    'notes' => 'Mercado Pago IPN - approved',
                ]);

                CashBookEntry::create([
                    'type' => 'entrada',
                    'amount' => $response['transaction_amount'] ?? $invoice->total,
                    'description' => "Pagamento fatura {$invoice->invoice_number}",
                    'category' => 'pagamento',
                    'entry_date' => $paymentDate,
                    'reference' => $paymentIdStr,
                    'payment_method' => 'pix',
                    'notes' => 'Mercado Pago IPN',
                    'invoice_id' => $invoice->id,
                ]);

                if ($totalPaid >= $invoice->total) {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_date' => $paymentDate,
                        'payment_method' => 'pix',
                        'transaction_id' => $paymentIdStr,
                        'gateway_status' => 'approved',
                        'auto_blocked' => false,
                    ]);

                    $this->tryUnblockContract($invoice);
                } else {
                    $invoice->update(['gateway_status' => 'approved']);
                }
            }
        } elseif (in_array($mpStatus, ['cancelled', 'refunded', 'charged_back'])) {
            $invoice = Invoice::where('boleto_numero', $paymentIdStr)->first();
            if ($invoice) {
                $invoice->update(['gateway_status' => $mpStatus]);
            }
        }

        return response()->ok();
    }

    private function fetchPaymentFromMP(string $paymentId, string $accessToken): ?array
    {
        $url = "https://api.mercadopago.com/v1/payments/{$paymentId}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? null;
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
                    }
                }
            }
        }
    }
}
