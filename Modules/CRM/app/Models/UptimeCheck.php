<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class UptimeCheck extends Model
{
    protected $fillable = [
        'uptime_monitor_id', 'is_up', 'response_time_ms',
        'error_message', 'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'is_up' => 'boolean',
            'checked_at' => 'datetime',
        ];
    }

    public function monitor()
    {
        return $this->belongsTo(UptimeMonitor::class);
    }
}
