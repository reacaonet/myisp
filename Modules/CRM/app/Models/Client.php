<?php

namespace Modules\CRM\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Address;

class Client extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $fillable = [
        'codigo', 'name', 'document', 'rg', 'email', 'login', 'senha',
        'phone', 'cellphone', 'birth_date', 'estado_civil', 'naturalidade',
        'data_entrada', 'vcto_contrato', 'pai', 'mae',
        'type', 'state_registration', 'nf', 'cfop',
        'tipo_assinante', 'tipo_utilizacao', 'grupo',
        'status', 'notes', 'remember_token',
    ];

    protected $hidden = [
        'senha', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'data_entrada' => 'date',
            'vcto_contrato' => 'date',
            'nf' => 'boolean',
            'senha' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function activeContracts()
    {
        return $this->hasMany(Contract::class)->where('status', 'active');
    }

    public function invoices()
    {
        return $this->hasMany(\Modules\Billing\Models\Invoice::class);
    }

    public function serviceOrders()
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
