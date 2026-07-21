<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $instance;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.url', '');
        $this->apiKey = config('services.whatsapp.api_key', '');
        $this->instance = config('services.whatsapp.instance', '');
    }

    public function send(string $phone, string $message): bool
    {
        if (empty($this->apiUrl)) {
            Log::info('[WhatsApp] Mock send to ' . $phone . ': ' . $message);
            return true;
        }

        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->post($this->apiUrl . '/message/send', [
            'instance' => $this->instance,
            'to' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }

    public function notifyOverdue(array $client, float $amount, string $dueDate): bool
    {
        $phone = preg_replace('/[^0-9]/', '', $client['cellphone'] ?? $client['phone'] ?? '');
        if (empty($phone)) {
            return false;
        }

        $message = "Olá {$client['name']}, sua fatura de R$ "
            . number_format($amount, 2, ',', '.')
            . " vencida em {$dueDate} está pendente. "
            . "Evite a suspensão do seu acesso. Entre em contato para regularizar.";

        return $this->send($phone, $message);
    }
}
