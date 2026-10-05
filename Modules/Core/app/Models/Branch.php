<?php

namespace Modules\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Concerns\BelongsToTenant;

class Branch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'parent_id',
        'code',
        'name',
        'document',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Filial sem CNPJ e normal: a matriz e as lojas que nao temem nota em
     * nome proprio ficam com o documento vazio.
     */
    public function documentFormatted(): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $this->document);

        if (! $digits) {
            return null;
        }

        if (strlen($digits) === 14) {
            return substr($digits, 0, 2).'.'.substr($digits, 2, 3).'.'.substr($digits, 5, 3).'/'
                .substr($digits, 8, 4).'-'.substr($digits, 12, 2);
        }

        return $this->document;
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function isMatrix(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * A filial e a propria unidade, entao o corte por filial incide sobre `id`.
     * Sem isto um usuario de loja veria as filiais das outras lojas da empresa
     * nos filtros de cliente, contrato e ordem de servico.
     */
    protected function tenantBranchColumn(): ?string
    {
        return 'id';
    }
}
