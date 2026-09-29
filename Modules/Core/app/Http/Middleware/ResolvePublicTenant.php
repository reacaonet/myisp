<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Services\PublicTenantResolver;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aponta as paginas publicas para a empresa do host acessado.
 */
class ResolvePublicTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // resolve a cada request: o processo e compartilhado entre requisicoes
        PublicTenantResolver::forget();
        PublicTenantResolver::resolveFor($request);


        return $next($request);
    }
}
