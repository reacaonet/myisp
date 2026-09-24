<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\ProvisioningRecord;
use Modules\CRM\Models\MikrotikServer;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pedido', 'client_id', 'plan_id',
        'activation_date', 'due_date', 'due_day',
        'status', 'situacao', 'billing_type',
        'tipo_conexao',
        'discount', 'acrescimo', 'insento',
        'autobloqueio', 'alterar_senha',
        'observacao',
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
        return $this->belongsTo(Plan::class)->withTrashed();
    }

    public function server()
    {
        return $this->belongsTo(\Modules\Core\Models\Server::class);
    }

    public function mikrotikServer()
    {
        return $this->belongsTo(MikrotikServer::class);
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

    public function latestProvisioningRecord(): ?ProvisioningRecord
    {
        return ProvisioningRecord::with('mikrotikServer')
            ->where('client_id', $this->client_id)
            ->where('success', true)
            ->where('action', '!=', 'remove')
            ->when($this->tipo_conexao, fn($q) => $q->where('type', $this->tipo_conexao))
            ->latest()
            ->first();
    }

    public function provisionedLogin(): ?string
    {
        return $this->pppoe_user
            ?? $this->latestProvisioningRecord()?->login;
    }

    public function provisionedIp(): ?string
    {
        if ($this->ip_address) {
            return $this->ip_address;
        }

        return $this->latestProvisioningRecord()?->params['address'] ?? null;
    }

    public function provisionedMac(): ?string
    {
        if ($this->mac_address) {
            return $this->mac_address;
        }

        $record = $this->latestProvisioningRecord();

        return $record?->params['mac-address'] ?? $record?->params['caller-id'] ?? null;
    }

    public function provisionedMikrotikServer(): ?MikrotikServer
    {
        if ($this->mikrotikServer) {
            return $this->mikrotikServer;
        }

        if ($this->server) {
            return MikrotikServer::where('ip', $this->server->ip)
                ->where('is_active', true)
                ->first();
        }

        return $this->latestProvisioningRecord()?->mikrotikServer;
    }
}
