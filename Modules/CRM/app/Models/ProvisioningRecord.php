<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class ProvisioningRecord extends Model
{
    protected $fillable = [
        'mikrotik_server_id', 'client_id', 'type', 'action',
        'login', 'params', 'response', 'success', 'error',
    ];

    protected function casts(): array
    {
        return [
            'params' => 'array',
            'response' => 'array',
            'success' => 'boolean',
        ];
    }

    public function mikrotikServer()
    {
        return $this->belongsTo(MikrotikServer::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
