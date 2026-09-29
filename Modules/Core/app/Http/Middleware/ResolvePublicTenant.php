<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Services\PublicTenantResolver;
use Modules\Core\Services\TenantContext;
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

        // visitante nao tem sessao autenticada: sem isso as consultas tenant-aware
        // cairiam na empresa padrao em vez da empresa do host acessado
        $companyId = PublicTenantResolver::companyId();

        if ($companyId && ! $request->user()) {
            session(['current_company_id' => $companyId]);

            TenantContext::forget();
        }

        return $next($request);
    }
}
