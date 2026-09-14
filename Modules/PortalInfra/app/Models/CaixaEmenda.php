<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaixaEmenda extends Model
{
    use SoftDeletes;

    protected $table = 'caixas_emenda';

    protected $fillable = [
        'ftth_project_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'capacity',
        'used_ports',
        'fiber_fusions',
        'splitter_config',
        'olt_port',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'zipcode',
        'status',
        'notes',
        'project_notes',
        'technician_notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'capacity' => 'integer',
        'used_ports' => 'integer',
        'fiber_fusions' => 'integer',
    ];

    public function ctos(): HasMany
    {
        return $this->hasMany(Cto::class);
    }

    public function ftthProject(): BelongsTo
    {
        return $this->belongsTo(FtthProject::class);
    }

    public function fusions(): HasMany
    {
        return $this->hasMany(FtthFusion::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->neighborhood,
            $this->city,
            $this->state,
        ]);
        return implode(', ', $parts) ?: 'Sem endereco';
    }

    public function getUsagePercentAttribute(): float
    {
        return $this->capacity > 0 ? round(($this->used_ports / $this->capacity) * 100, 1) : 0;
    }
}
