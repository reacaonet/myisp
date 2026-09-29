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

    protected static ?int $companyId = null;

    protected static ?int $branchId = null;

    public static function resolve(): void
    {
        $user = Auth::user();
        $userKey = $user?->getAuthIdentifier() !== null
            ? get_class($user).':'.$user->getAuthIdentifier()
            : null;

        if (self::$resolved && self::$resolvedUser === $userKey) {
            return;
        }

        if (self::$resolved) {
            self::forget();
        }

        self::$resolved = true;
        self::$resolvedUser = $userKey;

        if (! $user || ! method_exists($user, 'companies') || ! method_exists($user, 'branches')) {
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
        $user ??= Auth::user();

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
        $user ??= Auth::user();

        if (! $user || ! method_exists($user, 'group')) {
            return false;
        }

        return ($user->group?->slug ?? null) === 'superadmin';
    }

    public static function isCrossTenant(): bool
    {
        return self::isSuperadmin();
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
        self::$companyId = null;
        self::$branchId = null;

        SystemSetting::forget();
    }
}
