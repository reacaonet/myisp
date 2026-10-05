<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\FtthConnection;
use Modules\PortalInfra\Models\FtthFiberLink;
use Modules\PortalInfra\Models\FtthProject;
use Modules\PortalInfra\Models\FtthSplitter;

class FtthEditorController extends Controller
{
    private const EARTH_RADIUS_KM = 6371.0;

    public function index(Request $request)
    {
        $cities = Cto::scoped()->whereNotNull('city')->where('city', '!=', '')
            ->distinct()->orderBy('city')->pluck('city')->values();
        $selected = $request->get('cidade');

        return view('infra::editor.index', compact('cities', 'selected'));
    }

    public function data(Request $request)
    {
        $city = $request->get('cidade');
        $cities = Cto::scoped()->whereNotNull('city')->where('city', '!=', '')
            ->distinct()->orderBy('city')->pluck('city')->values();

        if (! $city) {
            return response()->json([
                'cities' => $cities,
                'city' => $cities->first() ?: null,
                'ctos' => [],
                'caixas' => [],
                'splitters' => [],
                'fibers' => [],
            ]);
        }

        $ctos = Cto::scoped()->whereNull('deleted_at')->where('city', $city)
            ->orderBy('code')->get()->map(fn ($c) => [
                'type' => 'cto',
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'lat' => (float) $c->latitude,
                'lng' => (float) $c->longitude,
                'street' => $c->street,
                'capacity' => $c->capacity,
                'used' => $c->used_ports,
                'status' => $c->status,
                'color' => $c->color,
                'caixa_id' => $c->caixa_emenda_id,
                'splitter_count' => $c->splitters()->count(),
            ]);

        $caixas = CaixaEmenda::scoped()->whereNull('deleted_at')->where('city', $city)
            ->orderBy('code')->get()->map(fn ($c) => [
                'type' => 'caixa',
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'lat' => (float) $c->latitude,
                'lng' => (float) $c->longitude,
                'street' => $c->street,
                'capacity' => $c->capacity,
                'used' => $c->used_ports,
                'status' => $c->status,
                'cto_count' => $c->ctos()->count(),
                'splitter_count' => $c->splitters()->count(),
            ]);

        $project = $this->findCityProject($city);

        $splitters = $project
            ? $project->splitters()->get()->map(fn ($s) => $this->splitterPayload($s))
            : collect();

        $fibers = $project
            ? $project->fiberLinks()->get()->map(fn ($f) => [
                'type' => 'fiber',
                'id' => $f->id,
                'name' => $f->name,
                'type' => $f->type,
                'geometry' => $f->geometry,
                'length_meters' => (float) $f->length_meters,
                'fiber_count' => $f->fiber_count,
            ])
            : collect();

        $connections = $project
            ? $project->connections()->get()->map(fn ($cnx) => [
                'id' => $cnx->id,
                'source_type' => $cnx->source_type,
                'source_id' => $cnx->source_id,
                'source_port' => $cnx->source_port,
                'source_code' => $this->elementCode($cnx->source_type, $cnx->source_id),
                'fiber_link_id' => $cnx->fiber_link_id,
                'fiber_name' => $cnx->fiber_link_id ? (FtthFiberLink::withTrashed()->whereKey($cnx->fiber_link_id)->value('name')) : null,
                'target_type' => $cnx->target_type,
                'target_id' => $cnx->target_id,
                'target_port' => $cnx->target_port,
                'target_code' => $this->elementCode($cnx->target_type, $cnx->target_id),
            ])
            : collect();

        return response()->json([
            'cities' => $cities,
            'city' => $city,
            'ctos' => $ctos,
            'caixas' => $caixas,
            'splitters' => $splitters,
            'fibers' => $fibers,
            'connections' => $connections,
        ]);
    }

    private function elementCode(string $type, ?int $id): ?string
    {
        if (! $id) {
            return null;
        }

        return match ($type) {
            'cto' => Cto::withTrashed()->visibleToUser()->whereKey($id)->value('code'),
            'caixa' => CaixaEmenda::withTrashed()->visibleToUser()->whereKey($id)->value('code'),
            'splitter' => FtthSplitter::withTrashed()->visibleToUser()->whereKey($id)->value('code')
                ?: FtthSplitter::withTrashed()->visibleToUser()->whereKey($id)->value('name'),
            default => null,
        };
    }

    private function findCityProject(string $city): ?FtthProject
    {
        return FtthProject::scoped()->where('city', $city)->orderByDesc('created_at')->first();
    }

    private function ensureCityProject(string $city): FtthProject
    {
        $project = $this->findCityProject($city);

        if ($project) {
            return $project;
        }

        return FtthProject::create([
            'name' => 'Rede '.$city,
            'city' => $city,
            'state' => null,
            'prefix' => null,
            'status' => 'active',
            'notes' => 'Projeto automatico criado pelo Editor de Rede FTTH.',
        ]);
    }

    public function move(Request $request, string $type, int $id)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = (float) $request->input('lat');
        $lng = (float) $request->input('lng');

        switch ($type) {
            case 'cto':
                $model = Cto::findScopedOrFail($id);
                break;
            case 'caixa':
                $model = CaixaEmenda::findScopedOrFail($id);
                break;
            case 'splitter':
                $model = FtthSplitter::findScopedOrFail($id);
                break;
            default:
                return response()->json(['message' => 'Tipo inválido.'], 422);
        }

