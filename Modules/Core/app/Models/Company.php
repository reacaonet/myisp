<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'legal_name',
        'fantasy_name',
        'slug',
        'code',
        'is_active',
        'is_franchise',
        'document',
        'state_registration',
        'municipal_registration',
        'phone',
        'cellphone',
        'email',
        'website',
        'address',
        'city',
        'state',
        'zip',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_franchise' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_user');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function fiscal(string $key)
    {
        $value = $this->{$key};

        if (filled($value)) {
            return $value;
        }

        return $this->parent ? $this->parent->fiscal($key) : null;
    }

    /** Razao social com heranca (filial/franquia pode compartilhar o CNPJ da matriz). */
    public function legalName(): string
    {
        return $this->fiscal('legal_name') ?: $this->name;
    }

    /** Nome fantasia com heranca; cai no nome cadastrado. */
    public function fantasyName(): string
    {
        return $this->fiscal('fantasy_name') ?: $this->name;
    }

    /** Nome usado em marca/boleto: fantasia > razao social. */
    public function displayName(): string
    {
        return $this->fantasyName();
    }

    /** Iniciais para o logo textual das paginas publicas. */
    public function initials(): string
    {
        $words = preg_split('/\s+/', trim($this->displayName())) ?: [];
        $letters = '';

        foreach ($words as $word) {
            if (mb_strlen($word) > 2 || mb_strlen($word) === 0) {
                $letters .= mb_substr($word, 0, 1);

                if (mb_strlen($letters) >= 2) {
                    break;
                }
            }
        }

        return mb_strtoupper($letters !== '' ? $letters : mb_substr($this->displayName(), 0, 2));
    }
}
