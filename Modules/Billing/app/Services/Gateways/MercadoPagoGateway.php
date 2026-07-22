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
            'payment_method_id' => 'bolbradesco',
            'date_of_expiration' => $this->getExpirationDate($invoice),
            'payer' => $this->buildPayer($invoice, $payerEmail),
        ];

        $response = $this->apiCall('/v1/payments', $payload, $accessToken);

        if (isset($response['id'])) {
            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'pending'),
                'boleto_numero' => (string) $response['id'],
                'link_boleto' => $response['transaction_details']['external_resource_url'] ?? null,
                'chave_boleto' => $response['point_of_interaction']['transaction_data']['ticket_url'] ?? null,
                'payment_url' => $response['transaction_details']['external_resource_url'] ?? null,
                'barcode' => $response['transaction_details']['barcode']['content'] ?? null,
                'digitable_line' => $response['transaction_details']['digitable_line'] ?? null,
            ]);

            return [
                'success' => true,
                'boleto_url' => $response['transaction_details']['external_resource_url'] ?? null,
                'id' => $response['id'],
            ];
        }

        $errorMsg = $response['message'] ?? ($response['errors'][0]['description'] ?? null);
        if (!$errorMsg) {
            $errorMsg = json_encode($response);
        }

        return [
            'success' => false,
            'error' => $errorMsg,
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
            'date_of_expiration' => $this->getExpirationDate($invoice),
            'payer' => $this->buildPayer($invoice, $payerEmail),
        ];

        $response = $this->apiCall('/v1/payments', $payload, $accessToken);

        if (isset($response['id'])) {
            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'pending'),
                'boleto_numero' => (string) $response['id'],
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

        $errorMsg = $response['message'] ?? ($response['errors'][0]['description'] ?? null);
        if (!$errorMsg) {
            $errorMsg = json_encode($response);
        }

        return [
            'success' => false,
            'error' => $errorMsg,
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

    private function getExpirationDate(Invoice $invoice): string
    {
        $dueDate = $invoice->due_date->copy()->setTime(23, 59, 59);
        $maxDate = now()->addDays(29)->setTime(23, 59, 59);

        if ($dueDate->isAfter($maxDate)) {
            $dueDate = $maxDate;
        }
        if ($dueDate->isBefore(now())) {
            $dueDate = now()->addDays(1)->setTime(23, 59, 59);
        }

        return $dueDate->format('Y-m-d\TH:i:s.000-03:00');
    }

    private function buildPayer(Invoice $invoice, string $email): array
    {
        $client = $invoice->client;
        if ($client->relationLoaded('addresses') === false) {
            $client->load('addresses');
        }
        $address = $client->addresses->first();

        $payer = [
            'email' => $email,
            'first_name' => explode(' ', $client->name ?? '')[0] ?? '',
            'last_name' => collect(explode(' ', $client->name ?? ''))->last() ?? '',
            'identification' => [
                'type' => strlen(preg_replace('/\D/', '', $client->document ?? '')) > 11 ? 'CNPJ' : 'CPF',
                'number' => preg_replace('/\D/', '', $client->document ?? ''),
            ],
            'address' => [
                'zip_code' => preg_replace('/\D/', '', $address->zipcode ?? ''),
                'street_name' => $address->street ?? '',
                'street_number' => $address->number ?? '0',
                'neighborhood' => $address->neighborhood ?? '',
                'city' => $address->city ?? '',
                'federal_unit' => $address->state ?? '',
            ],
        ];

        if (!empty($client->phone)) {
            $payer['phone'] = [
                'area_code' => preg_replace('/\D/', '', substr($client->phone, 1, 2)),
                'number' => preg_replace('/\D/', '', $client->phone),
            ];
        }

        return $payer;
    }

    private function apiCall(string $endpoint, array $data, string $token, string $method = 'POST'): array
    {
        $url = 'https://api.mercadopago.com' . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => $this->getConfig('ssl_verify', false),
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
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['error' => true, 'message' => "Erro de conexao: {$curlError}"];
        }

        $result = json_decode($response, true) ?? [];

        if ($httpCode >= 400) {
            $result['error'] = true;
            $result['message'] = $result['message'] ?? "Erro HTTP {$httpCode}: " . substr($response, 0, 500);
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
