<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\FtthProject;
use Modules\PortalInfra\Models\FtthFiberLink;
use Modules\PortalInfra\Models\FtthSplitter;
use Modules\PortalInfra\Models\FtthConnection;

class FtthEditorController extends Controller
{
    private const EARTH_RADIUS_KM = 6371.0;

    public function index(Request $request)
    {
        $cities = Cto::whereNull('deleted_at')->whereNotNull('city')->where('city', '!=', '')
            ->distinct()->orderBy('city')->pluck('city')->values();
        $selected = $request->get('cidade');

        return view('infra::editor.index', compact('cities', 'selected'));
    }

    public function data(Request $request)
    {
        $city = $request->get('cidade');
        $cities = Cto::whereNull('deleted_at')->whereNotNull('city')->where('city', '!=', '')
            ->distinct()->orderBy('city')->pluck('city')->values();

        if (!$city) {
            return response()->json([
                'cities' => $cities,
                'city' => $cities->first() ?: null,
                'ctos' => [],
                'caixas' => [],
                'splitters' => [],
                'fibers' => [],
            ]);
        }

        $ctos = Cto::whereNull('deleted_at')->where('city', $city)
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
                'caixa_id' => $c->caixa_emenda_id,
            ]);

        $caixas = CaixaEmenda::whereNull('deleted_at')->where('city', $city)
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
            ]);

        $project = $this->findCityProject($city);

        $splitters = $project
            ? $project->splitters()->get()->map(fn ($s) => [
                'type' => 'splitter',
                'id' => $s->id,
                'code' => $s->code,
                'name' => $s->name,
                'lat' => (float) $s->latitude,
                'lng' => (float) $s->longitude,
                'ratio' => $s->ratio,
                'output_ports' => $s->output_ports,
            ])
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
        if (!$id) {
            return null;
        }

        return match ($type) {
            'cto' => Cto::withTrashed()->whereKey($id)->value('code'),
            'caixa' => CaixaEmenda::withTrashed()->whereKey($id)->value('code'),
            'splitter' => FtthSplitter::withTrashed()->whereKey($id)->value('code')
                ?: FtthSplitter::withTrashed()->whereKey($id)->value('name'),
            default => null,
        };
    }

    private function findCityProject(string $city): ?FtthProject
    {
        return FtthProject::where('city', $city)->orderByDesc('created_at')->first();
    }

    private function ensureCityProject(string $city): FtthProject
    {
        $project = $this->findCityProject($city);

        if ($project) {
            return $project;
        }

        return FtthProject::create([
            'name' => 'Rede ' . $city,
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
                $model = Cto::findOrFail($id);
                break;
            case 'caixa':
                $model = CaixaEmenda::findOrFail($id);
                break;
            case 'splitter':
                $model = FtthSplitter::findOrFail($id);
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
        $fiber = FtthFiberLink::findOrFail($id);

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
        $fiber = FtthFiberLink::findOrFail($id);
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
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'input_ports' => 'required|integer|min:1|max:4',
            'output_ports' => 'required|integer|in:8,16,32,64',
        ]);

        $splitter = FtthSplitter::create([
            'ftth_project_id' => $this->ensureCityProject($request->input('city'))->id,
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'latitude' => (float) $request->input('lat'),
            'longitude' => (float) $request->input('lng'),
            'input_ports' => $request->input('input_ports'),
            'output_ports' => $request->input('output_ports'),
            'ratio' => $request->input('input_ports') . 'x' . $request->input('output_ports'),
        ]);

        return response()->json([
            'message' => 'Splitter adicionado.',
            'splitter' => [
                'type' => 'splitter',
                'id' => $splitter->id,
                'code' => $splitter->code,
                'name' => $splitter->name,
                'lat' => (float) $splitter->latitude,
                'lng' => (float) $splitter->longitude,
                'ratio' => $splitter->ratio,
                'output_ports' => $splitter->output_ports,
            ],
        ], 201);
    }

    public function destroySplitter(int $id)
    {
        $splitter = FtthSplitter::findOrFail($id);
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
            'fiber_link_id' => 'nullable|integer|exists:ftth_fiber_links,id',
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

            $splitter = FtthSplitter::findOrFail($ep['id']);

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
        $connection = FtthConnection::findOrFail($id);
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
            'cto' => Cto::whereKey($id)->exists(),
            'caixa' => CaixaEmenda::whereKey($id)->exists(),
            'splitter' => FtthSplitter::whereKey($id)->exists(),
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
        $ctos = Cto::whereNull('deleted_at')->where('city', $city)->get();
        $caixas = CaixaEmenda::whereNull('deleted_at')->where('city', $city)->get();
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

        if (!$project) {
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
                    $id = $c->{$side . '_id'};
                    $exists = match ($type) {
                        'cto' => Cto::withTrashed()->whereKey($id)->whereNotNull('deleted_at')->exists(),
                        'caixa' => CaixaEmenda::withTrashed()->whereKey($id)->whereNotNull('deleted_at')->exists(),
                        'splitter' => FtthSplitter::withTrashed()->whereKey($id)->whereNotNull('deleted_at')->exists(),
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
            if ($c->fiber_link_id && !$fibers->contains('id', $c->fiber_link_id)) {
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
                $key = $c->source_id . ':' . $c->source_port;
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
                $key = 't:' . $c->target_id . ':' . $c->target_port;
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
            $inputs = $connections->filter(fn ($c) =>
                ($c->target_type === 'splitter' && $c->target_id === $splitter->id && $c->target_port == 0)
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

        return response()->json(['city' => $city, 'issues' => $issues->values()]);
    }

    public function exportKml(string $city)
    {
        $ctos = Cto::whereNull('deleted_at')->where('city', $city)->get();
        $caixas = CaixaEmenda::whereNull('deleted_at')->where('city', $city)->get();
        $project = $this->findCityProject($city);
        $fibers = $project ? $project->fiberLinks()->get() : collect();
        $splitters = $project ? $project->splitters()->get() : collect();
        $connections = $project ? $project->connections()->get() : collect();

        if ($ctos->isEmpty() && $caixas->isEmpty() && $fibers->isEmpty()) {
            return back()->withErrors(['city' => "Nenhum dado de rede para {$city}."]);
        }

        $xml = $this->buildKml($city, $ctos, $caixas, $fibers, $splitters, $connections);

        $filename = 'FTTH_editado_' . preg_replace('/[^a-zA-Z0-9]/', '_', $city) . '_' . date('Ymd_His') . '.kml';

        return response($xml, 200)
            ->header('Content-Type', 'application/vnd.google-earth.kml+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportCsv(string $city)
    {
        $ctos = Cto::whereNull('deleted_at')->where('city', $city)->get();
        $caixas = CaixaEmenda::whereNull('deleted_at')->where('city', $city)->get();
        $project = $this->findCityProject($city);
        $fibers = $project ? $project->fiberLinks()->get() : collect();
        $splitters = $project ? $project->splitters()->get() : collect();
        $connections = $project ? $project->connections()->get() : collect();

        $filename = 'FTTH_editado_' . preg_replace('/[^a-zA-Z0-9]/', '_', $city) . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($city, $ctos, $caixas, $fibers, $splitters, $connections) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $writeSection = function (string $title) use ($out) {
                fputcsv($out, []);
                fputcsv($out, [strtoupper($title)]);
            };

            // CTOs
            $writeSection('CTOS (' . $ctos->count() . ')');
            fputcsv($out, ['Codigo', 'Nome', 'Rua', 'Latitude', 'Longitude', 'Capacidade', 'Portas Usadas', 'Status']);
            foreach ($ctos as $c) {
                fputcsv($out, [$c->code, $c->name, $c->street, $c->latitude, $c->longitude, $c->capacity, $c->used_ports, $c->status]);
            }

            // Caixas de emenda
            $writeSection('CAIXAS DE EMENDA (' . $caixas->count() . ')');
            fputcsv($out, ['Codigo', 'Nome', 'Rua', 'Latitude', 'Longitude', 'Capacidade', 'Portas Usadas', 'Status']);
            foreach ($caixas as $c) {
                fputcsv($out, [$c->code, $c->name, $c->street, $c->latitude, $c->longitude, $c->capacity, $c->used_ports, $c->status]);
            }

            // Splitters
            $writeSection('SPLITTERS (' . $splitters->count() . ')');
            fputcsv($out, ['Codigo', 'Nome', 'Latitude', 'Longitude', 'Splitter', 'Portas Saida']);
            foreach ($splitters as $s) {
                fputcsv($out, [$s->code, $s->name, $s->latitude, $s->longitude, $s->ratio ?: ($s->input_ports . 'x' . $s->output_ports), $s->output_ports]);
            }

            // Fibras
            $writeSection('FIBRA LANCADA (' . $fibers->count() . ') - ' . number_format((float) $fibers->sum('length_meters'), 0) . 'm');
            fputcsv($out, ['Nome', 'Tipo', 'Comprimento (m)', 'Qtde Fibras', 'Cor Tubo', 'Pontos (lat,lng)']);
            foreach ($fibers as $f) {
                $pts = collect((array) ($f->geometry ?? []))
                    ->map(fn ($p) => (float) ($p['lat'] ?? $p[0] ?? 0) . ',' . (float) ($p['lng'] ?? $p[1] ?? 0))
                    ->implode(';');
                fputcsv($out, [$f->name, $f->type, (float) $f->length_meters, $f->fiber_count, $f->tube_color, $pts]);
            }

            // Conexões
            $writeSection('CONEXOES (' . $connections->count() . ')');
            fputcsv($out, ['Origem', 'Porta', 'Fibra Id', 'Destino', 'Porta']);
            foreach ($connections as $cnx) {
                fputcsv($out, [
                    $cnx->source_type . '#' . ($cnx->source_id ?? '-') . ($this->elementCode($cnx->source_type, $cnx->source_id) ? ' (' . $this->elementCode($cnx->source_type, $cnx->source_id) . ')' : ''),
                    $cnx->source_port ?? '',
                    $cnx->fiber_link_id ?? '',
                    $cnx->target_type . '#' . ($cnx->target_id ?? '-') . ($this->elementCode($cnx->target_type, $cnx->target_id) ? ' (' . $this->elementCode($cnx->target_type, $cnx->target_id) . ')' : ''),
                    $cnx->target_port ?? '',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildKml(string $city, $ctos, $caixas, $fibers, $splitters, $connections): string
    {
        $codeTotal = $splitters->count() + $fibers->count();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $xml .= '<Document>' . "\n";
        $xml .= '  <name>FTTH editado - ' . htmlspecialchars($city) . '</name>' . "\n";
        $xml .= '  <description>Rede FTTH editada no Editor de Rede. ' . $ctos->count() . ' CTOs, ' . $caixas->count() . ' CE, ' . $splitters->count() . ' splitters, ' . $fibers->count() . ' fibras.</description>' . "\n";

        // Estilos
        foreach ([
            'cto-style' => ['color' => 'ff0000ff', 'icon' => 'target.png', 'scale' => '0.8', 'label' => '0.7'],
            'caixa-style' => ['color' => 'ff00ff00', 'icon' => 'square.png', 'scale' => '1.0', 'label' => '0.8'],
            'splitter-style' => ['color' => 'ff8f5bff', 'icon' => 'diamond3.png', 'scale' => '1.1', 'label' => '0.8'],
        ] as $id => $st) {
            $xml .= '  <Style id="' . $id . '">' . "\n";
            $xml .= '    <IconStyle><color>' . $st['color'] . '</color><scale>' . $st['scale'] . '</scale><Icon><href>http://maps.google.com/mapfiles/kml/shapes/' . $st['icon'] . '</href></Icon></IconStyle>' . "\n";
            $xml .= '    <LabelStyle><scale>' . $st['label'] . '</scale></LabelStyle>' . "\n";
            $xml .= '  </Style>' . "\n";
        }
        $xml .= '  <Style id="fiber-line">' . "\n";
        $xml .= '    <LineStyle><color>ff00aaff</color><width>3</width></LineStyle>' . "\n";
        $xml .= '  </Style>' . "\n";
        $xml .= '  <Style id="conn-line">' . "\n";
        $xml .= '    <LineStyle><color>ff00e6ff</color><width>2</width></LineStyle>' . "\n";
        $xml .= '  </Style>' . "\n";

        // Caixas
        $xml .= '  <Folder><name>Caixas de Emenda (' . $caixas->count() . ')</name>' . "\n";
        foreach ($caixas as $caixa) {
            $xml .= '    <Placemark>' . "\n";
            $xml .= '      <name>' . htmlspecialchars($caixa->code) . ' - ' . htmlspecialchars($caixa->street ?? '') . '</name>' . "\n";
            $xml .= '      <styleUrl>#caixa-style</styleUrl>' . "\n";
            $xml .= '      <description><![CDATA[<b>Codigo:</b> ' . htmlspecialchars($caixa->code) . '<br/><b>Nome:</b> ' . htmlspecialchars($caixa->name) . '<br/><b>Rua:</b> ' . htmlspecialchars($caixa->street ?? '-') . '<br/><b>Capacidade:</b> ' . $caixa->capacity . '<br/><b>Portas usadas:</b> ' . $caixa->used_ports . ']]></description>' . "\n";
            $xml .= '      <Point><coordinates>' . $caixa->longitude . ',' . $caixa->latitude . ',0</coordinates></Point>' . "\n";
            $xml .= '    </Placemark>' . "\n";
        }
        $xml .= '  </Folder>' . "\n";

        // CTOs
        $xml .= '  <Folder><name>CTOs (' . $ctos->count() . ')</name>' . "\n";
        foreach ($ctos as $cto) {
            $xml .= '    <Placemark>' . "\n";
            $xml .= '      <name>' . htmlspecialchars($cto->code) . ' - ' . htmlspecialchars($cto->street ?? '') . '</name>' . "\n";
            $xml .= '      <styleUrl>#cto-style</styleUrl>' . "\n";
            $xml .= '      <description><![CDATA[<b>Codigo:</b> ' . htmlspecialchars($cto->code) . '<br/><b>Rua:</b> ' . htmlspecialchars($cto->street ?? '-') . '<br/><b>Capacidade:</b> ' . $cto->capacity . '<br/><b>Portas usadas:</b> ' . $cto->used_ports . ']]></description>' . "\n";
            $xml .= '      <Point><coordinates>' . $cto->longitude . ',' . $cto->latitude . ',0</coordinates></Point>' . "\n";
            $xml .= '    </Placemark>' . "\n";
        }
        $xml .= '  </Folder>' . "\n";

        // Splitters
        $xml .= '  <Folder><name>Splitters (' . $splitters->count() . ')</name>' . "\n";
        foreach ($splitters as $splitter) {
            $xml .= '    <Placemark>' . "\n";
            $xml .= '      <name>' . htmlspecialchars($splitter->code ?: $splitter->name) . '</name>' . "\n";
            $xml .= '      <styleUrl>#splitter-style</styleUrl>' . "\n";
            $xml .= '      <description><![CDATA[<b>Nome:</b> ' . htmlspecialchars($splitter->name) . '<br/><b>Splitter:</b> ' . htmlspecialchars($splitter->ratio ?: ($splitter->input_ports . 'x' . $splitter->output_ports)) . '<br/><b>Saidas:</b> ' . $splitter->output_ports . ']]></description>' . "\n";
            $xml .= '      <Point><coordinates>' . $splitter->longitude . ',' . $splitter->latitude . ',0</coordinates></Point>' . "\n";
            $xml .= '    </Placemark>' . "\n";
        }
        $xml .= '  </Folder>' . "\n";

        // Fibras (polilinhas)
        $xml .= '  <Folder><name>Fibra lancada (' . $fibers->count() . ') ' . number_format($fibers->sum('length_meters'), 0) . 'm</name>' . "\n";
        foreach ($fibers as $fiber) {
            $pts = (array) ($fiber->geometry ?? []);
            if (count($pts) < 2) {
                continue;
            }
            $coords = implode(' ', array_map(fn ($p) => (float) ($p['lng'] ?? $p[1] ?? 0) . ',' . (float) ($p['lat'] ?? $p[0] ?? 0) . ',0', $pts));
            $xml .= '    <Placemark>' . "\n";
            $xml .= '      <name>' . htmlspecialchars($fiber->name ?: ('Fibra ' . $fiber->type)) . '</name>' . "\n";
            $xml .= '      <styleUrl>#fiber-line</styleUrl>' . "\n";
            $xml .= '      <description><![CDATA[<b>Tipo:</b> ' . htmlspecialchars($fiber->type) . '<br/><b>Comprimento:</b> ' . number_format((float) $fiber->length_meters, 0) . 'm<br/><b>Fibras:</b> ' . htmlspecialchars((string) ($fiber->fiber_count ?: '-')) . ']]></description>' . "\n";
            $xml .= '      <LineString><tessellate>1</tessellate><coordinates>' . $coords . '</coordinates></LineString>' . "\n";
            $xml .= '    </Placemark>' . "\n";
        }
        $xml .= '  </Folder>' . "\n";

        // Conexões (representadas como linha entre pontos dos elementos)
        if ($connections->isNotEmpty()) {
            $xml .= '  <Folder><name>Conexoes (' . $connections->count() . ')</name>' . "\n";
            $positionCache = [];
            foreach ($connections as $cnx) {
                $start = $this->connectionPosition($cnx, 'source', $positionCache);
                $end = $this->connectionPosition($cnx, 'target', $positionCache);
                if (!$start) continue;
                $end = $end ?: $start;
                $xml .= '    <Placemark>' . "\n";
                $xml .= '      <name>' . htmlspecialchars((string) ($cnx->source_type . ' p' . ($cnx->source_port ?? '-'))) . ' → ' . htmlspecialchars((string) ($cnx->target_type . ' p' . ($cnx->target_port ?? '-'))) . '</name>' . "\n";
                $xml .= '      <styleUrl>#conn-line</styleUrl>' . "\n";
                $xml .= '      <LineString><tessellate>1</tessellate><coordinates>' . $start[1] . ',' . $start[0] . ',0 ' . $end[1] . ',' . $end[0] . ',0</coordinates></LineString>' . "\n";
                $xml .= '    </Placemark>' . "\n";
            }
            $xml .= '  </Folder>' . "\n";
        }

        $xml .= '</Document>' . "\n";
        $xml .= '</kml>';

        return $xml;
    }

    private function connectionPosition(FtthConnection $cnx, string $side, array &$cache): ?array
    {
        $type = $cnx->{$side . '_type'};
        $id = $cnx->{$side . '_id'};
        $key = $side . '_' . $type . '_' . $id;

        if (isset($cache[$key])) {
            return $cache[$key];
        }

        $pos = match ($type) {
            'cto' => (fn ($m) => $m ? [(float) $m->latitude, (float) $m->longitude] : null)(Cto::withTrashed()->whereKey($id)->first()),
            'caixa' => (fn ($m) => $m ? [(float) $m->latitude, (float) $m->longitude] : null)(CaixaEmenda::withTrashed()->whereKey($id)->first()),
            'splitter' => (fn ($m) => $m ? [(float) $m->latitude, (float) $m->longitude] : null)(FtthSplitter::withTrashed()->whereKey($id)->first()),
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