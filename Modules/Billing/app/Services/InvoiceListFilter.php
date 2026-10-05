<?php

namespace Modules\Billing\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Modules\Core\Models\Branch;
use Modules\Core\Services\TenantContext;

/**
 * Filtros das listagens de faturas e de boletos. As duas telas leem a mesma
 * tabela (invoices), entao a busca fica agrupada em um unico lugar: um
 * `orWhere` solto na listagem escapa do `forCompany` e devolve fatura de outra
 * empresa.
 */
class InvoiceListFilter
{
    public const STATUSES = [
        'pending' => 'Pendente',
        'overdue' => 'Atrasada',
        'paid' => 'Paga',
        'canceled' => 'Cancelada',
    ];

    public const PAYMENT_METHODS = [
        'pix' => 'PIX',
        'boleto' => 'Boleto',
        'credit_card' => 'Cartao de credito',
        'debit_contract' => 'Debito em contrato',
        'cash' => 'Dinheiro',
        'other' => 'Outro',
    ];

    public const CHARGE_TYPES = [
        'boleto' => 'Com boleto gerado',
        'pix' => 'Com PIX gerado',
        'sem' => 'Sem cobranca gerada',
    ];

    public const YES_NO = [
        'sim' => 'Sim',
        'nao' => 'Nao',
    ];

    /**
     * Regras de validacao dos parametros de GET das listagens. As regras de
     * comparacao (`after_or_equal`, `gte`) so entram quando o campo inicial do
     * intervalo veio na request, senao o par ausente reprova o filtro.
     *
     * @return array<string, mixed>
     */
    public function rules(Request $request, bool $boletos = false): array
    {
        $rules = [
            'search' => 'nullable|string|max:120',
            'status' => 'nullable|in:'.implode(',', array_keys(self::STATUSES)),
            'payment_method' => 'nullable|in:'.implode(',', array_keys(self::PAYMENT_METHODS)),
            'branch_id' => 'nullable|integer',
            'due_from' => 'nullable|date',
            'due_to' => $this->endOfRangeRule(['date'], 'after_or_equal', 'due_from', $request),
            'paid_from' => 'nullable|date',
            'paid_to' => $this->endOfRangeRule(['date'], 'after_or_equal', 'paid_from', $request),
            'total_min' => 'nullable|numeric|min:0',
            'total_max' => $this->endOfRangeRule(['numeric', 'min:0'], 'gte', 'total_min', $request),
        ];

        if ($boletos) {
            $rules['gateway_id'] = 'nullable|integer';
            $rules['charge'] = 'nullable|in:'.implode(',', array_keys(self::CHARGE_TYPES));

            return $rules;
        }

        $rules['blocked'] = 'nullable|in:'.implode(',', array_keys(self::YES_NO));
        $rules['avulso'] = 'nullable|in:'.implode(',', array_keys(self::YES_NO));

        return $rules;
    }

    /**
     * Regras do fim de um intervalo. A comparacao com o inicio so entra quando
     * o inicio veio na request: sem o campo inicial, `total_max` sozinho seria
     * reprovado por `gte:` vazio.
     *
     * @param  array<int, string>  $base
     */
    private function endOfRangeRule(array $base, string $comparison, string $startField, Request $request): string
    {
        $rules = implode('|', $base);

        return $request->filled($startField)
            ? "nullable|{$rules}|{$comparison}:{$startField}"
            : "nullable|{$rules}";
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder $query, array $filters, bool $boletos = false): Builder
    {
        $filters = array_filter($filters, fn ($value) => $value !== null && $value !== '');

        if ($search = $this->clean($filters['search'] ?? null)) {
            $like = "%{$search}%";

            $query->where(function (Builder $q) use ($like) {
                $q->where('invoice_number', 'ilike', $like)
                    ->orWhere('boleto_numero', 'ilike', $like)
                    ->orWhere('transaction_id', 'ilike', $like)
                    ->orWhereHas('client', fn ($client) => $client->where('name', 'ilike', $like));
            });
        }

        if ($status = $this->clean($filters['status'] ?? null)) {
            $query->where('status', $status);
        }

        if ($paymentMethod = $this->clean($filters['payment_method'] ?? null)) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($branchId = $filters['branch_id'] ?? null) {
            $query->where('branch_id', $branchId);
        }

        $this->applyDateRange($query, 'due_date', $filters['due_from'] ?? null, $filters['due_to'] ?? null);
        $this->applyDateRange($query, 'paid_date', $filters['paid_from'] ?? null, $filters['paid_to'] ?? null);

        if (isset($filters['total_min']) && $filters['total_min'] !== '') {
            $query->where('total', '>=', (float) $filters['total_min']);
        }

        if (isset($filters['total_max']) && $filters['total_max'] !== '') {
            $query->where('total', '<=', (float) $filters['total_max']);
        }

        if ($boletos) {
            if ($gatewayId = $filters['gateway_id'] ?? null) {
                $query->where('gateway_id', $gatewayId);
            }

            $this->applyCharge($query, $this->clean($filters['charge'] ?? null));

            return $query;
        }

        $this->applyYesNo($query, 'blocked_at', $this->clean($filters['blocked'] ?? null));

        if ($avulso = $this->clean($filters['avulso'] ?? null)) {
            $query->where('avulso', $avulso === 'sim');
        }

        return $query;
    }

    /**
     * Filias que aparecem no filtro. Segue o mesmo escopo da listagem: por
     * empresa, sem restricao de filial liberada ao operador.
     *
     * @return Collection<int, Branch>
     */
    public function branches(): Collection
    {
        // `forTenant` e o que corta pela filial do usuario. Com `forCompany` o
        // filtro de filial oferecia as lojas da rede inteira.
        return Branch::query()
            ->forTenant()
            ->orderByRaw('parent_id is null desc')
            ->orderBy('name')
            ->get();
    }

    private function applyDateRange(Builder $query, string $column, mixed $from, mixed $to): void
    {
        if ($this->clean($from)) {
            $query->whereDate($column, '>=', $from);
        }

        if ($this->clean($to)) {
            $query->whereDate($column, '<=', $to);
        }
    }

    private function applyCharge(Builder $query, ?string $charge): void
    {
        if ($charge === 'boleto') {
            $query->whereNotNull('boleto_numero');
        } elseif ($charge === 'pix') {
            $query->whereNotNull('pix_copy_paste');
        } elseif ($charge === 'sem') {
            $query->whereNull('boleto_numero')->whereNull('pix_copy_paste');
        }
    }

    private function applyYesNo(Builder $query, string $column, ?string $value): void
    {
        if ($value === 'sim') {
            $query->whereNotNull($column);
        } elseif ($value === 'nao') {
            $query->whereNull($column);
        }
    }

    private function clean(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
