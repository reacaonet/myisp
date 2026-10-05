<?php

namespace Modules\Billing\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Concerns\BelongsToTenant;

class CashBookEntry extends Model
{
    use BelongsToTenant;

    /**
     * Lancamento e dado da filial. O saldo anterior da tela de livro caixa
     * tambem passa por aqui: sem o corte, a soma de entrada e saida da rede
     * inteira aparece no topo do usuario de uma loja.
     */
    protected function tenantBranchColumn(): ?string
    {
        return 'branch_id';
    }

    public static function scoped(?Builder $query = null): Builder
    {
        return ($query ?? static::query())->forTenant();
    }

    public static function findScopedOrFail(int $id): self
    {
        return static::scoped()->findOrFail($id);
    }

    protected $fillable = [
        'company_id', 'branch_id',
        'type', 'amount', 'description', 'category',
        'entry_date', 'reference', 'payment_method',
        'notes', 'invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeEntradas($query)
    {
        return $query->where('type', 'entrada');
    }

    public function scopeSaidas($query)
    {
        return $query->where('type', 'saida');
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('entry_date', [$start, $end]);
    }
}
