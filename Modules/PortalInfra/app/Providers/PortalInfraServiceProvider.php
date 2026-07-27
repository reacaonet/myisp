<?php

namespace Modules\PortalInfra\Providers;

use Illuminate\Support\Facades\View;
use Nwidart\Modules\Support\ModuleServiceProvider;

class PortalInfraServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'PortalInfra';

    protected string $nameLower = 'portal-infra';

    protected array $providers = [
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        View::addNamespace('infra', module_path($this->name, '/resources/views'));
    }
}
