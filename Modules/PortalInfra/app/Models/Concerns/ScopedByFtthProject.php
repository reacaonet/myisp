<?php

namespace Modules\PortalInfra\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Services\TenantContext;
use Modules\PortalInfra\Models\FtthProject;

/**
 * Itens do desenho de rede (CTO, caixa, splitter, fibra, conexao, fusao) nao tem
 * `company_id` nem `branch_id`: o vinculo de dono e `ftth_project_id`. Por isso
 * nao dá para usar `BelongsToTenant` aqui, e o escopo precisa ser derivado do
 * projeto.
 *
 * Sem isso o `FtthEditorController` trabalha apenas por `city`, que e global no
 * banco: o usuario de uma filial abria e editava a rede da outra.
 *
 * Item sem projeto fica invisivel para quem nao e superadmin, porque nao ha como
 * provar de qual filial ele e.
 */
trait ScopedByFtthProject
{
    public function scopeVisibleToUser(Builder $query): Builder
    {
        if (TenantContext::isCrossTenant()) {
            return $query;
        }

        return $query->whereIn('ftth_project_id', static::visibleProjectIdsQuery());
    }

    /**
     * Ids dos projetos que o usuario atual pode tocar. Vazio para superadmin,
     * que enxerga tudo e nao precisa de filtro.
     */
    public static function visibleProjectIdsQuery(): Builder
    {
        return FtthProject::scoped()->select('id');
    }

    public static function scoped(?Builder $query = null): Builder
    {
        return static::visibleToUser($query ?? static::query());
    }

    public static function findScopedOrFail(int $id): self
    {
        return static::scoped()->findOrFail($id);
    }
}
