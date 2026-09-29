<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\PaymentGateway;
use Modules\Billing\Services\Gateways\AsaasGateway;
use Modules\Billing\Services\Gateways\GerencianetGateway;
use Modules\Billing\Services\Gateways\MercadoPagoGateway;
use Modules\Core\Services\TenantContext;

class PaymentService
{
    private static array $gatewayClasses = [
        'mercado-pago' => MercadoPagoGateway::class,
        'asaas' => AsaasGateway::class,
        'gerencianet' => GerencianetGateway::class,
    ];

    public static function getGateway(string $slug, ?int $companyId = null): ?PaymentGatewayInterface
    {
        $gateway = self::forCompany(PaymentGateway::where('slug', $slug)->where('status', 'active'), $companyId)->first();
        if (! $gateway) {
            return null;
        }

        $class = self::$gatewayClasses[$slug] ?? null;
        if (! $class) {
            return null;
        }

        return new $class($gateway);
    }

    public static function forInvoice(Invoice $invoice): ?PaymentGatewayInterface
    {
        if (! $invoice->gateway_id) {
            return null;
        }

        $gateway = PaymentGateway::find($invoice->gateway_id);
        if (! $gateway) {
            return null;
        }

        $class = self::$gatewayClasses[$gateway->slug] ?? null;
        if (! $class) {
            return null;
        }

        return new $class($gateway);
    }

    public static function getActiveGateways(?int $companyId = null)
    {
        return self::forCompany(PaymentGateway::where('status', 'active')->orderBy('name'), $companyId)->get();
    }

    public static function getAllGateways(?int $companyId = null)
    {
        return self::forCompany(PaymentGateway::orderBy('name'), $companyId)->get();
    }

    protected static function forCompany($query, ?int $companyId = null)
    {
        if (TenantContext::isCrossTenant()) {
            return $query;
        }

        return $query->forCompany($companyId ?? TenantContext::companyId());
    }
}
