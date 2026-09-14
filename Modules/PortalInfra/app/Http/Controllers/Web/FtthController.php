<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\FtthProject;
use Modules\PortalInfra\Models\FtthFusion;
use Modules\PortalInfra\Services\KmlNetworkGenerator;

class FtthController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_ctos' => Cto::count(),
            'active_ctos' => Cto::where('status', 'active')->count(),
            'total_caixas' => CaixaEmenda::count(),
            'active_caixas' => CaixaEmenda::where('status', 'active')->count(),
            'total_capacity' => Cto::sum('capacity'),
            'total_used' => Cto::sum('used_ports'),
        ];

        $recentCtos = Cto::with('caixaEmenda')->latest()->take(10)->get();
        $recentCaixas = CaixaEmenda::withCount('ctos')->latest()->take(10)->get();

        return view('infra::dashboard', compact('stats', 'recentCtos', 'recentCaixas'));
    }

    public function indexCtos(Request $request)
    {
        $query = Cto::with('caixaEmenda');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('street', 'like', "%{$search}%");
            });
        }

        if ($city = $request->get('city')) {
            $query->where('city', $city);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($projectId = $request->get('project')) {
            $query->where('ftth_project_id', $projectId);
        }

        $ctos = $query->orderBy('code')->paginate(20);
        $cities = Cto::whereNotNull('city')->distinct()->pluck('city')->sort()->values();
        $projects = FtthProject::orderBy('name')->get();

        return view('infra::ctos.index', compact('ctos', 'cities', 'projects'));
    }

    public function createCto()
    {
        $caixas = CaixaEmenda::where('status', 'active')->orderBy('code')->get();
        return view('infra::ctos.create', compact('caixas'));
    }

    public function storeCto(Request $request)
    {
        $validated = $request->validate([
            'caixa_emenda_id' => 'nullable|exists:caixas_emenda,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|integer|min:1|max:256',
            'fiber_fusions' => 'nullable|integer|min:0',
            'splitter_config' => 'nullable|string|max:50',
            'olt_port' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
            'project_notes' => 'nullable|string',
        ]);

        $validated['status'] = 'active';
        Cto::create($validated);

        return redirect()->route('infra.ftth.ctos.index')
            ->with('success', 'CTO criada com sucesso.');
    }

    public function showCto($id)
    {
        $cto = Cto::with(['caixaEmenda', 'fusions' => fn ($q) => $q->orderBy('fiber_number')])->findOrFail($id);
        return view('infra::ctos.show', compact('cto'));
    }

    public function editCto($id)
    {
        $cto = Cto::findOrFail($id);
        $caixas = CaixaEmenda::where('status', 'active')->orderBy('code')->get();
        return view('infra::ctos.edit', compact('cto', 'caixas'));
    }

    public function updateCto(Request $request, $id)
    {
        $cto = Cto::findOrFail($id);

        $validated = $request->validate([
            'caixa_emenda_id' => 'nullable|exists:caixas_emenda,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|integer|min:1|max:256',
            'status' => 'required|in:active,inactive,maintenance',
            'fiber_fusions' => 'nullable|integer|min:0',
            'splitter_config' => 'nullable|string|max:50',
            'olt_port' => 'nullable|string|max:50',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
            'project_notes' => 'nullable|string',
        ]);

        $cto->update($validated);

        return redirect()->route('infra.ftth.ctos.index')
            ->with('success', 'CTO atualizada com sucesso.');
    }

    public function destroyCto($id)
    {
        $cto = Cto::findOrFail($id);
        $cto->delete();

        return redirect()->route('infra.ftth.ctos.index')
            ->with('success', 'CTO removida com sucesso.');
    }

    public function indexCaixas(Request $request)
    {
        $query = CaixaEmenda::withCount('ctos');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('street', 'like', "%{$search}%");
            });
        }

        if ($city = $request->get('city')) {
            $query->where('city', $city);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($projectId = $request->get('project')) {
            $query->where('ftth_project_id', $projectId);
        }

        $caixas = $query->orderBy('code')->paginate(20);
        $cities = CaixaEmenda::whereNotNull('city')->distinct()->pluck('city')->sort()->values();
        $projects = FtthProject::orderBy('name')->get();

        return view('infra::caixas.index', compact('caixas', 'cities', 'projects'));
    }

    public function createCaixa()
    {
        return view('infra::caixas.create');
    }

    public function storeCaixa(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|integer|min:1|max:288',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
            'fiber_fusions' => 'nullable|integer|min:0',
            'splitter_config' => 'nullable|string|max:50',
            'project_notes' => 'nullable|string',
        ]);

        $validated['status'] = 'active';
        CaixaEmenda::create($validated);

        return redirect()->route('infra.ftth.caixas.index')
            ->with('success', 'Caixa de Emenda criada com sucesso.');
    }

    public function showCaixa($id)
    {
        $caixa = CaixaEmenda::with(['ctos', 'fusions' => fn ($q) => $q->orderBy('fiber_number')])->withCount('ctos')->findOrFail($id);
        return view('infra::caixas.show', compact('caixa'));
    }

    public function editCaixa($id)
    {
        $caixa = CaixaEmenda::findOrFail($id);
        return view('infra::caixas.edit', compact('caixa'));
    }

    public function updateCaixa(Request $request, $id)
    {
        $caixa = CaixaEmenda::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'capacity' => 'required|integer|min:1|max:288',
            'status' => 'required|in:active,inactive,maintenance',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
            'fiber_fusions' => 'nullable|integer|min:0',
            'splitter_config' => 'nullable|string|max:50',
            'project_notes' => 'nullable|string',
        ]);

        $caixa->update($validated);

        return redirect()->route('infra.ftth.caixas.index')
            ->with('success', 'Caixa de Emenda atualizada com sucesso.');
    }

    public function destroyCaixa($id)
    {
        $caixa = CaixaEmenda::findOrFail($id);
        $caixa->delete();

        return redirect()->route('infra.ftth.caixas.index')
            ->with('success', 'Caixa de Emenda removida com sucesso.');
    }

    // ==================== Planos de Fusao ====================

    public function storeFusion(Request $request)
    {
        $validated = $request->validate([
            'cto_id' => 'nullable|exists:ctos,id',
            'caixa_emenda_id' => 'nullable|exists:caixas_emenda,id',
            'ftth_project_id' => 'nullable|exists:ftth_projects,id',
            'fiber_number' => 'nullable|string|max:20',
            'olt_port' => 'nullable|string|max:50',
            'tube' => 'nullable|string|max:20',
            'destination' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['cto_id'] ?? null) && empty($validated['caixa_emenda_id'] ?? null)) {
            return back()->withErrors(['fiber_number' => 'Informe a CTO ou a Caixa de Emenda.']);
        }

        $validated['status'] = 'pending';
        FtthFusion::create($validated);

        return back()->with('success', 'Fusao adicionada ao plano.');
    }

    public function updateFusion(Request $request, $id)
    {
        $fusion = FtthFusion::findOrFail($id);

        $validated = $request->validate([
            'fiber_number' => 'nullable|string|max:20',
            'olt_port' => 'nullable|string|max:50',
            'tube' => 'nullable|string|max:20',
            'destination' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,done',
            'notes' => 'nullable|string',
        ]);

        $fusion->update($validated);

        if ($fusion->cto_id) {
            return redirect()->route('infra.ftth.ctos.show', $fusion->cto_id)->with('success', 'Fusao atualizada.');
        }

        return redirect()->route('infra.ftth.caixas.show', $fusion->caixa_emenda_id)->with('success', 'Fusao atualizada.');
    }

    public function destroyFusion($id)
    {
        $fusion = FtthFusion::with('cto', 'caixaEmenda')->findOrFail($id);
        $redirect = $fusion->cto_id
            ? route('infra.ftth.ctos.show', $fusion->cto_id)
            : route('infra.ftth.caixas.show', $fusion->caixa_emenda_id);

        $fusion->delete();

        return redirect($redirect)->with('success', 'Fusao removida do plano.');
    }

    public function indexProjects(Request $request)
    {
        $query = FtthProject::withCount('ctos', 'caixas');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $projects = $query->orderByDesc('created_at')->paginate(20);

        return view('infra::projects.index', compact('projects'));
    }

    public function createProject()
    {
        return view('infra::projects.create');
    }

    public function storeProject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'prefix' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $project = FtthProject::create($validated);

        return redirect()->route('infra.ftth.projects.show', $project)
            ->with('success', 'Projeto criado com sucesso.');
    }

    public function showProject($id)
    {
        $project = FtthProject::withCount('ctos', 'caixas')->findOrFail($id);
        $ctos = $project->ctos()->with('caixaEmenda')->orderBy('code')->get();
        $caixas = $project->caixas()->withCount('ctos')->orderBy('code')->get();

        return view('infra::projects.show', compact('project', 'ctos', 'caixas'));
    }

    public function editProject($id)
    {
        $project = FtthProject::findOrFail($id);
        return view('infra::projects.edit', compact('project'));
    }

    public function updateProject(Request $request, $id)
    {
        $project = FtthProject::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'prefix' => 'nullable|string|max:10',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->route('infra.ftth.projects.show', $project)
            ->with('success', 'Projeto atualizado com sucesso.');
    }

    public function destroyProject($id)
    {
        $project = FtthProject::findOrFail($id);
        $project->ctos()->update(['ftth_project_id' => null]);
        $project->caixas()->update(['ftth_project_id' => null]);
        $project->delete();

        return redirect()->route('infra.ftth.projects.index')
            ->with('success', 'Projeto removido com sucesso.');
    }

    public function bulkDestroyCtos(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Nenhuma CTO selecionada.');
        }

        Cto::whereIn('id', $ids)->delete();

        return redirect()->route('infra.ftth.ctos.index')
            ->with('success', count($ids) . ' CTO(s) excluida(s) com sucesso.');
    }

    public function bulkDestroyCaixas(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Nenhuma caixa selecionada.');
        }

        CaixaEmenda::whereIn('id', $ids)->delete();

        return redirect()->route('infra.ftth.caixas.index')
            ->with('success', count($ids) . ' caixa(s) excluida(s) com sucesso.');
    }

    public function generateNetwork()
    {
        return view('infra::generate');
    }

    public function generateCity()
    {
        return redirect()->route('infra.ftth.generate.cities');
    }

    public function generateCities()
    {
        return view('infra::generate-cities');
    }

    public function exportKml()
    {
        $cities = Cto::whereNotNull('city')->distinct()->pluck('city')->sort()->values();
        return view('infra::export-kml', compact('cities'));
    }

    public function map()
    {
        $cities = Cto::whereNotNull('city')->distinct()->pluck('city')->sort()->values();
        return view('infra::map', compact('cities'));
    }

    public function mapData(Request $request)
    {
        $queryCto = Cto::select('id', 'code', 'name', 'latitude', 'longitude', 'street', 'city', 'capacity', 'used_ports', 'status', 'caixa_emenda_id');
        $queryCaixa = CaixaEmenda::select('id', 'code', 'name', 'latitude', 'longitude', 'street', 'city', 'capacity', 'used_ports', 'status');

        if ($city = $request->get('city')) {
            $queryCto->where('city', $city);
            $queryCaixa->where('city', $city);
        }

        $ctos = $queryCto->orderBy('code')->get()->map(fn($c) => [
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
            'caixa_id' => $c->caixa_emenda_id,
        ]);

        $caixas = $queryCaixa->orderBy('code')->get()->map(fn($c) => [
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
        ]);

        return response()->json(['ctos' => $ctos, 'caixas' => $caixas]);
    }

    public function downloadKml(string $city)
    {
        $ctos = Cto::where('city', $city)->orderBy('code')->get();
        $caixas = CaixaEmenda::where('city', $city)->with('ctos')->orderBy('code')->get();

        if ($ctos->isEmpty() && $caixas->isEmpty()) {
            return back()->withErrors(['city' => "Nenhuma CTO ou Caixa encontrada para {$city}."]);
        }

        $xml = $this->buildKml($city, $ctos, $caixas);

        $filename = 'FTTH_' . preg_replace('/[^a-zA-Z0-9]/', '_', $city) . '_' . date('Ymd_His') . '.kml';

        return response($xml, 200)
            ->header('Content-Type', 'application/vnd.google-earth.kml+xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportCsvCtos(Request $request)
    {
        $query = Cto::with(['caixaEmenda', 'ftthProject']);

        if ($projectId = $request->get('project')) {
            $query->where('ftth_project_id', $projectId);
        }
        if ($city = $request->get('city')) {
            $query->where('city', $city);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $filename = 'CTOs_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Codigo', 'Nome', 'Cidade', 'UF', 'Rua', 'Bairro', 'Latitude', 'Longitude', 'Capacidade', 'Portas Usadas', 'Porta OLT', 'Splitter', 'Caixa de Emenda', 'Projeto', 'Status', 'Distancia (m)', 'Observacoes']);
            $query->chunkById(500, function ($ctos) use ($out) {
                foreach ($ctos as $cto) {
                    fputcsv($out, [
                        $cto->code,
                        $cto->name,
                        $cto->city,
                        $cto->state,
                        $cto->street,
                        $cto->neighborhood,
                        $cto->latitude,
                        $cto->longitude,
                        $cto->capacity,
                        $cto->used_ports,
                        $cto->olt_port,
                        $cto->splitter_config,
                        $cto->caixaEmenda->code ?? '',
                        $cto->ftthProject->name ?? '',
                        $cto->status,
                        $cto->distance_from_start,
                        $cto->project_notes,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportCsvCaixas(Request $request)
    {
        $query = CaixaEmenda::with(['ftthProject'])->withCount('ctos');

        if ($projectId = $request->get('project')) {
            $query->where('ftth_project_id', $projectId);
        }
        if ($city = $request->get('city')) {
            $query->where('city', $city);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $filename = 'Caixas_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Codigo', 'Nome', 'Cidade', 'UF', 'Rua', 'Bairro', 'Latitude', 'Longitude', 'Capacidade', 'Portas Usadas', 'Porta OLT', 'Splitter', 'CTOs Vinculadas', 'Projeto', 'Status', 'Observacoes']);
            $query->chunkById(500, function ($caixas) use ($out) {
                foreach ($caixas as $caixa) {
                    fputcsv($out, [
                        $caixa->code,
                        $caixa->name,
                        $caixa->city,
                        $caixa->state,
                        $caixa->street,
                        $caixa->neighborhood,
                        $caixa->latitude,
                        $caixa->longitude,
                        $caixa->capacity,
                        $caixa->used_ports,
                        $caixa->olt_port,
                        $caixa->splitter_config,
                        $caixa->ctos_count,
                        $caixa->ftthProject->name ?? '',
                        $caixa->status,
                        $caixa->project_notes,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildKml(string $city, $ctos, $caixas): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $xml .= '<Document>' . "\n";
        $xml .= '  <name>FTTH - ' . htmlspecialchars($city) . '</name>' . "\n";
        $xml .= '  <description>Rede FTTH gerada automaticamente para ' . htmlspecialchars($city) . '</description>' . "\n";

        $xml .= '  <Style id="cto-style">' . "\n";
        $xml .= '    <IconStyle>' . "\n";
        $xml .= '      <color>ff0000ff</color>' . "\n";
        $xml .= '      <scale>0.8</scale>' . "\n";
        $xml .= '      <Icon><href>http://maps.google.com/mapfiles/kml/shapes/target.png</href></Icon>' . "\n";
        $xml .= '    </IconStyle>' . "\n";
        $xml .= '    <LabelStyle><scale>0.7</scale></LabelStyle>' . "\n";
        $xml .= '  </Style>' . "\n";

        $xml .= '  <Style id="caixa-style">' . "\n";
        $xml .= '    <IconStyle>' . "\n";
        $xml .= '      <color>ff00ff00</color>' . "\n";
        $xml .= '      <scale>1.0</scale>' . "\n";
        $xml .= '      <Icon><href>http://maps.google.com/mapfiles/kml/shapes/square.png</href></Icon>' . "\n";
        $xml .= '    </IconStyle>' . "\n";
        $xml .= '    <LabelStyle><scale>0.8</scale></LabelStyle>' . "\n";
        $xml .= '  </Style>' . "\n";

        $xml .= '  <Folder>' . "\n";
        $xml .= '    <name>Caixas de Emenda (' . $caixas->count() . ')</name>' . "\n";
        foreach ($caixas as $caixa) {
            $xml .= '    <Placemark>' . "\n";
            $xml .= '      <name>' . htmlspecialchars($caixa->code) . ' - ' . htmlspecialchars($caixa->street ?? '') . '</name>' . "\n";
            $xml .= '      <styleUrl>#caixa-style</styleUrl>' . "\n";
            $xml .= '      <description><![CDATA[';
            $xml .= '<b>Codigo:</b> ' . htmlspecialchars($caixa->code) . '<br/>';
            $xml .= '<b>Nome:</b> ' . htmlspecialchars($caixa->name) . '<br/>';
            $xml .= '<b>Rua:</b> ' . htmlspecialchars($caixa->street ?? '-') . '<br/>';
            $xml .= '<b>Cidade:</b> ' . htmlspecialchars($caixa->city ?? '-') . '<br/>';
            $xml .= '<b>Capacidade:</b> ' . $caixa->capacity . '<br/>';
            $xml .= '<b>Portas usadas:</b> ' . $caixa->used_ports . '<br/>';
            $xml .= '<b>CTOs:</b> ' . $caixa->ctos_count . '<br/>';
            $xml .= ']]></description>' . "\n";
            $xml .= '      <Point><coordinates>' . $caixa->longitude . ',' . $caixa->latitude . ',0</coordinates></Point>' . "\n";
            $xml .= '    </Placemark>' . "\n";
        }
        $xml .= '  </Folder>' . "\n";

        $xml .= '  <Folder>' . "\n";
        $xml .= '    <name>CTOs (' . $ctos->count() . ')</name>' . "\n";
        foreach ($ctos as $cto) {
            $xml .= '    <Placemark>' . "\n";
            $xml .= '      <name>' . htmlspecialchars($cto->code) . ' - ' . htmlspecialchars($cto->street ?? '') . '</name>' . "\n";
            $xml .= '      <styleUrl>#cto-style</styleUrl>' . "\n";
            $xml .= '      <description><![CDATA[';
            $xml .= '<b>Codigo:</b> ' . htmlspecialchars($cto->code) . '<br/>';
            $xml .= '<b>Nome:</b> ' . htmlspecialchars($cto->name) . '<br/>';
            $xml .= '<b>Rua:</b> ' . htmlspecialchars($cto->street ?? '-') . '<br/>';
            $xml .= '<b>Cidade:</b> ' . htmlspecialchars($cto->city ?? '-') . '<br/>';
            $xml .= '<b>Capacidade:</b> ' . $cto->capacity . '<br/>';
            $xml .= '<b>Portas usadas:</b> ' . $cto->used_ports . '<br/>';
            if ($cto->caixaEmenda) {
                $xml .= '<b>Caixa:</b> ' . htmlspecialchars($cto->caixaEmenda->code) . '<br/>';
            }
            $xml .= '<b>Distancia:</b> ' . number_format($cto->distance_from_start ?? 0, 0) . 'm do inicio<br/>';
            $xml .= ']]></description>' . "\n";
            $xml .= '      <Point><coordinates>' . $cto->longitude . ',' . $cto->latitude . ',0</coordinates></Point>' . "\n";
            $xml .= '    </Placemark>' . "\n";
        }
        $xml .= '  </Folder>' . "\n";

        $xml .= '</Document>' . "\n";
        $xml .= '</kml>';

        return $xml;
    }

    public function runGenerateCity(Request $request)
    {
        set_time_limit(300);

        $hasBounds = $request->filled('south') && $request->filled('west') && $request->filled('north') && $request->filled('east');
        $hasMultipleCities = $request->has('city_name') && is_array($request->input('city_name'));

        if (!$hasBounds && !$request->filled('city_name')) {
            return back()->withErrors(['city_name' => 'Informe o nome da cidade ou as coordenadas de limite.']);
        }

        $prefix = $request->input('prefix') ?? '';
        $state = $request->input('state') ?? 'MA';
        $ctoCapacity = $request->input('cto_capacity') ?? 8;
        $ctoInterval = $request->input('cto_interval') ?? 250;

        if ($hasMultipleCities) {
            return $this->runGenerateMultipleCities($request, $state, $prefix, $ctoCapacity, $ctoInterval);
        }

        try {
            $generator = new KmlNetworkGenerator();

            if ($hasBounds) {
                $streets = $generator->fetchStreetsByBounds(
                    (float) $request->input('south'),
                    (float) $request->input('west'),
                    (float) $request->input('north'),
                    (float) $request->input('east')
                );
            } else {
                $streets = $generator->fetchStreetsFromOverpass(
                    $request->input('city_name'),
                    $state
                );
            }

            if (empty($streets)) {
                return back()->withErrors(['city_name' => 'Nenhuma rua encontrada. Verifique o nome da cidade e o estado.']);
            }

            $cityName = $request->input('city_name');
            $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $cityName), 0, 4));
            $result = $generator->generateFromStreets($streets, $prefix, $cityName, $state, $ctoCapacity, $ctoInterval);

            $cityLabel = $hasBounds
                ? 'Regiao delimitada'
                : $request->input('city_name') . ($state ? '/' . $state : '');

            if (!$hasBounds) {
                $project = $this->attachProject($cityName, $state, $prefix, $result, false);

                return view('infra::generate-result', [
                    'result' => $result,
                    'street_name' => $cityLabel,
                    'project' => $project,
                ]);
            }

            return view('infra::generate-result', [
                'result' => $result,
                'street_name' => $cityLabel,
            ]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['city_name' => 'Erro ao consultar Overpass API: ' . $e->getMessage()]);
        }
    }

    private function runGenerateMultipleCities(Request $request, string $state, string $prefix, $ctoCapacity = 8, $ctoInterval = 250)
    {
        $cities = $request->input('city_name');
        $allResults = [];
        $totalCtos = 0;
        $totalCaixas = 0;
        $totalStreets = 0;
        $totalDistance = 0;
        $errors = [];

        foreach ($cities as $city) {
            try {
                $generator = new KmlNetworkGenerator();
                $streets = $generator->fetchStreetsFromOverpass($city, $state);

                if (empty($streets)) {
                    $errors[] = "{$city}: Nenhuma rua encontrada";
                    continue;
                }

                $cityPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $city), 0, 4));
                $result = $generator->generateFromStreets($streets, $cityPrefix, $city, $state, $ctoCapacity, $ctoInterval);
                $project = $this->attachProject($city, $state, $cityPrefix, $result, false);

                $allResults[] = [
                    'city' => $city,
                    'result' => $result,
                    'project' => $project,
                ];

                $totalCtos += $result['stats']['total_ctos'];
                $totalCaixas += $result['stats']['total_caixas'];
                $totalStreets += $result['stats']['total_streets'] ?? 0;
                $totalDistance += $result['stats']['total_distance_km'];
            } catch (\RuntimeException $e) {
                $errors[] = "{$city}: " . $e->getMessage();
            }
        }

        if (empty($allResults) && !empty($errors)) {
            return back()->withErrors(['city_name' => implode("\n", $errors)]);
        }

        return view('infra::generate-result-multi', [
            'results' => $allResults,
            'errors' => $errors,
            'stats' => [
                'total_cities' => count($allResults),
                'total_ctos' => $totalCtos,
                'total_caixas' => $totalCaixas,
                'total_streets' => $totalStreets,
                'total_distance_km' => $totalDistance,
            ],
        ]);
    }

    private function attachProject(string $city, string $state, string $prefix, array $result, bool $hasBounds): FtthProject
    {
        $project = FtthProject::create([
            'name' => 'Projeto ' . $city,
            'city' => $city,
            'state' => $state,
            'prefix' => $prefix,
            'status' => 'active',
            'total_streets' => $result['stats']['total_streets'] ?? 0,
            'total_ctos' => $result['stats']['total_ctos'] ?? 0,
            'total_caixas' => $result['stats']['total_caixas'] ?? 0,
            'total_distance_km' => $result['stats']['total_distance_km'] ?? 0,
            'has_bounds' => $hasBounds,
        ]);

        $ctoIds = collect($result['ctos'] ?? [])->pluck('id')->filter()->all();
        if (!empty($ctoIds)) {
            Cto::whereIn('id', $ctoIds)->update(['ftth_project_id' => $project->id]);
        }

        $caixaIds = collect($result['caixas'] ?? [])->pluck('id')->filter()->all();
        if (!empty($caixaIds)) {
            CaixaEmenda::whereIn('id', $caixaIds)->update(['ftth_project_id' => $project->id]);
        }

        return $project;
    }

    public function runGenerate(Request $request)
    {
        $request->validate([
            'coordinates' => 'required|string',
            'street_name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:10',
            'cto_capacity' => 'nullable|integer|min:1|max:256',
            'cto_interval' => 'nullable|integer|min:50|max:1000',
        ]);

        $raw = $request->input('coordinates');
        $lines = array_filter(array_map('trim', explode("\n", $raw)));
        $coordinates = [];

        foreach ($lines as $line) {
            $parts = array_map('trim', explode(',', $line));
            if (count($parts) >= 2) {
                $lat = (float) $parts[0];
                $lng = (float) $parts[1];
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180) {
                    $coordinates[] = ['lat' => $lat, 'lng' => $lng];
                }
            }
        }

        if (count($coordinates) < 2) {
            return back()->withErrors(['coordinates' => 'Insira pelo menos 2 coordenadas validas (lat, lng por linha).']);
        }

        $generator = new KmlNetworkGenerator();
        $result = $generator->generateFromCoordinates(
            $coordinates,
            $request->input('street_name'),
            $request->input('prefix', ''),
            $request->input('cto_capacity', 8),
            $request->input('cto_interval', 250)
        );

        return view('infra::generate-result', [
            'result' => $result,
            'street_name' => $request->input('street_name'),
        ]);
    }
}
