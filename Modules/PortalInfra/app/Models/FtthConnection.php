<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FtthConnection extends Model
{
    use SoftDeletes;

    protected $table = 'ftth_connections';

    protected $fillable = [
        'ftth_project_id',
        'source_type',
        'source_id',
        'source_port',
        'fiber_link_id',
        'target_type',
        'target_id',
        'target_port',
        'fiber_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'source_id' => 'integer',
        'source_port' => 'integer',
        'target_id' => 'integer',
        'target_port' => 'integer',
    ];

    public function ftthProject(): BelongsTo
    {
        return $this->belongsTo(FtthProject::class);
    }

    public function fiberLink(): BelongsTo
    {
        return $this->belongsTo(FtthFiberLink::class, 'fiber_link_id');
    }
}