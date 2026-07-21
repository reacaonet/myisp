<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pedido', 'client_id', 'plan_id', 'server_id',
        'activation_date', 'due_date', 'due_day',
        'status', 'situacao', 'billing_type',
        'pppoe_user', 'pppoe_password',
        'ip_address', 'route_ip', 'ipv6',
        'mac_address', 'mac_wireless', 'wpa_key',
        'tipo_conexao',
        'discount', 'acrescimo', 'insento',
        'autobloqueio', 'alterar_senha',
        'ip_pool', 'observacao',
        'ip_ubnt', 'porta_ubnt', 'login_ubnt', 'senha_ubnt',
        'install_street', 'install_number', 'install_complement',
        'install_neighborhood', 'install_city', 'install_state', 'install_zipcode',
        'notes', 'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'activation_date' => 'date',
            'due_date' => 'date',
            'canceled_at' => 'datetime',
            'insento' => 'boolean',
            'autobloqueio' => 'boolean',
            'alterar_senha' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function server()
    {
        return $this->belongsTo(\Modules\Core\Models\Server::class);
    }

    public function invoices()
    {
        return $this->hasMany(\Modules\Billing\Models\Invoice::class);
    }

    public function activeInvoices()
    {
        return $this->hasMany(\Modules\Billing\Models\Invoice::class)
            ->whereIn('status', ['pending', 'overdue']);
    }
}
