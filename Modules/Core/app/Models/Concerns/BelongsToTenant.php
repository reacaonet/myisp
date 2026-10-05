<?php

namespace Modules\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Branch;
use Modules\Core\Models\Company;
use Modules\Core\Services\TenantContext;

trait BelongsToTenant
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Catalogo compartilhado pela empresa inteira: plano, servidor, local de
     * estoque. Fica so na empresa.
     */
    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where($this->qualifyColumn('company_id'), $companyId);
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        return $query->where($this->qualifyColumn('branch_id'), $branchId);
    }

    /**
     * Funil que corta tambem pela filial. Use em tudo que e dado do cliente:
     * clientes, ordens de servico, contratos e financeiro.
     *
     * O corte por filial e opt-in por model e nao deriva da existencia da coluna
     * `branch_id`. Os planos sao gravados na Matriz e continuam valendo para as
     * lojas; restringir pelo branch_id deixaria a loja sem plano nenhum para
     * vender.
     *
     * Quem nao tem nenhuma filial em `branch_user` continua vendo a empresa
     * inteira, que e o caso do supervisor de matriz
     * (ver TenantContext::isBranchScoped).
     */
    public function scopeForTenant($query)
    {
        if (TenantContext::isCrossTenant()) {
            return $query;
        }

        $query = $this->scopeForCompany($query, TenantContext::companyId());

        $column = $this->tenantBranchColumn();

        if ($column !== null && TenantContext::isBranchScoped()) {
            $query->whereIn(
                $this->qualifyColumn($column),
                TenantContext::allowedBranchIds()
            );
        }

        return $query;
    }

    /**
     * Coluna que o corte por filial aplica. O padrao e null (catalogo, so
     * empresa); models de dado do cliente sobrescrevem.
     */
    protected function tenantBranchColumn(): ?string
    {
        return null;
    }

    public static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (! isset($model->company_id)) {
                $model->company_id = TenantContext::companyId();
            }

            if (in_array('branch_id', $model->getFillable(), true) && ! isset($model->branch_id)) {
                $model->branch_id = TenantContext::branchId();
            }
        });
    }
}
