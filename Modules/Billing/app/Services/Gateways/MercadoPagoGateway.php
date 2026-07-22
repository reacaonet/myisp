<?php

namespace Modules\Billing\Services\Gateways;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\AbstractPaymentGateway;

class MercadoPagoGateway extends AbstractPaymentGateway
{
    public function getName(): string
    {
        return 'Mercado Pago';
    }

    public function supportsBoleto(): bool
    {
        return true;
    }

    public function supportsPix(): bool
    {
        return true;
    }

    public function supportsCreditCard(): bool
    {
        return true;
    }

    public function generateBoleto(Invoice $invoice): array
    {
        $accessToken = $this->getConfig('access_token');
        $payerEmail = $this->getConfig('payer_email', $invoice->client->email ?? '');

        $payload = [
            'transaction_amount' => (float) $invoice->total,
            'description' => "Fatura {$invoice->invoice_number}",
            'payment_method_id' => 'billet',
            'date_of_expiration' => $invoice->due_date->format('c'),
            'payer' => [
                'email' => $payerEmail,
                'first_name' => explode(' ', $invoice->client->name ?? '')[0] ?? '',
                'last_name' => collect(explode(' ', $invoice->client->name ?? ''))->last() ?? '',
                'identification' => [
                    'type' => strlen(preg_replace('/\D/', '', $invoice->client->document ?? '')) > 11 ? 'CNPJ' : 'CPF',
                    'number' => preg_replace('/\D/', '', $invoice->client->document ?? ''),
                ],
            ],
        ];

        $response = $this->apiCall('/v1/payments', $payload, $accessToken);

        if (isset($response['id'])) {
            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'pending'),
                'boleto_numero' => (string) $response['id'],
                'link_boleto' => $response['transaction_details']['external_resource_url'] ?? null,
                'chave_boleto' => $response['point_of_interaction']['transaction_data']['ticket_url'] ?? null,
                'payment_url' => $response['transaction_details']['external_resource_url'] ?? null,
            ]);

            return [
                'success' => true,
                'boleto_url' => $response['transaction_details']['external_resource_url'] ?? null,
                'id' => $response['id'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Erro ao gerar boleto via Mercado Pago',
        ];
    }

    public function generatePix(Invoice $invoice): array
    {
        $accessToken = $this->getConfig('access_token');
        $payerEmail = $this->getConfig('payer_email', $invoice->client->email ?? '');

        $payload = [
            'transaction_amount' => (float) $invoice->total,
            'description' => "Fatura {$invoice->invoice_number}",
            'payment_method_id' => 'pix',
            'date_of_expiration' => $invoice->due_date->format('c'),
            'payer' => [
                'email' => $payerEmail,
                'first_name' => explode(' ', $invoice->client->name ?? '')[0] ?? '',
                'last_name' => collect(explode(' ', $invoice->client->name ?? ''))->last() ?? '',
                'identification' => [
                    'type' => strlen(preg_replace('/\D/', '', $invoice->client->document ?? '')) > 11 ? 'CNPJ' : 'CPF',
                    'number' => preg_replace('/\D/', '', $invoice->client->document ?? ''),
                ],
            ],
        ];

        $response = $this->apiCall('/v1/payments', $payload, $accessToken);

        if (isset($response['id'])) {
            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'pending'),
                'qr_code' => $response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                'pix_copy_paste' => $response['point_of_interaction']['transaction_data']['qr_code'] ?? null,
                'payment_url' => $response['point_of_interaction']['transaction_data']['ticket_url'] ?? null,
            ]);

            return [
                'success' => true,
                'qr_code' => $response['point_of_interaction']['transaction_data']['qr_code_base64'] ?? null,
                'copy_paste' => $response['point_of_interaction']['transaction_data']['qr_code'] ?? null,
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Erro ao gerar PIX via Mercado Pago',
        ];
    }

    public function generateCreditCard(Invoice $invoice, array $cardData): array
    {
        $accessToken = $this->getConfig('access_token');

        $payload = [
            'transaction_amount' => (float) $invoice->total,
            'description' => "Fatura {$invoice->invoice_number}",
            'token' => $cardData['token'] ?? '',
            'installments' => $cardData['installments'] ?? 1,
            'payment_method_id' => $cardData['payment_method_id'] ?? 'visa',
            'payer' => [
                'email' => $this->getConfig('payer_email', $invoice->client->email ?? ''),
                'identification' => [
                    'type' => strlen(preg_replace('/\D/', '', $invoice->client->document ?? '')) > 11 ? 'CNPJ' : 'CPF',
                    'number' => preg_replace('/\D/', '', $invoice->client->document ?? ''),
                ],
            ],
        ];

        $response = $this->apiCall('/v1/payments', $payload, $accessToken);

        if (isset($response['id'])) {
            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'pending'),
            ]);

            return [
                'success' => true,
                'id' => $response['id'],
                'status' => $response['status'] ?? 'pending',
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Erro ao processar cartao via Mercado Pago',
        ];
    }

    public function checkStatus(Invoice $invoice): string
    {
        $accessToken = $this->getConfig('access_token');
        $response = $this->apiCall("/v1/payments/{$invoice->boleto_numero}", [], $accessToken, 'GET');
        return $this->mapStatus($response['status'] ?? 'unknown');
    }

    public function cancelPayment(Invoice $invoice): bool
    {
        $accessToken = $this->getConfig('access_token');
        $response = $this->apiCall("/v1/payments/{$invoice->boleto_numero}", [
            'status' => 'cancelled',
        ], $accessToken, 'PUT');

        return isset($response['status']) && $response['status'] === 'cancelled';
    }

    private function apiCall(string $endpoint, array $data, string $token, string $method = 'POST'): array
    {
        $url = ($this->getConfig('sandbox', true) ? 'https://api.mercadopago.com/sandbox' : 'https://api.mercadopago.com') . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_CUSTOMREQUEST => $method,
        ]);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            $result['error'] = true;
            $result['message'] = $result['message'] ?? "Erro HTTP {$httpCode}";
        }

        return $result;
    }

    private function mapStatus(string $mpStatus): string
    {
        return match ($mpStatus) {
            'approved' => 'paid',
            'pending', 'in_process', 'in_mediation' => 'pending',
            'cancelled', 'refunded', 'charged_back' => 'canceled',
            'expired' => 'overdue',
            default => 'pending',
        };
    }
}
