<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockLocation extends Model
{
    protected $fillable = ['name', 'type', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
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
