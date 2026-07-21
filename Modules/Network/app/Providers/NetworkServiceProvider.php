<?php

namespace Modules\Network\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class NetworkServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Network';
    protected string $nameLower = 'network';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
