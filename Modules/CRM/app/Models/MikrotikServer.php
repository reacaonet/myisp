<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Concerns\BelongsToTenant;
use Modules\Core\Services\TenantContext;

class MikrotikServer extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = ['company_id', 'branch_id', 'name', 'ip', 'port', 'login', 'senha', 'type', 'is_active', 'notes'];

    protected $hidden = ['senha'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'port' => 'integer',
        ];
    }

    public function provisioningRecords()
    {
        return $this->hasMany(ProvisioningRecord::class);
    }

    public function backups()
    {
        return $this->hasMany(MikrotikBackup::class, 'server_id');
    }

    /**
     * Query sempre ancorada na empresa atual, salvo navegacao cross-tenant.
     */
    public static function scoped(?Builder $query = null): Builder
    {
        $query ??= static::query();

        if (! TenantContext::isCrossTenant()) {
            $query->forCompany(TenantContext::companyId());
        }

        return $query;
    }

    public static function findScoped(int $id): ?self
    {
        return static::scoped()->find($id);
    }

    public static function findScopedOrFail(int $id): self
    {
        return static::scoped()->findOrFail($id);
    }
}
