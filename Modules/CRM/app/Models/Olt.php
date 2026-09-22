<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Olt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'model',
        'ip',
        'subnet_mask',
        'type',
        'pon_ports',
        'used_pon_ports',
        'mgmt_login',
        'mgmt_password',
        'snmp_port',
        'snmp_community',
        'olt_region',
        'is_active',
        'notes',
    ];

    protected $hidden = ['mgmt_password'];

    protected function casts(): array
    {
        return [
            'pon_ports' => 'integer',
            'used_pon_ports' => 'integer',
            'snmp_port' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function availablePonPorts(): int
    {
        return max(0, (int) $this->pon_ports - (int) $this->used_pon_ports);
    }
}