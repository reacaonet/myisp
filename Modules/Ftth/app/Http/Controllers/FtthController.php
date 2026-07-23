<?php

namespace Modules\Ftth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ftth\Models\Cto;
use Modules\Ftth\Models\CaixaEmenda;
use Modules\Ftth\Services\KmlNetworkGenerator;

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

        return view('ftth::dashboard', compact('stats', 'recentCtos', 'recentCaixas'));
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

        return view('ftth::ctos.index', compact('ctos', 'cities'));
    }

    public function createCto()
    {
        $caixas = CaixaEmenda::where('status', 'active')->orderBy('code')->get();
        return view('ftth::ctos.create', compact('caixas'));
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

        return redirect()->route('ftth.ctos.index')
            ->with('success', 'CTO criada com sucesso.');
    }

    public function showCto($id)
    {
        $cto = Cto::with('caixaEmenda')->findOrFail($id);
        return view('ftth::ctos.show', compact('cto'));
    }

    public function editCto($id)
    {
        $cto = Cto::findOrFail($id);
        $caixas = CaixaEmenda::where('status', 'active')->orderBy('code')->get();
        return view('ftth::ctos.edit', compact('cto', 'caixas'));
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

        return redirect()->route('ftth.ctos.index')
            ->with('success', 'CTO atualizada com sucesso.');
    }

    public function destroyCto($id)
    {
        $cto = Cto::findOrFail($id);
        $cto->delete();

        return redirect()->route('ftth.ctos.index')
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

        return view('ftth::caixas.index', compact('caixas', 'cities'));
    }

    public function createCaixa()
    {
        return view('ftth::caixas.create');
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

        return redirect()->route('ftth.caixas.index')
            ->with('success', 'Caixa de Emenda criada com sucesso.');
    }

    public function showCaixa($id)
    {
        $caixa = CaixaEmenda::with('ctos')->withCount('ctos')->findOrFail($id);
        return view('ftth::caixas.show', compact('caixa'));
    }

    public function editCaixa($id)
    {
        $caixa = CaixaEmenda::findOrFail($id);
        return view('ftth::caixas.edit', compact('caixa'));
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

        return redirect()->route('ftth.caixas.index')
            ->with('success', 'Caixa de Emenda atualizada com sucesso.');
    }

    public function destroyCaixa($id)
    {
        $caixa = CaixaEmenda::findOrFail($id);
        $caixa->delete();

        return redirect()->route('ftth.caixas.index')
            ->with('success', 'Caixa de Emenda removida com sucesso.');
    }

    public function generateNetwork()
    {
        return view('ftth::generate');
    }

    public function generateCity()
    {
        return redirect()->route('ftth.generate.cities');
    }

    public function generateCities()
    {
        return view('ftth::generate-cities');
    }

    public function runGenerateCity(Request $request)
    {
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

            return view('ftth::generate-result', [
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

        return view('ftth::generate-result-multi', [
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

        return view('ftth::generate-result', [
            'result' => $result,
            'street_name' => $request->input('street_name'),
        ]);
    }
}
