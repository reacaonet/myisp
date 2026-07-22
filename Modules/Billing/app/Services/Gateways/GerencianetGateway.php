<?php

namespace Modules\Billing\Services\Gateways;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Services\AbstractPaymentGateway;

class GerencianetGateway extends AbstractPaymentGateway
{
    public function getName(): string
    {
        return 'Gerencianet (Efí)';
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
        return false;
    }

    public function generateBoleto(Invoice $invoice): array
    {
        $token = $this->authenticate();
        if (!$token) {
            return ['success' => false, 'error' => 'Erro de autenticacao na Gerencianet'];
        }

        $payload = [
            'payment' => [
                'banking_billet' => [
                    'expire_at' => $invoice->due_date->format('Y-m-d'),
                    'customer' => [
                        'name' => $invoice->client->name ?? '',
                        'email' => $invoice->client->email ?? '',
                        'address' => [
                            'street' => 'Endereco nao informado',
                            'number' => '0',
                            'neighborhood' => 'Centro',
                            'zipcode' => '00000000',
                            'city' => 'Sao Paulo',
                            'state' => 'SP',
                        ],
                        'phone_number' => $invoice->client->phone ?? '',
                    ],
                    'message' => "Fatura {$invoice->invoice_number}",
                ],
                'items' => [
                    [
                        'name' => "Fatura {$invoice->invoice_number}",
                        'amount' => 1,
                        'value' => (int) ($invoice->total * 100),
                    ],
                ],
            ],
        ];

        $response = $this->apiCall('/v1/charge', $payload, $token);

        if (isset($response['data']['charge']['code'])) {
            $chargeCode = $response['data']['charge']['code'];

            $linkResponse = $this->apiCall("/v1/charge/{$chargeCode}/pdf", [], $token, 'GET');

            $this->saveInvoiceGatewayData($invoice, [
                'status' => 'pending',
                'boleto_numero' => (string) $chargeCode,
                'link_boleto' => $linkResponse['data'] ?? null,
                'chave_boleto' => (string) $chargeCode,
            ]);

            return [
                'success' => true,
                'boleto_url' => $linkResponse['data'] ?? null,
                'id' => $chargeCode,
            ];
        }

        return [
            'success' => false,
            'error' => $response['error_description'] ?? 'Erro ao gerar boleto via Gerencianet',
        ];
    }

    public function generatePix(Invoice $invoice): array
    {
        $token = $this->authenticate();
        if (!$token) {
            return ['success' => false, 'error' => 'Erro de autenticacao na Gerencianet'];
        }

        $payload = [
            'payment' => [
                'pix' => [
                    'expiration_time' => 3600,
                    'customer' => [
                        'name' => $invoice->client->name ?? '',
                        'email' => $invoice->client->email ?? '',
                        'phone_number' => $invoice->client->phone ?? '',
                    ],
                ],
                'items' => [
                    [
                        'name' => "Fatura {$invoice->invoice_number}",
                        'amount' => 1,
                        'value' => (int) ($invoice->total * 100),
                    ],
                ],
            ],
        ];

        $response = $this->apiCall('/v1/charge', $payload, $token);

        if (isset($response['data']['charge']['code'])) {
            $chargeCode = $response['data']['charge']['code'];
            $pixData = $response['data']['charge']['pix'] ?? [];

            $this->saveInvoiceGatewayData($invoice, [
                'status' => 'pending',
                'qr_code' => $pixData['qrcode'] ?? null,
                'pix_copy_paste' => $pixData['qrcode_image'] ?? null,
            ]);

            return [
                'success' => true,
                'qr_code' => $pixData['qrcode_image'] ?? null,
                'copy_paste' => $pixData['qrcode'] ?? null,
            ];
        }

        return [
            'success' => false,
            'error' => $response['error_description'] ?? 'Erro ao gerar PIX via Gerencianet',
        ];
    }

    public function generateCreditCard(Invoice $invoice, array $cardData): array
    {
        return [
            'success' => false,
            'error' => 'Gerencianet nao suporta pagamento com cartao de credito',
        ];
    }

    public function checkStatus(Invoice $invoice): string
    {
        $token = $this->authenticate();
        if (!$token) return 'pending';

        $response = $this->apiCall("/v1/charge/{$invoice->boleto_numero}", [], $token, 'GET');
        $gnStatus = $response['data']['charge']['status'] ?? 'unknown';

        return match ($gnStatus) {
            'new', 'waiting' => 'pending',
            'pay' => 'paid',
            'refund', 'refunded' => 'canceled',
            'failed', 'canceled' => 'overdue',
            default => 'pending',
        };
    }

    public function cancelPayment(Invoice $invoice): bool
    {
        $token = $this->authenticate();
        if (!$token) return false;

        $response = $this->apiCall("/v1/charge/{$invoice->boleto_numero}/cancel", [], $token);
        return isset($response['data']['charge']['status']) && $response['data']['charge']['status'] === 'canceled';
    }

    private function authenticate(): ?string
    {
        $clientId = $this->getConfig('client_id');
        $clientSecret = $this->getConfig('client_secret');
        $baseUrl = $this->getBaseUrl();

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $baseUrl . '/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'client_credentials',
            ]),
            CURLOPT_USERPWD => $clientId . ':' . $clientSecret,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response['access_token'] ?? null;
    }

    private function getBaseUrl(): string
    {
        return $this->getConfig('sandbox', true)
            ? 'https://sandbox.gerencianet.com.br/v1'
            : 'https://api.gerencianet.com.br/v1';
    }

    private function apiCall(string $endpoint, array $data, string $token, string $method = 'POST'): array
    {
        $url = $this->getBaseUrl() . $endpoint;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => $this->getConfig('ssl_verify', false),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token,
                'Access-Token: ' . $token,
            ],
            CURLOPT_CUSTOMREQUEST => $method,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }
}
