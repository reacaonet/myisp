<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentGateway;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    protected PaymentGateway $gateway;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function getGateway(): PaymentGateway
    {
        return $this->gateway;
    }

    public function getConfig(string $key, $default = null)
    {
        return $this->gateway->getConfigValue($key, $default);
    }

    abstract public function getName(): string;

    abstract public function supportsBoleto(): bool;

    abstract public function supportsPix(): bool;

    abstract public function supportsCreditCard(): bool;

    protected function saveInvoiceGatewayData(Invoice $invoice, array $data): void
    {
        $invoice->update([
            'gateway_id' => $this->gateway->id,
            'gateway_status' => $data['status'] ?? 'pending',
            'gateway_payment_url' => $data['payment_url'] ?? null,
            'gateway_qr_code' => $data['qr_code'] ?? null,
            'pix_copy_paste' => $data['pix_copy_paste'] ?? null,
            'boleto_numero' => $data['boleto_numero'] ?? $invoice->boleto_numero,
            'link_boleto' => $data['link_boleto'] ?? $invoice->link_boleto,
            'chave_boleto' => $data['chave_boleto'] ?? $invoice->chave_boleto,
            'barcode' => $data['barcode'] ?? $invoice->barcode,
            'digitable_line' => $data['digitable_line'] ?? $invoice->digitable_line,
        ]);
    }
}
