<?php

namespace Modules\Billing\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Modules\Billing\Console\Commands\GenerateInvoices;
use Modules\Billing\Console\Commands\CheckOverdueAndBlock;
use Modules\Billing\Console\Commands\UnblockPaid;

class BillingServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Billing';
    protected string $nameLower = 'billing';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->commands([
            GenerateInvoices::class,
            CheckOverdueAndBlock::class,
            UnblockPaid::class,
        ]);
    }
}
