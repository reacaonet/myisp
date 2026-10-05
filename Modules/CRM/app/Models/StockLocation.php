<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Concerns\BelongsToTenant;

class StockLocation extends Model
{
    use BelongsToTenant;

    protected $fillable = ['company_id', 'branch_id', 'name', 'type', 'user_id'];

    /**
     * Local de estoque e dado da filial: o corte usa `branch_id`.
     */
    protected function tenantBranchColumn(): ?string
    {
        return 'branch_id';
    }

    public static function scoped(?Builder $query = null): Builder
    {
        return ($query ?? static::query())->forTenant();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(StockBalance::class, 'location_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'location_id');
    }
}
