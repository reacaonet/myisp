<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotspotCoupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'profile', 'duration_hours', 'price', 'status',
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
        return $this->belongsTo(\Modules\Core\Models\Server::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
