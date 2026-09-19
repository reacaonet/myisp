<?php

namespace Modules\PortalInfra\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FtthFiberLink extends Model
{
    use SoftDeletes;

    protected $table = 'ftth_fiber_links';

    protected $fillable = [
        'ftth_project_id',
        'name',
        'type',
        'geometry',
        'length_meters',
        'fiber_count',
        'tube_color',
        'status',
        'notes',
    ];

    protected $casts = [
        'geometry' => 'array',
        'length_meters' => 'decimal:2',
    ];

    public function ftthProject(): BelongsTo
    {
        return $this->belongsTo(FtthProject::class);
    }

    public function connections(): HasMany
    {
        return $this->hasMany(FtthConnection::class, 'fiber_link_id');
    }

    public function getCoordinatesAttribute(): array
    {
        $geometry = $this->geometry ?? [];

        return array_map(function ($point) {
            if (is_array($point) && isset($point['lat']) && isset($point['lng'])) {
                return [(float) $point['lat'], (float) $point['lng']];
            }

            return $point;
        }, $geometry);
    }
}