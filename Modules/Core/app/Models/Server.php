<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = [
        'name', 'ip', 'username', 'password', 'interface',
        'secret', 'tipo', 'porta_api', 'porta_ssh', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
