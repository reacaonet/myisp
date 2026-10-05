<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    /** Categoria usada quando o chamado e uma solicitacao de mudanca de endereco. */
    public const CATEGORY_ADDRESS = 'endereco';

    /**
     * Campos de um endereco proposals. A ordem e a mesma do formulario do
     * portal para o `$fillable` do `Address` aceitar o array direto.
     */
    public const ADDRESS_FIELDS = [
        'street', 'number', 'referencia', 'complement',
        'neighborhood', 'city', 'state', 'zipcode',
    ];

    protected $fillable = [
        'codigo', 'client_id', 'contract_id',
        'subject', 'description', 'status', 'priority', 'category',
        'proposed_address',
    ];

    protected function casts(): array
    {
        return [
            'proposed_address' => 'array',
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

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(TicketMessage::class)->latest();
    }

    public function isAddressChange(): bool
    {
        return $this->category === self::CATEGORY_ADDRESS
            && is_array($this->proposed_address)
            && $this->proposed_address !== [];
    }

    /**
     * Endereco como o cliente digitou, ja normalizado. Retorna null quando o
     * chamado nao e de mudanca de endereco, para a interface nao inventar um
     * formulario de aprovacao vazio.
     *
     * @return array<string, string>|null
     */
    public function proposedAddress(): ?array
    {
        if (! $this->isAddressChange()) {
            return null;
        }

        $address = [];

        foreach (self::ADDRESS_FIELDS as $field) {
            $address[$field] = (string) ($this->proposed_address[$field] ?? '');
        }

        return $address;
    }
}
