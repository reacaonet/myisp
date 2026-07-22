<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    protected $fillable = ['name', 'website', 'phone', 'email', 'notes'];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
