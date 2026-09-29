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

    public function scopeForCompany($query, ?int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        return $query->where('branch_id', $branchId);
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
