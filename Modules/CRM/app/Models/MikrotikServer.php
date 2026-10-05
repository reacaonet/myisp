<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Concerns\BelongsToTenant;

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
     * Servidor e dado da filial: o corte usa `branch_id`.
     */
    protected function tenantBranchColumn(): ?string
    {
        return 'branch_id';
    }

    /**
     * Query sempre ancorada na empresa e na filial atuais, salvo navegacao
     * cross-tenant. E o que o resto da Infra ja usa como funil.
     */
    public static function scoped(?Builder $query = null): Builder
    {
        return ($query ?? static::query())->forTenant();
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
