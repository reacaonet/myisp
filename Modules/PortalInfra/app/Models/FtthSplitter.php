<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FtthSplitter extends Model
{
    use SoftDeletes;

    protected $table = 'ftth_splitters';

    protected $fillable = [
        'ftth_project_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'input_ports',
        'output_ports',
        'ratio',
        'status',
        'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'input_ports' => 'integer',
        'output_ports' => 'integer',
    ];

    public function ftthProject(): BelongsTo
    {
        return $this->belongsTo(FtthProject::class);
    }

    public function connections(): HasMany
    {
        return $this->hasMany(FtthConnection::class, 'source_id')
            ->where('source_type', 'splitter');
    }

    public function getRatioAttribute(?string $value): string
    {
        return $value ?: $this->input_ports . 'x' . $this->output_ports;
    }
}