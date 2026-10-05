<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'street',
        'number',
        // `referencia` existe na tabela e e validada/enviada pelo formulario de
        // cliente, mas faltava aqui: `create()` passa por `fill()`, entao o
        // valor era descartado em silencio ao criar o primeiro endereco.
        'referencia',
        'complement',
        'neighborhood',
        'city',
        'state',
        'zipcode',
        'notes',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

    /**
     * Endereco em uma linha, para exibicao.
     *
     * Diversas views ja usavam `$address->full_address`, mas o atributo nunca
     * existiu: o resultado era sempre `N/A`. O accessor resolve as chamadas
     * antigas sem precisar tocar em cada template.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            trim((string) $this->street),
            trim((string) $this->number),
            trim((string) $this->referencia),
        ], fn ($part) => $part !== '');

        $line = implode(', ', $parts);

        $tail = array_filter([
            trim((string) $this->neighborhood),
            $this->city ? trim((string) $this->city).'/'.trim((string) $this->state) : trim((string) $this->state),
            trim((string) $this->zipcode),
        ], fn ($part) => $part !== '' && $part !== '/');

        return trim($line.($tail ? ' - '.implode(', ', $tail) : ''));
    }
}
