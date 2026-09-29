<?php

namespace Modules\Core\Services;

use Illuminate\Http\Request;
use Modules\Core\Models\Company;

/**
 * Resolve a empresa publica a partir do host acessado.
 *
 * Regras: host de empresa registrada vence; senao cai na matriz. Usuario
 * autenticado tem prioridade, porque o contexto dele ja foi escolhido
 * explicitamente pelo seletor do layout.
 */
class PublicTenantResolver
{
    protected static ?int $resolvedCompanyId = null;

    protected static bool $resolved = false;

    public static function resolveFor(Request $request): void
    {
        if (static::$resolved) {
            return;
        }

        static::$resolved = true;

        if ($request->user()) {
            return;
        }

        $host = strtolower($request->getHost());
        $port = (int) $request->getPort();

        if ($port !== 80 && $port !== 443) {
            $host = str_contains($host, ':') ? explode(':', $host)[0] : $host;
        }

        $host = preg_replace('/^www\./', '', $host) ?? $host;

        if ($host === '' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return;
        }

        $company = static::matchHost($host);

        if ($company) {
            static::$resolvedCompanyId = $company->id;
        }
    }

    public static function matchHost(string $host): ?Company
    {
        return Company::query()
            ->whereNotNull('domain')
            ->whereRaw('lower(domain) = ?', [$host])
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    /** Id resolvido pelo host, ou null quando a request nao define tenant. */
    public static function companyId(): ?int
    {
        return static::$resolvedCompanyId;
    }

    public static function forget(): void
    {
        static::$resolvedCompanyId = null;
        static::$resolved = false;
    }
}
