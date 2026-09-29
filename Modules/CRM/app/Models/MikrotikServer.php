<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Concerns\BelongsToTenant;

class MikrotikServer extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = ['company_id', 'branch_id', 'name', 'ip', 'port', 'login', 'senha', 'type', 'is_active', 'notes'];

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
