<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FtthProject extends Model
{
    use SoftDeletes;

    protected $table = 'ftth_projects';

    protected $fillable = [
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
}