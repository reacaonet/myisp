<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Technician extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'cargo', 'phone', 'cellphone', 'email',
        'city', 'state', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
