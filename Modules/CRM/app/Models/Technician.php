<?php

namespace Modules\CRM\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technician extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'login', 'senha', 'cargo', 'phone', 'cellphone', 'email',
        'city', 'state', 'is_active', 'remember_token',
    ];

    protected $hidden = [
        'senha', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'senha' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
