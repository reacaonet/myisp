<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Concerns\BelongsToTenant;

class FtthProject extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'ftth_projects';

    /**
     * O dono da rede e a filial (`branch_id`). Um projeto criado na Matriz nao
     * aparece para o usuario de uma loja, e o inverso tambem vale.
     */
    protected function tenantBranchColumn(): ?string
    {
        return 'branch_id';
    }

    public static function scoped(?Builder $query = null): Builder
    {
        return ($query ?? static::query())->forTenant();
    }

    public static function findScopedOrFail(int $id): self
    {
        return static::scoped()->findOrFail($id);
    }

    protected $fillable = [
        'company_id', 'branch_id',
        'name',
        'city',
        'state',
        'prefix',
        'status',
        'total_streets',
        'total_ctos',
        'total_caixas',
        'total_distance_km',
        'has_bounds',
        'notes',
    ];

    protected $casts = [
        'total_streets' => 'integer',
        'total_ctos' => 'integer',
        'total_caixas' => 'integer',
        'total_distance_km' => 'decimal:2',
        'has_bounds' => 'boolean',
    ];

    public function ctos(): HasMany
    {
        return $this->hasMany(Cto::class);
    }

    public function caixas(): HasMany
    {
        return $this->hasMany(CaixaEmenda::class);
    }

    public function fiberLinks(): HasMany
    {
        return $this->hasMany(FtthFiberLink::class);
    }

    public function splitters(): HasMany
    {
        return $this->hasMany(FtthSplitter::class);
    }

    public function connections(): HasMany
    {
        return $this->hasMany(FtthConnection::class);
    }
}
