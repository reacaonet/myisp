<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentGateway;
use Modules\Billing\Services\Gateways\MercadoPagoGateway;
use Modules\Billing\Services\Gateways\AsaasGateway;
use Modules\Billing\Services\Gateways\GerencianetGateway;

class PaymentService
{
    private static array $gatewayClasses = [
        'mercado-pago' => MercadoPagoGateway::class,
        'asaas' => AsaasGateway::class,
        'gerencianet' => GerencianetGateway::class,
    ];

    public static function getGateway(string $slug): ?PaymentGatewayInterface
    {
        $gateway = PaymentGateway::where('slug', $slug)->where('status', 'active')->first();
        if (!$gateway) return null;

        $class = self::$gatewayClasses[$slug] ?? null;
        if (!$class) return null;

        return new $class($gateway);
    }

    public static function forInvoice(Invoice $invoice): ?PaymentGatewayInterface
    {
        if (!$invoice->gateway_id) return null;

        $gateway = PaymentGateway::find($invoice->gateway_id);
        if (!$gateway) return null;

        $class = self::$gatewayClasses[$gateway->slug] ?? null;
        if (!$class) return null;

        return new $class($gateway);
    }

    public static function getActiveGateways()
    {
        return PaymentGateway::where('status', 'active')->orderBy('name')->get();
    }

    public static function getAllGateways()
    {
        return PaymentGateway::orderBy('name')->get();
    }
}
