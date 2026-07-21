<?php

namespace Modules\Billing\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class BillingServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Billing';
    protected string $nameLower = 'billing';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
