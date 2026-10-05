<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * `service_orders` nao tem `company_id`/`branch_id`: a titularidade vem do
     * cliente. Sem ancorar no cliente escopado, uma ordem de qualquer loja
     * entrava na listagem — e `findOrFail($id)` abria, editava, concluia e
     * apagava a ordem alheia pelo id.
     */
    public function scopeScoped($query): Builder
    {
        return $query->whereHas('client', fn (Builder $q) => $q->scoped());
    }

    public static function scopedQuery(): Builder
    {
        return static::query()->scoped();
    }

    public static function findScoped(int $id): ?self
    {
        return static::scopedQuery()->find($id);
    }

    public static function findScopedOrFail(int $id): self
    {
        return static::scopedQuery()->findOrFail($id);
    }
}
