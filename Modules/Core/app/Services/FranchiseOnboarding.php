<?php

namespace Modules\Core\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserGroup;

class FranchiseOnboarding
{
    /**
     * Cadastro de franquia (Fase 3): cria a compania, a filial Matriz e,
     * quando informado, o usuario administrador com acesso a compania e a Matriz.
     *
     * @param  array<string, mixed>  $companyData
     * @param  array<string, mixed>  $matrixData
     * @param  array<string, mixed>  $adminData
     * @return array{company: Company, matrix: Branch, admin: ?User, temporary_password: ?string}
     */
    public function register(array $companyData, array $matrixData = [], array $adminData = []): array
    {
        return DB::transaction(function () use ($companyData, $matrixData, $adminData) {
            $company = Company::create($companyData);

            $matrix = Branch::create([
                'company_id' => $company->id,
                'parent_id' => null,
                'code' => $this->value($matrixData, 'code', 'MAT'),
                'name' => $this->value($matrixData, 'name', 'Matriz'),
                'is_active' => true,
            ]);

            $invited = $this->inviteAdmin($company, $matrix, $adminData);

            return [
                'company' => $company,
                'matrix' => $matrix,
                'admin' => $invited['admin'],
                'temporary_password' => $invited['temporary_password'],
            ];
        });
    }

    /**
     * Cria (ou reaproveita) o administrador da franquia e o vincula a compania e a Matriz.
     * Sem senha informada, gera uma senha temporaria exibida uma unica vez ao operador.
     *
     * @param  array<string, mixed>  $adminData
     * @return array{admin: ?User, temporary_password: ?string}
     */
    public function inviteAdmin(Company $company, ?Branch $matrix = null, array $adminData = []): array
    {
        $email = $adminData['email'] ?? null;

        if (blank($email)) {
            return ['admin' => null, 'temporary_password' => null];
        }

        $matrix ??= $company->branches()->orderBy('id')->first();
        $group = $this->resolveGroup($adminData['user_group_id'] ?? null);

        $password = $adminData['password'] ?? null;
        $temporaryPassword = filled($password) ? null : $this->generatePassword();

        $admin = User::firstOrNew(['email' => $email]);
        $admin->fill([
            'name' => $this->value($adminData, 'name', Str::before($email, '@')),
            'phone' => $adminData['phone'] ?? null,
            'user_group_id' => $group->id,
            'role' => $group->slug,
            'is_active' => true,
        ]);
        $admin->password = $password ?: $temporaryPassword;
        $admin->must_change_password = $temporaryPassword !== null || (bool) ($adminData['must_change_password'] ?? false);
        $admin->save();

        $admin->companies()->syncWithoutDetaching([$company->id]);

        if ($matrix) {
            $admin->branches()->syncWithoutDetaching([$matrix->id]);
        }

        return ['admin' => $admin->fresh(), 'temporary_password' => $temporaryPassword];
    }

    protected function resolveGroup(?int $groupId): UserGroup
    {
        if ($groupId) {
            $group = UserGroup::find($groupId);

            if ($group) {
                return $group;
            }
        }

        return UserGroup::where('slug', 'admin')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function value(array $data, string $key, string $default): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? trim($value) : $default;
    }

    protected function generatePassword(): string
    {
        return Str::password(10, symbols: false);
    }
}
