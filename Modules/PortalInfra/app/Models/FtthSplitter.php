<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FtthSplitter extends Model
{
    use SoftDeletes;

    protected $table = 'ftth_splitters';

    protected $fillable = [
        'ftth_project_id',
        'name',
        'code',
        'parent_type',
        'parent_id',
        'latitude',
        'longitude',
        'input_ports',
        'output_ports',
        'ratio',
        'status',
        'notes',
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'input_ports' => 'integer',
        'output_ports' => 'integer',
    ];

    public function ftthProject(): BelongsTo
    {
        return $this->belongsTo(FtthProject::class);
    }

    /**
     * O splitter vive dentro de uma CEO ou de uma CTO: quem o alimenta e quem
     * distribui. parent_type guarda o tipo ('caixa' ou 'cto') porque a
     * referencia e polimorfica e nao ha FK no banco.
     */
    public function parent(): ?Model
    {
        return match ($this->parent_type) {
            'cto' => Cto::withTrashed()->find($this->parent_id),
            'caixa' => CaixaEmenda::withTrashed()->find($this->parent_id),
            default => null,
        };
    }

    public function parentLabel(): ?string
    {
        $parent = $this->parent();

        return $parent ? ($parent->code ?: $parent->name) : null;
    }

    public function connections(): HasMany
    {
        return $this->hasMany(FtthConnection::class, 'source_id')
            ->where('source_type', 'splitter');
    }

    public function getRatioAttribute(?string $value): string
    {
        return $value ?: $this->input_ports.'x'.$this->output_ports;
    }
}
