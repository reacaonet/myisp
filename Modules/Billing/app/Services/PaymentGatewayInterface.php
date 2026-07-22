<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentGateway;

interface PaymentGatewayInterface
{
    public function generateBoleto(Invoice $invoice): array;
    public function generatePix(Invoice $invoice): array;
    public function generateCreditCard(Invoice $invoice, array $cardData): array;
    public function checkStatus(Invoice $invoice): string;
    public function cancelPayment(Invoice $invoice): bool;
}