        $model->update(['latitude' => $lat, 'longitude' => $lng]);

        return response()->json(['message' => 'Posição atualizada.', 'id' => $model->id]);
    }

    public function storeFiber(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'type' => 'required|in:tronco,distribuicao,drop',
            'geometry' => 'required|array|min:2',
            'fiber_count' => 'nullable|string|max:10',
            'tube_color' => 'nullable|string|max:20',
        ]);

        $geometry = $request->input('geometry');

        $fiber = FtthFiberLink::create([
            'ftth_project_id' => $this->ensureCityProject($request->input('city'))->id,
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'geometry' => $geometry,
            'length_meters' => round($this->polylineLength($geometry), 2),
            'fiber_count' => $request->input('fiber_count'),
            'tube_color' => $request->input('tube_color'),
        ]);

        return response()->json([
            'message' => 'Fibra lançada salva.',
            'fiber' => [
                'id' => $fiber->id,
                'name' => $fiber->name,
                'type' => $fiber->type,
                'geometry' => $fiber->geometry,
                'length_meters' => (float) $fiber->length_meters,
                'fiber_count' => $fiber->fiber_count,
            ],
        ], 201);
    }

    public function updateFiber(Request $request, int $id)
    {
        $fiber = FtthFiberLink::findScopedOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'required|in:tronco,distribuicao,drop',
            'geometry' => 'nullable|array|min:2',
            'fiber_count' => 'nullable|string|max:10',
            'tube_color' => 'nullable|string|max:20',
        ]);

        $data = [
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'fiber_count' => $request->input('fiber_count'),
            'tube_color' => $request->input('tube_color'),
        ];

        if ($request->filled('geometry')) {
            $data['geometry'] = $request->input('geometry');
            $data['length_meters'] = round($this->polylineLength($request->input('geometry')), 2);
        }

        $fiber->update($data);

        return response()->json(['message' => 'Fibra atualizada.', 'fiber' => $fiber->fresh()]);
    }

    public function destroyFiber(int $id)
    {
        $fiber = FtthFiberLink::findScopedOrFail($id);
        $fiber->connections()->delete();
        $fiber->delete();

        return response()->json(['message' => 'Fibra removida.']);
    }

    public function storeSplitter(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            // O splitter nasce dentro de uma CEO ou de uma CTO, nunca solto.
            'parent_type' => 'required|in:caixa,cto',
            'parent_id' => 'required|integer',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'input_ports' => 'required|integer|min:1|max:4',
            'output_ports' => 'required|integer|in:8,16,32,64',
        ]);

        $parent = $this->assertParent($request->input('parent_type'), $request->input('parent_id'));

        // Sem coordenada no mapa, o splitter nasce em cima de quem o alimenta.
        $lat = $request->filled('lat') ? (float) $request->input('lat') : (float) $parent->latitude;
        $lng = $request->filled('lng') ? (float) $request->input('lng') : (float) $parent->longitude;

        $splitter = FtthSplitter::create([
            'ftth_project_id' => $parent->ftth_project_id
                ?: $this->ensureCityProject($request->input('city'))->id,
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'parent_type' => $request->input('parent_type'),
            'parent_id' => $parent->id,
            'latitude' => $lat,
            'longitude' => $lng,
            'input_ports' => $request->input('input_ports'),
            'output_ports' => $request->input('output_ports'),
            'ratio' => $request->input('input_ports').'x'.$request->input('output_ports'),
        ]);

        return response()->json([
            'message' => 'Splitter adicionado.',
            'splitter' => $this->splitterPayload($splitter),
        ], 201);
    }

    public function updateSplitter(Request $request, int $id)
    {
        $splitter = FtthSplitter::findScopedOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:20',
            'input_ports' => 'sometimes|required|integer|min:1|max:4',
            'output_ports' => 'sometimes|required|integer|in:8,16,32,64',
            'parent_type' => 'sometimes|required|in:caixa,cto',
            'parent_id' => 'sometimes|required|integer',
        ]);

        $data = $request->only(['name', 'code', 'input_ports', 'output_ports']);

        if ($request->has('output_ports') || $request->has('input_ports')) {
            $data['ratio'] = (int) ($data['input_ports'] ?? $splitter->input_ports)
                .'x'.(int) ($data['output_ports'] ?? $splitter->output_ports);
        }

        // Trocar de progenitor: CEO e CTO trocam de lugar o splitter.
        if ($request->filled('parent_type') || $request->filled('parent_id')) {
            $parent = $this->assertParent(
                $request->input('parent_type', $splitter->parent_type),
                $request->input('parent_id', $splitter->parent_id)
            );

            $data['parent_type'] = $request->input('parent_type', $splitter->parent_type);
            $data['parent_id'] = $parent->id;
        }

        $splitter->update($data);

        return response()->json([
            'message' => 'Splitter atualizado.',
            'splitter' => $this->splitterPayload($splitter->fresh()),
        ]);
    }

    /**
     * Edicao de CEO/CTO pelo popup do mapa. A CTO e o unico elemento com cor
     * propria: o tecnico usa a cor para identificar a CTO no mapa.
     */
    public function updateElement(Request $request, string $type, int $id)
    {
        $model = match ($type) {
            'cto' => Cto::findScopedOrFail($id),
            'caixa' => CaixaEmenda::findScopedOrFail($id),
            default => abort(422, 'Tipo inválido.'),
        };

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:20',
            'street' => 'nullable|string|max:255',
            'capacity' => 'sometimes|required|integer|min:1|max:1024',
            'status' => 'sometimes|required|in:active,inactive',
            'notes' => 'nullable|string|max:1000',
        ];

        if ($type === 'cto') {
            $rules['color'] = 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/';
        }

        $request->validate($rules);

        $data = $request->only(array_keys($rules));

        if ($request->has('capacity')) {
            $data['capacity'] = (int) $request->input('capacity');
        }

        if ($type === 'cto' && $request->has('color')) {
            $data['color'] = $request->input('color') ?: null;
        }

        $model->update($data);

        return response()->json([
            'message' => ($type === 'cto' ? 'CTO' : 'CEO').' atualizada.',
            'element' => [
                'type' => $type,
                'id' => $model->id,
                'code' => $model->code,
                'name' => $model->name,
                'street' => $model->street,
                'capacity' => $model->capacity,
                'status' => $model->status,
                'color' => $type === 'cto' ? $model->color : null,
            ],
        ]);
    }

    /**
     * Duplica CEO ou CTO a partir do popup do mapa. A copia nasce no ponto que
     * o tecnico clicar, repetindo os dados da original: e o atalho para montar
     * um trecho de rede copiando um padrao que ja funciona.
     *
     * Splitters e conexoes nao vem junto. A copia entra zerada e sem filha:
     * replicar a arvore inteira multiplica portas e SPLs na mao, e quem monta
     * o trecho decide o que precisa. A copia tambem mantem o status da
     * original, que e o status que a rede planejada usa.
     */
    public function duplicateElement(Request $request, string $type, int $id)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            // O tecnico pode renomear a copia no prompt. Vazio mantem o nome da
            // original como base.
            'name' => 'nullable|string|max:255',
        ]);

        $source = match ($type) {
            'cto' => Cto::findScopedOrFail($id),
            'caixa' => CaixaEmenda::findScopedOrFail($id),
            default => abort(422, 'Tipo inválido.'),
        };

        $model = $type === 'cto' ? Cto::class : CaixaEmenda::class;

        $copy = $model::create($this->copyAttributes(
            $type,
            $source,
            (float) $request->input('lat'),
            (float) $request->input('lng'),
            $request->input('name') ?: $source->name
        ));

        return response()->json([
            'message' => ($type === 'cto' ? 'CTO' : 'CEO').' duplicada.',
            'element' => [
                'type' => $type,
                'id' => $copy->id,
                'code' => $copy->code,
                'name' => $copy->name,
                'lat' => (float) $copy->latitude,
                'lng' => (float) $copy->longitude,
                'capacity' => $copy->capacity,
                'status' => $copy->status,
                'color' => $type === 'cto' ? $copy->color : null,
            ],
        ], 201);
    }

    /**
     * Campos que a copia herda. used_ports, fusoes, splitter_config e olt_port
     * ficam de fora de proposito: sao o estado de uso da original e nao fazem
     * sentido numa copia que ainda nao tem nada ligado. distance_from_start
     * tambem, porque e a posicao do poste ao longo da rua original.
     */
    private function copyAttributes(string $type, Cto|CaixaEmenda $source, float $lat, float $lng, string $nameBase): array
    {
        $model = $type === 'cto' ? Cto::class : CaixaEmenda::class;

        $attributes = [
            'ftth_project_id' => $source->ftth_project_id,
            'name' => $this->freeCopyName($nameBase, $model),
            'code' => $this->freeCopyCode($source->code, $model),
            'latitude' => $lat,
            'longitude' => $lng,
            'capacity' => $source->capacity,
            'status' => $source->status,
            'street' => $source->street,
            'number' => $source->number,
            'neighborhood' => $source->neighborhood,
            'city' => $source->city,
            'state' => $source->state,
            'zipcode' => $source->zipcode,
            'notes' => $source->notes,
        ];

        if ($type === 'cto') {
            // A copia nasce na mesma CEO para o agrupamento continuar valendo.
            $attributes['caixa_emenda_id'] = $source->caixa_emenda_id;
            $attributes['color'] = $source->color;
        }

        return $attributes;
    }

    /**
     * code e unico na tabela e o original ja ocupa o valor, entao a copia
     * recebe o sufixo -C. Duplicar a mesma CTO varias vezes empilha -C2, -C3.
     */
    private function freeCopyCode(string $base, string $model): string
    {
        $code = $base.'-C';
        $tentativas = 1;

        while ($model::withTrashed()->where('code', $code)->exists() && $tentativas < 1000) {
            $tentativas++;
            $code = $base.'-C'.$tentativas;
        }

        return $code;
    }

    private function freeCopyName(string $base, string $model): string
    {
        $name = $base.' (cópia)';
        $tentativas = 1;

        while ($model::where('name', $name)->exists() && $tentativas < 1000) {
            $tentativas++;
            $name = $base.' (cópia '.$tentativas.')';
        }

        return $name;
    }

    private function assertParent(string $type, $id): Cto|CaixaEmenda
    {
        $parent = match ($type) {
            'cto' => Cto::find($id),
            'caixa' => CaixaEmenda::find($id),
            default => null,
        };

        abort_unless($parent, 422, 'Progenitor não encontrado. Escolha uma CEO ou CTO existente.');

        return $parent;
    }

    private function splitterPayload(FtthSplitter $splitter): array
    {
        return [
            'type' => 'splitter',
            'id' => $splitter->id,
            'code' => $splitter->code,
            'name' => $splitter->name,
            'lat' => (float) $splitter->latitude,
            'lng' => (float) $splitter->longitude,
            'ratio' => $splitter->ratio,
            'output_ports' => $splitter->output_ports,
            'parent_type' => $splitter->parent_type,
            'parent_id' => $splitter->parent_id,
            'parent_code' => $splitter->parentLabel(),
        ];
    }

    public function destroySplitter(int $id)
    {
        $splitter = FtthSplitter::findScopedOrFail($id);
        $splitter->connections()->delete();
        $splitter->delete();

        return response()->json(['message' => 'Splitter removido.']);
    }

    public function storeConnection(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:255',
            'source_type' => 'required|in:cto,caixa,splitter,olt,ponto',
            'source_id' => 'nullable|integer',
            'source_port' => 'nullable|integer',
            'fiber_link_id' => ['nullable', 'integer', function ($attribute, $value, Closure $fail) {
                if ($value && ! FtthFiberLink::scoped()->whereKey($value)->exists()) {
                    $fail('Fibra invalida.');
                }
            }],
            'target_type' => 'required|in:cto,caixa,splitter,olt,ponto',
            'target_id' => 'nullable|integer',
            'target_port' => 'nullable|integer',
        ]);

        $project = $this->ensureCityProject($request->input('city'));

        $sourceType = $request->input('source_type');
        $targetType = $request->input('target_type');

        $this->assertElement($sourceType, $request->input('source_id'));
        $this->assertElement($targetType, $request->input('target_id'));

        // Validação de capacidade dos splitters
        foreach ([['type' => $sourceType, 'id' => $request->input('source_id'), 'port' => $request->input('source_port')],
            ['type' => $targetType, 'id' => $request->input('target_id'), 'port' => $request->input('target_port')]] as $ep) {
            if ($ep['type'] !== 'splitter') {
                continue;
            }

            $splitter = FtthSplitter::findScopedOrFail((int) $ep['id']);

            if ($ep['port'] === 0) {
                // Porta de entrada: apenas 1 conexão
                $hasInput = FtthConnection::where('source_type', 'splitter')->where('source_id', $splitter->id)->where('source_port', 0)
                    ->orWhere(function ($q) use ($splitter) {
                        $q->where('target_type', 'splitter')->where('target_id', $splitter->id)->where('target_port', 0);
                    })
                    ->exists();

                if ($hasInput) {
                    return response()->json(['message' => "Entrada do splitter {$splitter->name} já está conectada."], 422);
                }
            } else {
                // Porta de saída ocupada?
                $occupied = FtthConnection::where('source_type', 'splitter')->where('source_id', $splitter->id)->where('source_port', $ep['port'])
                    ->orWhere(function ($q) use ($splitter, $ep) {
                        $q->where('target_type', 'splitter')->where('target_id', $splitter->id)->where('target_port', $ep['port']);
                    })
                    ->exists();

                if ($occupied) {
                    return response()->json(['message' => "Porta {$ep['port']} do splitter {$splitter->name} já está ocupada."], 422);
                }

                if ($ep['port'] < 1 || $ep['port'] > $splitter->output_ports) {
                    return response()->json(['message' => "Splitter {$splitter->name} tem apenas {$splitter->output_ports} portas de saída."], 422);
                }
            }
        }

        $connection = FtthConnection::create([
            'ftth_project_id' => $project->id,
            'source_type' => $sourceType,
            'source_id' => $request->input('source_id'),
            'source_port' => $request->input('source_port'),
            'fiber_link_id' => $request->input('fiber_link_id'),
            'target_type' => $targetType,
            'target_id' => $request->input('target_id'),
            'target_port' => $request->input('target_port'),
        ]);

        return response()->json([
            'message' => 'Conexão criada.',
            'connection' => [
                'id' => $connection->id,
                'source_type' => $connection->source_type,
                'source_id' => $connection->source_id,
                'source_port' => $connection->source_port,
                'source_code' => $this->elementCode($connection->source_type, $connection->source_id),
                'fiber_link_id' => $connection->fiber_link_id,
                'fiber_name' => $connection->fiber_link_id ? (FtthFiberLink::withTrashed()->whereKey($connection->fiber_link_id)->value('name')) : null,
                'target_type' => $connection->target_type,
                'target_id' => $connection->target_id,
                'target_port' => $connection->target_port,
                'target_code' => $this->elementCode($connection->target_type, $connection->target_id),
            ],
        ], 201);
    }

    public function destroyConnection(int $id)
    {
        $connection = FtthConnection::findScopedOrFail($id);
        $connection->delete();

        return response()->json(['message' => 'Conexão removida.']);
    }

    private function assertElement(string $type, $id): void
    {
        if ($type === 'olt' || $type === 'ponto') {
            return;
        }

        abort_unless($id, 422, 'Identificador do elemento obrigatório.');

        $exists = match ($type) {
            'cto' => Cto::scoped()->whereKey($id)->exists(),
            'caixa' => CaixaEmenda::scoped()->whereKey($id)->exists(),
            'splitter' => FtthSplitter::scoped()->whereKey($id)->exists(),
            default => false,
        };

        abort_unless($exists, 422, "Elemento {$type} #{$id} não encontrado.");
    }

    private function polylineLength(array $geometry): float
    {
        $total = 0.0;
        $count = count($geometry);

        for ($i = 1; $i < $count; $i++) {
            $a = $geometry[$i - 1];
            $b = $geometry[$i];

            $lat1 = (float) ($a['lat'] ?? $a[0] ?? 0);
            $lng1 = (float) ($a['lng'] ?? $a[1] ?? 0);
            $lat2 = (float) ($b['lat'] ?? $b[0] ?? 0);
            $lng2 = (float) ($b['lng'] ?? $b[1] ?? 0);

            $total += $this->haversine($lat1, $lng1, $lat2, $lng2);
        }

        return $total;
    }

    public function report(string $city)
    {
        $ctos = Cto::scoped()->whereNull('deleted_at')->where('city', $city)->get();
        $caixas = CaixaEmenda::scoped()->whereNull('deleted_at')->where('city', $city)->get();
        $project = $this->findCityProject($city);
        $fibers = $project ? $project->fiberLinks()->get() : collect();
        $splitters = $project ? $project->splitters()->get() : collect();
        $connections = $project ? $project->connections()->get() : collect();

        $fiberByType = $fibers->groupBy('type')->map(fn ($group) => [
            'count' => $group->count(),
            'length_meters' => (float) $group->sum('length_meters'),
        ]);

        $occupiedPorts = $connections
            ->filter(fn ($c) => $c->source_type === 'splitter' && $c->source_port)
            ->count();

        return response()->json([
            'city' => $city,
            'stats' => [
                'ctos' => $ctos->count(),
                'cto_capacity_total' => (int) $ctos->sum('capacity'),
                'cto_capacity_used' => (int) $ctos->sum('used_ports'),
                'caixas' => $caixas->count(),
                'splitters' => $splitters->count(),
                'splitter_output_total' => (int) $splitters->sum('output_ports'),
                'splitter_output_used' => $occupiedPorts,
                'fibers' => $fibers->count(),
                'fiber_total_meters' => (float) $fibers->sum('length_meters'),
                'connections' => $connections->count(),
            ],
            'fiber_by_type' => $fiberByType,
            'capacity' => [
                'cto_used_pct' => $ctos->sum('capacity') > 0 ? round($ctos->sum('used_ports') / max(1, $ctos->sum('capacity')) * 100, 1) : 0,
                'splitter_used_pct' => $splitters->sum('output_ports') > 0 ? round($occupiedPorts / max(1, $splitters->sum('output_ports')) * 100, 1) : 0,
            ],
        ]);
    }

    public function validate(string $city)
    {
        $project = $this->findCityProject($city);

        if (! $project) {
            return response()->json(['city' => $city, 'issues' => collect()]);
        }

        $connections = $project->connections()->get();
        $splitters = $project->splitters()->get();
        $fibers = $project->fiberLinks()->get();

        $issues = collect();

        // Conexões órfãs (referenciam elemento inexistente/deletado)
        foreach ($connections as $c) {
            foreach (['source' => $c->source_type, 'target' => $c->target_type] as $side => $type) {
                if (in_array($type, ['cto', 'caixa', 'splitter'])) {
                    $id = $c->{$side.'_id'};
                    $exists = match ($type) {
                        'cto' => Cto::withTrashed()->visibleToUser()->whereKey($id)->whereNotNull('deleted_at')->exists(),
                        'caixa' => CaixaEmenda::withTrashed()->visibleToUser()->whereKey($id)->whereNotNull('deleted_at')->exists(),
                        'splitter' => FtthSplitter::withTrashed()->visibleToUser()->whereKey($id)->whereNotNull('deleted_at')->exists(),
                        default => false,
                    };
                    if ($exists) {
                        $issues->push([
                            'level' => 'erro',
                            'type' => 'conexao_orfa',
                            'message' => "Conexão #{$c->id} aponta para {$side} ({$type} #{$id}) que foi excluído.",
                        ]);
                    }
                }
            }
            if ($c->fiber_link_id && ! $fibers->contains('id', $c->fiber_link_id)) {
                $issues->push([
                    'level' => 'erro',
                    'type' => 'fibra_inexistente',
                    'message' => "Conexão #{$c->id} referencia fibra #{$c->fiber_link_id} que não existe no projeto.",
                ]);
            }
        }

        // Portas de splitter duplicadas
        $seen = [];
        foreach ($connections as $c) {
            if ($c->source_type === 'splitter' && $c->source_port !== null) {
                $key = $c->source_id.':'.$c->source_port;
                if (isset($seen[$key])) {
                    $issues->push([
                        'level' => 'erro',
                        'type' => 'porta_duplicada',
                        'message' => "Splitter #{$c->source_id} porta {$c->source_port} usada por mais de uma conexão.",
                    ]);
                }
                $seen[$key] = true;
            }
            if ($c->target_type === 'splitter' && $c->target_port !== null && $c->target_port > 0) {
                $key = 't:'.$c->target_id.':'.$c->target_port;
                if (isset($seen[$key])) {
                    $issues->push([
                        'level' => 'erro',
                        'type' => 'porta_duplicada',
                        'message' => "Splitter #{$c->target_id} porta {$c->target_port} usada por mais de uma conexão.",
                    ]);
                }
                $seen[$key] = true;
            }
        }

        // Splitters excedendo entrada (mais de 1 conexão de entrada)
        foreach ($splitters as $splitter) {
            $inputs = $connections->filter(fn ($c) => ($c->target_type === 'splitter' && $c->target_id === $splitter->id && $c->target_port == 0)
                || ($c->source_type === 'splitter' && $c->source_id === $splitter->id && $c->source_port == 0)
            )->count();
            if ($inputs > 1) {
                $issues->push([
                    'level' => 'erro',
                    'type' => 'entrada_excedida',
                    'message' => "Splitter {$splitter->name} (#{$splitter->id}) tem {$inputs} conexões na entrada (permitido: 1).",
                ]);
            }
        }

        // Splitter sem progenitor: nao pertence a nenhuma CEO nem CTO, entao
        // nao da para saber de onde sai a fibra.
        foreach ($splitters as $splitter) {
            if ($splitter->parent_type && $splitter->parent_id) {
                continue;
            }

            $issues->push([
                'level' => 'erro',
                'type' => 'splitter_sem_progenitor',
                'message' => "Splitter {$splitter->name} (#{$splitter->id}) não pertence a nenhuma CEO ou CTO.",
            ]);
        }

        return response()->json(['city' => $city, 'issues' => $issues->values()]);
    }

    public function exportKml(string $city)
    {
        $ctos = Cto::scoped()->whereNull('deleted_at')->where('city', $city)->get();
        $caixas = CaixaEmenda::scoped()->whereNull('deleted_at')->where('city', $city)->get();
        $project = $this->findCityProject($city);
        $fibers = $project ? $project->fiberLinks()->get() : collect();
        $splitters = $project ? $project->splitters()->get() : collect();
        $connections = $project ? $project->connections()->get() : collect();

        if ($ctos->isEmpty() && $caixas->isEmpty() && $fibers->isEmpty()) {
            return back()->withErrors(['city' => "Nenhum dado de rede para {$city}."]);
        }

        $xml = $this->buildKml($city, $ctos, $caixas, $fibers, $splitters, $connections);

        $filename = 'FTTH_editado_'.preg_replace('/[^a-zA-Z0-9]/', '_', $city).'_'.date('Ymd_His').'.kml';

        return response($xml, 200)
            ->header('Content-Type', 'application/vnd.google-earth.kml+xml')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
    }

    public function exportCsv(string $city)
    {
        $ctos = Cto::scoped()->whereNull('deleted_at')->where('city', $city)->get();
        $caixas = CaixaEmenda::scoped()->whereNull('deleted_at')->where('city', $city)->get();
        $project = $this->findCityProject($city);
        $fibers = $project ? $project->fiberLinks()->get() : collect();
        $splitters = $project ? $project->splitters()->get() : collect();
        $connections = $project ? $project->connections()->get() : collect();

        $filename = 'FTTH_editado_'.preg_replace('/[^a-zA-Z0-9]/', '_', $city).'_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($ctos, $caixas, $fibers, $splitters, $connections) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $writeSection = function (string $title) use ($out) {
                fputcsv($out, []);
                fputcsv($out, [strtoupper($title)]);
            };

            // CTOs
            $writeSection('CTOS ('.$ctos->count().')');
            fputcsv($out, ['Codigo', 'Nome', 'Rua', 'Latitude', 'Longitude', 'Capacidade', 'Portas Usadas', 'Status']);
            foreach ($ctos as $c) {
                fputcsv($out, [$c->code, $c->name, $c->street, $c->latitude, $c->longitude, $c->capacity, $c->used_ports, $c->status]);
            }

            // Caixas de emenda
            $writeSection('CAIXAS DE EMENDA ('.$caixas->count().')');
            fputcsv($out, ['Codigo', 'Nome', 'Rua', 'Latitude', 'Longitude', 'Capacidade', 'Portas Usadas', 'Status']);
            foreach ($caixas as $c) {
                fputcsv($out, [$c->code, $c->name, $c->street, $c->latitude, $c->longitude, $c->capacity, $c->used_ports, $c->status]);
            }

            // Splitters
            $writeSection('SPLITTERS ('.$splitters->count().')');
            fputcsv($out, ['Codigo', 'Nome', 'Latitude', 'Longitude', 'Splitter', 'Portas Saida']);
            foreach ($splitters as $s) {
                fputcsv($out, [$s->code, $s->name, $s->latitude, $s->longitude, $s->ratio ?: ($s->input_ports.'x'.$s->output_ports), $s->output_ports]);
            }

            // Fibras
            $writeSection('FIBRA LANCADA ('.$fibers->count().') - '.number_format((float) $fibers->sum('length_meters'), 0).'m');
            fputcsv($out, ['Nome', 'Tipo', 'Comprimento (m)', 'Qtde Fibras', 'Cor Tubo', 'Pontos (lat,lng)']);
            foreach ($fibers as $f) {
                $pts = collect((array) ($f->geometry ?? []))
                    ->map(fn ($p) => (float) ($p['lat'] ?? $p[0] ?? 0).','.(float) ($p['lng'] ?? $p[1] ?? 0))
                    ->implode(';');
                fputcsv($out, [$f->name, $f->type, (float) $f->length_meters, $f->fiber_count, $f->tube_color, $pts]);
            }

            // Conexões
            $writeSection('CONEXOES ('.$connections->count().')');
            fputcsv($out, ['Origem', 'Porta', 'Fibra Id', 'Destino', 'Porta']);
            foreach ($connections as $cnx) {
                fputcsv($out, [
                    $cnx->source_type.'#'.($cnx->source_id ?? '-').($this->elementCode($cnx->source_type, $cnx->source_id) ? ' ('.$this->elementCode($cnx->source_type, $cnx->source_id).')' : ''),
                    $cnx->source_port ?? '',
                    $cnx->fiber_link_id ?? '',
                    $cnx->target_type.'#'.($cnx->target_id ?? '-').($this->elementCode($cnx->target_type, $cnx->target_id) ? ' ('.$this->elementCode($cnx->target_type, $cnx->target_id).')' : ''),
                    $cnx->target_port ?? '',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildKml(string $city, $ctos, $caixas, $fibers, $splitters, $connections): string
    {
        $codeTotal = $splitters->count() + $fibers->count();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<kml xmlns="http://www.opengis.net/kml/2.2">'."\n";
        $xml .= '<Document>'."\n";
        $xml .= '  <name>FTTH editado - '.htmlspecialchars($city).'</name>'."\n";
        $xml .= '  <description>Rede FTTH editada no Editor de Rede. '.$ctos->count().' CTOs, '.$caixas->count().' CE, '.$splitters->count().' splitters, '.$fibers->count().' fibras.</description>'."\n";

        // Estilos
        foreach ([
            'cto-style' => ['color' => 'ff0000ff', 'icon' => 'target.png', 'scale' => '0.8', 'label' => '0.7'],
            'caixa-style' => ['color' => 'ff00ff00', 'icon' => 'square.png', 'scale' => '1.0', 'label' => '0.8'],
            'splitter-style' => ['color' => 'ff8f5bff', 'icon' => 'diamond3.png', 'scale' => '1.1', 'label' => '0.8'],
        ] as $id => $st) {
            $xml .= '  <Style id="'.$id.'">'."\n";
            $xml .= '    <IconStyle><color>'.$st['color'].'</color><scale>'.$st['scale'].'</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/'.$st['icon'].'</href></Icon></IconStyle>'."\n";
            $xml .= '    <LabelStyle><scale>'.$st['label'].'</scale></LabelStyle>'."\n";
            $xml .= '  </Style>'."\n";
        }
        $xml .= '  <Style id="fiber-line">'."\n";
        $xml .= '    <LineStyle><color>ff00aaff</color><width>3</width></LineStyle>'."\n";
        $xml .= '  </Style>'."\n";
        $xml .= '  <Style id="conn-line">'."\n";
        $xml .= '    <LineStyle><color>ff00e6ff</color><width>2</width></LineStyle>'."\n";
        $xml .= '  </Style>'."\n";

        // Caixas
        $xml .= '  <Folder><name>Caixas de Emenda ('.$caixas->count().')</name>'."\n";
        foreach ($caixas as $caixa) {
            $xml .= '    <Placemark>'."\n";
            $xml .= '      <name>'.htmlspecialchars($caixa->code).' - '.htmlspecialchars($caixa->street ?? '').'</name>'."\n";
            $xml .= '      <styleUrl>#caixa-style</styleUrl>'."\n";
            $xml .= '      <description><![CDATA[<b>Codigo:</b> '.htmlspecialchars($caixa->code).'<br/><b>Nome:</b> '.htmlspecialchars($caixa->name).'<br/><b>Rua:</b> '.htmlspecialchars($caixa->street ?? '-').'<br/><b>Capacidade:</b> '.$caixa->capacity.'<br/><b>Portas usadas:</b> '.$caixa->used_ports.']]></description>'."\n";
            $xml .= '      <Point><coordinates>'.$caixa->longitude.','.$caixa->latitude.',0</coordinates></Point>'."\n";
            $xml .= '    </Placemark>'."\n";
        }
        $xml .= '  </Folder>'."\n";

        // CTOs
        $xml .= '  <Folder><name>CTOs ('.$ctos->count().')</name>'."\n";
        foreach ($ctos as $cto) {
            $xml .= '    <Placemark>'."\n";
            $xml .= '      <name>'.htmlspecialchars($cto->code).' - '.htmlspecialchars($cto->street ?? '').'</name>'."\n";
            $xml .= '      <styleUrl>#cto-style</styleUrl>'."\n";
            $xml .= '      <description><![CDATA[<b>Codigo:</b> '.htmlspecialchars($cto->code).'<br/><b>Rua:</b> '.htmlspecialchars($cto->street ?? '-').'<br/><b>Capacidade:</b> '.$cto->capacity.'<br/><b>Portas usadas:</b> '.$cto->used_ports.']]></description>'."\n";
            $xml .= '      <Point><coordinates>'.$cto->longitude.','.$cto->latitude.',0</coordinates></Point>'."\n";
            $xml .= '    </Placemark>'."\n";
        }
        $xml .= '  </Folder>'."\n";

        // Splitters
        $xml .= '  <Folder><name>Splitters ('.$splitters->count().')</name>'."\n";
        foreach ($splitters as $splitter) {
            $xml .= '    <Placemark>'."\n";
            $xml .= '      <name>'.htmlspecialchars($splitter->code ?: $splitter->name).'</name>'."\n";
            $xml .= '      <styleUrl>#splitter-style</styleUrl>'."\n";
            $xml .= '      <description><![CDATA[<b>Nome:</b> '.htmlspecialchars($splitter->name).'<br/><b>Splitter:</b> '.htmlspecialchars($splitter->ratio ?: ($splitter->input_ports.'x'.$splitter->output_ports)).'<br/><b>Saidas:</b> '.$splitter->output_ports.']]></description>'."\n";
            $xml .= '      <Point><coordinates>'.$splitter->longitude.','.$splitter->latitude.',0</coordinates></Point>'."\n";
            $xml .= '    </Placemark>'."\n";
        }
        $xml .= '  </Folder>'."\n";

        // Fibras (polilinhas)
        $xml .= '  <Folder><name>Fibra lancada ('.$fibers->count().') '.number_format($fibers->sum('length_meters'), 0).'m</name>'."\n";
        foreach ($fibers as $fiber) {
            $pts = (array) ($fiber->geometry ?? []);
            if (count($pts) < 2) {
                continue;
            }
            $coords = implode(' ', array_map(fn ($p) => (float) ($p['lng'] ?? $p[1] ?? 0).','.(float) ($p['lat'] ?? $p[0] ?? 0).',0', $pts));
            $xml .= '    <Placemark>'."\n";
            $xml .= '      <name>'.htmlspecialchars($fiber->name ?: ('Fibra '.$fiber->type)).'</name>'."\n";
            $xml .= '      <styleUrl>#fiber-line</styleUrl>'."\n";
            $xml .= '      <description><![CDATA[<b>Tipo:</b> '.htmlspecialchars($fiber->type).'<br/><b>Comprimento:</b> '.number_format((float) $fiber->length_meters, 0).'m<br/><b>Fibras:</b> '.htmlspecialchars((string) ($fiber->fiber_count ?: '-')).']]></description>'."\n";
            $xml .= '      <LineString><tessellate>1</tessellate><coordinates>'.$coords.'</coordinates></LineString>'."\n";
            $xml .= '    </Placemark>'."\n";
        }
        $xml .= '  </Folder>'."\n";

        // Conexões (representadas como linha entre pontos dos elementos)
        if ($connections->isNotEmpty()) {
            $xml .= '  <Folder><name>Conexoes ('.$connections->count().')</name>'."\n";
            $positionCache = [];
            foreach ($connections as $cnx) {
                $start = $this->connectionPosition($cnx, 'source', $positionCache);
                $end = $this->connectionPosition($cnx, 'target', $positionCache);
                if (! $start) {
                    continue;
                }
                $end = $end ?: $start;
                $xml .= '    <Placemark>'."\n";
                $xml .= '      <name>'.htmlspecialchars((string) ($cnx->source_type.' p'.($cnx->source_port ?? '-'))).' → '.htmlspecialchars((string) ($cnx->target_type.' p'.($cnx->target_port ?? '-'))).'</name>'."\n";
                $xml .= '      <styleUrl>#conn-line</styleUrl>'."\n";
                $xml .= '      <LineString><tessellate>1</tessellate><coordinates>'.$start[1].','.$start[0].',0 '.$end[1].','.$end[0].',0</coordinates></LineString>'."\n";
                $xml .= '    </Placemark>'."\n";
            }
            $xml .= '  </Folder>'."\n";
        }

        $xml .= '</Document>'."\n";
        $xml .= '</kml>';

        return $xml;
    }

    private function connectionPosition(FtthConnection $cnx, string $side, array &$cache): ?array
    {
        $type = $cnx->{$side.'_type'};
        $id = $cnx->{$side.'_id'};
        $key = $side.'_'.$type.'_'.$id;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $pos = match ($type) {
            'cto' => (fn ($m) => $m ? [(float) $m->latitude, (float) $m->longitude] : null)(Cto::withTrashed()->visibleToUser()->whereKey($id)->first()),
            'caixa' => (fn ($m) => $m ? [(float) $m->latitude, (float) $m->longitude] : null)(CaixaEmenda::withTrashed()->visibleToUser()->whereKey($id)->first()),
            'splitter' => (fn ($m) => $m ? [(float) $m->latitude, (float) $m->longitude] : null)(FtthSplitter::withTrashed()->visibleToUser()->whereKey($id)->first()),
            default => null,
        };

        $cache[$key] = $pos;

        return $pos;
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($dLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c * 1000;
    }
}
