<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Concerns\BelongsToTenant;
use Modules\Core\Models\Server;

class Plan extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id',
        'name', 'slug', 'description', 'segment',
        'download_speed', 'upload_speed',
        'price', 'setup_fee', 'billing_cycle',
        'max_simultaneous', 'max_session_time',
        'has_pppoe', 'has_hotspot',
        'pool', 'address_list',
        'url_advertise', 'advertise_intervalo',
        'police_in', 'police_out',
        'tipo_servidor', 'interface',
        'plano_id_externo', 'server_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'has_pppoe' => 'boolean',
            'has_hotspot' => 'boolean',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'setup_fee' => 'decimal:2',
        ];
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
