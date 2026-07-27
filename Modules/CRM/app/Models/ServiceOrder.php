<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'codigo', 'client_id', 'contract_id', 'plan_id', 'technician_id',
        'situacao', 'status', 'encerrado',
        'servico', 'tipo_servico',
        'emissao', 'hora_abertura',
        'orcamento', 'aprovacao', 'saida',
        'data_agendamento', 'hora_agendamento',
        'problema', 'diagnostico', 'solucao',
        'atendente', 'preco', 'serie',
    ];

    protected function casts(): array
    {
        return [
            'emissao' => 'date',
            'orcamento' => 'date',
            'aprovacao' => 'date',
            'saida' => 'date',
            'data_agendamento' => 'date',
            'encerrado' => 'boolean',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function technician()
    {
        return $this->belongsTo(\App\Models\User::class, 'technician_id');
    }
}
