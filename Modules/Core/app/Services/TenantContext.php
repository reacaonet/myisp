<?php

namespace Modules\Core\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\SystemSetting;

class TenantContext
{
    protected static bool $resolved = false;

    /** Usuario do contexto resolvido (evita cache crossover em teste/Octane). */
    protected static ?string $resolvedUser = null;

    /** Host publico que o contexto resolvido representa (idempotencia por request). */
    protected static ?int $resolvedPublicCompanyId = null;

    protected static ?int $companyId = null;

    protected static ?int $branchId = null;

    /**
     * Usuario operador do request.
     *
     * O guard padrao (`web`) nao enxerga o portal do tecnico, que autentica no
     * guard `technician` com o mesmo model `User`. Sem esta preferencia o
     * contexto do tecnico caia no ramo anonimo e resolvia para a empresa
     * publica/raiz — ou seja, `forTenant()` nao cortava nada dentro daquele
     * portal, que e o caso do desenho FTTH em `tecnico/ftth`.
     *
     * O guard `client` fica de fora de proposito: ele autentica `Client`, que nao
     * tem vinculo em `company_user`/`branch_user`, e o portal do cliente depende
     * de enxergar os proprios dados. Ver `docs/AUDITORIA-ESCOPO-TENANT.md`.
     *
     * Quando o mesmo navegador tem os dois guards abertos, o `web` ganha: e a
     * sessao administrativa que controla o contexto e o seletor de empresa.
     */
    protected static function authenticatedUser(): ?Authenticatable
    {
        return Auth::guard('web')->user() ?? Auth::guard('technician')->user();
    }

    public static function resolve(): void
    {
        $user = self::authenticatedUser();
        $userKey = $user?->getAuthIdentifier() !== null
            ? get_class($user).':'.$user->getAuthIdentifier()
            : null;

        // o host publico entra na chave: um contexto resolvido antes do
        // request (hooks, seeders) nao pode contaminar a resolucao por host
        $publicCompanyId = $user ? null : PublicTenantResolver::companyId();

        if (
            self::$resolved
            && self::$resolvedUser === $userKey
            && self::$resolvedPublicCompanyId === $publicCompanyId
        ) {
            return;
        }

        if (self::$resolved) {
            self::forget();
        }

        self::$resolved = true;
        self::$resolvedUser = $userKey;
        self::$resolvedPublicCompanyId = $publicCompanyId;

        if (! $user) {
            [$company, $branch] = $publicCompanyId
                ? self::scopeFor($publicCompanyId)
                : self::rootScope();

            self::$companyId = $company;
            self::$branchId = $branch;

            return;
        }

        if (! method_exists($user, 'companies') || ! method_exists($user, 'branches')) {
            [$company, $branch] = self::rootScope();

            self::$companyId = $company;
            self::$branchId = $branch;

            return;
        }

        $crossTenant = self::isSuperadmin($user);

        $allowedCompanies = $crossTenant
            ? Company::orderBy('id')->pluck('id')->all()
            : $user->companies()->pluck('companies.id')->all();

        $sessionCompany = (int) session('current_company_id');

        if ($sessionCompany && in_array($sessionCompany, $allowedCompanies, true)) {
            self::$companyId = $sessionCompany;
        } elseif (count($allowedCompanies) > 0) {
            self::$companyId = (int) $allowedCompanies[0];
        }

        if (self::$companyId) {
            $allowedBranches = $crossTenant
                ? Branch::where('company_id', self::$companyId)->orderBy('id')->pluck('id')->all()
                : Branch::where('company_id', self::$companyId)
                    ->whereIn('id', $user->branches()->pluck('branches.id'))
                    ->orderBy('id')
                    ->pluck('id')
                    ->all();

            $sessionBranch = (int) session('current_branch_id');

            if ($sessionBranch && in_array($sessionBranch, $allowedBranches, true)) {
                self::$branchId = $sessionBranch;
            } elseif (count($allowedBranches) > 0) {
                self::$branchId = (int) $allowedBranches[0];
            }
        }
    }

    public static function companyId(): ?int
    {
        self::resolve();

        return self::$companyId;
    }

    public static function branchId(): ?int
    {
        self::resolve();

        return self::$branchId;
    }

    public static function company(): ?Company
    {
        $id = self::companyId();

        return $id ? Company::find($id) : null;
    }

    public static function branch(): ?Branch
    {
        $id = self::branchId();

        return $id ? Branch::find($id) : null;
    }

    public static function allowedCompanyIds(?Authenticatable $user = null): array
    {
        $user ??= self::authenticatedUser();

        if (! $user || ! method_exists($user, 'companies')) {
            return [self::companyId()];
        }

        if (self::isSuperadmin($user)) {
            return Company::orderBy('id')->pluck('id')->all();
        }

        return $user->companies()->pluck('companies.id')->all();
    }

    public static function isSuperadmin(?Authenticatable $user = null): bool
    {
        $user ??= self::authenticatedUser();

        if (! $user || ! method_exists($user, 'group')) {
            return false;
        }

        return ($user->group?->slug ?? null) === 'superadmin';
    }

    /** Filiais que o usuario logado pode assumir. */
    public static function allowedBranchIds(?Authenticatable $user = null): array
    {
        $user ??= self::authenticatedUser();

        if (! $user || ! method_exists($user, 'branches')) {
            $id = self::branchId();

            return $id ? [$id] : [];
        }

        if (self::isSuperadmin($user)) {
            return Branch::orderBy('id')->pluck('id')->all();
        }

        return $user->branches()->pluck('branches.id')->all();
    }

    public static function isCrossTenant(): bool
    {
        return self::isSuperadmin();
    }

    /**
     * O vinculo em `branch_user` restringe ate onde o usuario enxerga dentro da
     * empresa. Sem nenhum vinculo de filial a empresa vale inteira: e assim que
     * um supervisor de matriz continua alcancando todas as lojas.
     */
    public static function isBranchScoped(?Authenticatable $user = null): bool
    {
        $user ??= self::authenticatedUser();

        if (! $user || self::isSuperadmin($user) || ! method_exists($user, 'branches')) {
            return false;
        }

        return $user->branches()->exists();
    }

    protected static function scopeFor(int $companyId): array
    {
        $branch = Branch::query()
            ->where('company_id', $companyId)
            ->whereNull('parent_id')
            ->orderBy('id')
            ->first();

        return [$companyId, $branch?->id];
    }

    protected static function rootScope(): array
    {
        $company = Company::query()->whereNull('parent_id')->orderBy('id')->first();
        $branch = $company
            ? Branch::query()->where('company_id', $company->id)->whereNull('parent_id')->orderBy('id')->first()
            : null;

        return [$company?->id, $branch?->id];
    }

    public static function forget(): void
    {
        self::$resolved = false;
        self::$resolvedUser = null;
        self::$resolvedPublicCompanyId = null;
        self::$companyId = null;
        self::$branchId = null;

        SystemSetting::forget();
    }
}
