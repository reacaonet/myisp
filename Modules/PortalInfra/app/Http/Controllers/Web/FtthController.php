<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\CaixaEmenda;
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

        $ctos = $query->orderBy('code')->paginate(20);
        $cities = Cto::whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('infra::ctos.index', compact('ctos', 'cities'));
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
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'active';
        Cto::create($validated);

        return redirect()->route('infra.ftth.ctos.index')
            ->with('success', 'CTO criada com sucesso.');
    }

    public function showCto($id)
    {
        $cto = Cto::with('caixaEmenda')->findOrFail($id);
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
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
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

        $caixas = $query->orderBy('code')->paginate(20);
        $cities = CaixaEmenda::whereNotNull('city')->distinct()->pluck('city')->sort()->values();

        return view('infra::caixas.index', compact('caixas', 'cities'));
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
        ]);

        $validated['status'] = 'active';
        CaixaEmenda::create($validated);

        return redirect()->route('infra.ftth.caixas.index')
            ->with('success', 'Caixa de Emenda criada com sucesso.');
    }

    public function showCaixa($id)
    {
        $caixa = CaixaEmenda::with('ctos')->withCount('ctos')->findOrFail($id);
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

        if ($hasMultipleCities) {
            return $this->runGenerateMultipleCities($request, $state, $prefix);
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
            $result = $generator->generateFromStreets($streets, $prefix, $cityName, $state);

            $cityLabel = $hasBounds
                ? 'Regiao delimitada'
                : $request->input('city_name') . ($state ? '/' . $state : '');

            return view('infra::generate-result', [
                'result' => $result,
                'street_name' => $cityLabel,
            ]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['city_name' => 'Erro ao consultar Overpass API: ' . $e->getMessage()]);
        }
    }

    private function runGenerateMultipleCities(Request $request, string $state, string $prefix)
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
                $result = $generator->generateFromStreets($streets, $cityPrefix, $city, $state);

                $allResults[] = [
                    'city' => $city,
                    'result' => $result,
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

    public function runGenerate(Request $request)
    {
        $request->validate([
            'coordinates' => 'required|string',
            'street_name' => 'required|string|max:255',
            'prefix' => 'nullable|string|max:10',
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
            $request->input('prefix', '')
        );

        return view('infra::generate-result', [
            'result' => $result,
            'street_name' => $request->input('street_name'),
        ]);
    }
}
