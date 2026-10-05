<?php

namespace Modules\PortalInfra\Services;

use Illuminate\Support\Collection;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\Cto;

/**
 * Projeção única dos marcadores do mapa de rede.
 *
 * O mapa existe em dois portais (admin e técnico) e os dois precisam mostrar
 * exatamente os mesmos campos, com o mesmo filtro de status translúcido e o
 * mesmo link de detalhe. Duplicar o `select` + `map` fazia os dois divergirem
 * em silêncio: bastava o admin ganhar um campo e o técnico deixava de ver.
 */
class FtthMapData
{
    /**
     * @return array{ctos: Collection<int, array<string, mixed>>, caixas: Collection<int, array<string, mixed>>}
     */
    public function build(?string $city = null, ?int $projectId = null): array
    {
        // `scoped()` resolve via `ftth_project_id`, derivado do proprio tecnico
        // quando o guard e `technician`. Sem isso o mapa do tecnico desenharia a
        // rede das outras franquias.
        $queryCto = Cto::scoped()->select('id', 'code', 'name', 'latitude', 'longitude', 'street', 'city', 'capacity', 'used_ports', 'status', 'color', 'caixa_emenda_id', 'ftth_project_id');
        $queryCaixa = CaixaEmenda::scoped()->select('id', 'code', 'name', 'latitude', 'longitude', 'street', 'city', 'capacity', 'used_ports', 'status', 'ftth_project_id');

        if ($city) {
            $queryCto->where('city', $city);
            $queryCaixa->where('city', $city);
        }

        if ($projectId) {
            $queryCto->where('ftth_project_id', $projectId);
            $queryCaixa->where('ftth_project_id', $projectId);
        }

        return [
            'ctos' => $queryCto->orderBy('code')->get()->map(fn ($c) => [
                'type' => 'cto',
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'lat' => (float) $c->latitude,
                'lng' => (float) $c->longitude,
                'street' => $c->street,
                'city' => $c->city,
                'capacity' => $c->capacity,
                'used' => $c->used_ports,
                'status' => $c->status,
                'color' => $c->color,
                'project_id' => $c->ftth_project_id,
                'caixa_id' => $c->caixa_emenda_id,
            ]),
            'caixas' => $queryCaixa->orderBy('code')->get()->map(fn ($c) => [
                'type' => 'caixa',
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'lat' => (float) $c->latitude,
                'lng' => (float) $c->longitude,
                'street' => $c->street,
                'city' => $c->city,
                'capacity' => $c->capacity,
                'used' => $c->used_ports,
                'status' => $c->status,
                'project_id' => $c->ftth_project_id,
            ]),
        ];
    }
}
