<?php

namespace Modules\CRM\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class CRMServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'CRM';
    protected string $nameLower = 'crm';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
