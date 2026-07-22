<?php

namespace Modules\Billing\Services\Gateways;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\AbstractPaymentGateway;

class AsaasGateway extends AbstractPaymentGateway
{
    public function getName(): string
    {
        return 'Asaas';
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
        $apiKey = $this->getConfig('api_key');
        $baseUrl = $this->getBaseUrl();

        $customerId = $this->ensureCustomer($invoice, $apiKey, $baseUrl);
        if (!$customerId) {
            return ['success' => false, 'error' => 'Erro ao criar cliente no Asaas'];
        }

        $payload = [
            'customer' => $customerId,
            'billingType' => 'BOLETO',
            'value' => (float) $invoice->total,
            'dueDate' => $invoice->due_date->format('Y-m-d'),
            'description' => "Fatura {$invoice->invoice_number}",
        ];

        $response = $this->apiCall('/v3/payments', $payload, $apiKey, $baseUrl);

        if (isset($response['id'])) {
            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'PENDING'),
                'boleto_numero' => $response['id'],
                'link_boleto' => $response['bankSlipUrl'] ?? null,
                'chave_boleto' => $response['identificationField'] ?? null,
                'payment_url' => $response['bankSlipUrl'] ?? null,
            ]);

            return [
                'success' => true,
                'boleto_url' => $response['bankSlipUrl'] ?? null,
                'id' => $response['id'],
            ];
        }

        return [
            'success' => false,
            'error' => $response['errors'][0]['description'] ?? 'Erro ao gerar boleto via Asaas',
        ];
    }

    public function generatePix(Invoice $invoice): array
    {
        $apiKey = $this->getConfig('api_key');
        $baseUrl = $this->getBaseUrl();

        $customerId = $this->ensureCustomer($invoice, $apiKey, $baseUrl);
        if (!$customerId) {
            return ['success' => false, 'error' => 'Erro ao criar cliente no Asaas'];
        }

        $payload = [
            'customer' => $customerId,
            'billingType' => 'PIX',
            'value' => (float) $invoice->total,
            'dueDate' => $invoice->due_date->format('Y-m-d'),
            'description' => "Fatura {$invoice->invoice_number}",
        ];

        $response = $this->apiCall('/v3/payments', $payload, $apiKey, $baseUrl);

        if (isset($response['id'])) {
            $pixData = $this->apiCall("/v3/payments/{$response['id']}/pixQrCode", [], $apiKey, $baseUrl, 'GET');

            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'PENDING'),
                'qr_code' => $pixData['encodedImage'] ?? null,
                'pix_copy_paste' => $pixData['payload'] ?? null,
            ]);

            return [
                'success' => true,
                'qr_code' => $pixData['encodedImage'] ?? null,
                'copy_paste' => $pixData['payload'] ?? null,
            ];
        }

        return [
            'success' => false,
            'error' => $response['errors'][0]['description'] ?? 'Erro ao gerar PIX via Asaas',
        ];
    }

    public function generateCreditCard(Invoice $invoice, array $cardData): array
    {
        $apiKey = $this->getConfig('api_key');
        $baseUrl = $this->getBaseUrl();

        $customerId = $this->ensureCustomer($invoice, $apiKey, $baseUrl);
        if (!$customerId) {
            return ['success' => false, 'error' => 'Erro ao criar cliente no Asaas'];
        }

        $payload = [
            'customer' => $customerId,
            'billingType' => 'CREDIT_CARD',
            'value' => (float) $invoice->total,
            'dueDate' => $invoice->due_date->format('Y-m-d'),
            'description' => "Fatura {$invoice->invoice_number}",
            'creditCard' => [
                'holderName' => $cardData['holder_name'] ?? '',
                'number' => $cardData['number'] ?? '',
                'expiryMonth' => $cardData['expiry_month'] ?? '',
                'expiryYear' => $cardData['expiry_year'] ?? '',
                'ccv' => $cardData['ccv'] ?? '',
            ],
            'creditCardHolderInfo' => [
                'name' => $invoice->client->name ?? '',
                'email' => $invoice->client->email ?? '',
                'cpfCnpj' => preg_replace('/\D/', '', $invoice->client->document ?? ''),
                'postalCode' => '00000000',
                'addressNumber' => '0',
                'phone' => $invoice->client->phone ?? '',
            ],
            'installmentCount' => $cardData['installments'] ?? 1,
        ];

        $response = $this->apiCall('/v3/payments', $payload, $apiKey, $baseUrl);

        if (isset($response['id'])) {
            $this->saveInvoiceGatewayData($invoice, [
                'status' => $this->mapStatus($response['status'] ?? 'PENDING'),
            ]);

            return [
                'success' => true,
                'id' => $response['id'],
                'status' => $response['status'] ?? 'PENDING',
            ];
        }

        return [
            'success' => false,
            'error' => $response['errors'][0]['description'] ?? 'Erro ao processar cartao via Asaas',
        ];
    }

    public function checkStatus(Invoice $invoice): string
    {
        $apiKey = $this->getConfig('api_key');
        $baseUrl = $this->getBaseUrl();
        $response = $this->apiCall("/v3/payments/{$invoice->boleto_numero}", [], $apiKey, $baseUrl, 'GET');
        return $this->mapStatus($response['status'] ?? 'UNKNOWN');
    }

    public function cancelPayment(Invoice $invoice): bool
    {
        $apiKey = $this->getConfig('api_key');
        $baseUrl = $this->getBaseUrl();
        $response = $this->apiCall("/v3/payments/{$invoice->boleto_numero}", [], $apiKey, $baseUrl, 'DELETE');
        return !isset($response['errors']);
    }

    private function ensureCustomer(Invoice $invoice, string $apiKey, string $baseUrl): ?string
    {
        $client = $invoice->client;
        if (!$client) return null;

        $document = preg_replace('/\D/', '', $client->document ?? '');

        $searchUrl = $baseUrl . '/v3/customers?cpfCnpj=' . $document;
        $ch = curl_init($searchUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['access_token: ' . $apiKey],
        ]);
        $searchResult = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (!empty($searchResult['data'][0]['id'])) {
            return $searchResult['data'][0]['id'];
        }

        $payload = [
            'name' => $client->name ?? '',
            'cpfCnpj' => $document,
            'email' => $client->email ?? '',
            'phone' => $client->phone ?? '',
            'mobilePhone' => $client->cellphone ?? '',
        ];

        $response = $this->apiCall('/v3/customers', $payload, $apiKey, $baseUrl);
        return $response['id'] ?? null;
    }

    private function getBaseUrl(): string
    {
        return $this->getConfig('sandbox', true)
            ? 'https://sandbox.asaas.com/api'
            : 'https://api.asaas.com';
    }

    private function apiCall(string $endpoint, array $data, string $apiKey, string $baseUrl, string $method = 'POST'): array
    {
        $url = $baseUrl . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . $apiKey,
            ],
            CURLOPT_CUSTOMREQUEST => $method,
        ]);

        if ($method !== 'GET' && $method !== 'DELETE') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    private function mapStatus(string $asaasStatus): string
    {
        return match ($asaasStatus) {
            'RECEIVED', 'CONFIRMED', 'CREDIT_DONE' => 'paid',
            'PENDING', 'AWAITING_RISK_ANALYSIS' => 'pending',
            'OVERDUE' => 'overdue',
            'CANCELLED', 'REFUNDED', 'RECEIVED_IN_CASH' => 'canceled',
            default => 'pending',
        };
    }
}
