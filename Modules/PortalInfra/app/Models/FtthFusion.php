<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FtthFusion extends Model
{
    use SoftDeletes;

    protected $table = 'ftth_fusions';

    protected $fillable = [
        'ftth_project_id',
        'cto_id',
        'caixa_emenda_id',
        'fiber_number',
        'olt_port',
        'tube',
        'destination',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function ftthProject(): BelongsTo
    {
        return $this->belongsTo(FtthProject::class);
    }

    public function cto(): BelongsTo
    {
        return $this->belongsTo(Cto::class);
    }

    public function caixaEmenda(): BelongsTo
    {
        return $this->belongsTo(CaixaEmenda::class);
    }

    public function getLabelAttribute(): string
    {
        $parts = array_filter([
            $this->fiber_number ? 'Fibra ' . $this->fiber_number : null,
            $this->olt_port ? 'Porta OLT ' . $this->olt_port : null,
            $this->tube ? 'Tubo ' . $this->tube : null,
        ]);
        return implode(' - ', $parts) ?: 'Fusao';
    }
}