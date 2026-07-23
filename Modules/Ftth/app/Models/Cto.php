<?php

namespace Modules\Ftth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cto extends Model
{
    use SoftDeletes;

    protected $table = 'ctos';

    protected $fillable = [
        'caixa_emenda_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'capacity',
        'used_ports',
        'street',
        'number',
        'neighborhood',
        'city',
        'state',
        'zipcode',
        'status',
        'distance_from_start',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'capacity' => 'integer',
        'used_ports' => 'integer',
        'distance_from_start' => 'decimal:2',
    ];

    public function caixaEmenda(): BelongsTo
    {
        return $this->belongsTo(CaixaEmenda::class);
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
