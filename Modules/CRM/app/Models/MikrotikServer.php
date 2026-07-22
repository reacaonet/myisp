<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MikrotikServer extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'ip', 'port', 'login', 'senha', 'type', 'is_active', 'notes'];

    protected $hidden = ['senha'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'port' => 'integer',
        ];
    }

    public function provisioningRecords()
    {
        return $this->hasMany(ProvisioningRecord::class);
    }
}
