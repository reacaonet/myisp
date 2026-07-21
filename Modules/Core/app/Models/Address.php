<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zipcode',
        'notes',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }
}
