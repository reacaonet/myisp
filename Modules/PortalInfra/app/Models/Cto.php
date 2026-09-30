<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cto extends Model
{
    use SoftDeletes;

    protected $table = 'ctos';

    protected $fillable = [
        'caixa_emenda_id',
        'ftth_project_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'capacity',
        'color',
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
        'distance_from_start',
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
        'distance_from_start' => 'decimal:2',
    ];

    public function caixaEmenda(): BelongsTo
    {
        return $this->belongsTo(CaixaEmenda::class);
    }

    public function ftthProject(): BelongsTo
    {
        return $this->belongsTo(FtthProject::class);
    }

    public function fusions(): HasMany
    {
        return $this->hasMany(FtthFusion::class);
    }

    /**
     * Splitter que esta dentro desta CTO. Na distribuicao real a CTO mae recebe
     * o link e concentra os splitters que alimentam as demais.
     */
    public function splitters(): HasMany
    {
        return $this->hasMany(FtthSplitter::class, 'parent_id')
            ->where('parent_type', 'cto');
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
