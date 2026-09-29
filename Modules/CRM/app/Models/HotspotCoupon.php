<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Concerns\BelongsToTenant;

class HotspotCoupon extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id', 'code', 'profile', 'duration_hours', 'price', 'status',
        'server_id', 'client_id', 'used_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function server()
    {
        return $this->belongsTo(MikrotikServer::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
