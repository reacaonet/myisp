<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Concerns\BelongsToTenant;

class UptimeMonitor extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name', 'host', 'port', 'type', 'interval_seconds',
        'is_active', 'is_up', 'last_check_at', 'response_time_ms',
        'last_error', 'server_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_up' => 'boolean',
            'last_check_at' => 'datetime',
        ];
    }

    public function server()
    {
        return $this->belongsTo(MikrotikServer::class);
    }

    public function checks()
    {
        return $this->hasMany(UptimeCheck::class);
    }
}
