<?php

namespace Modules\PortalInfra\Services;

use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\CaixaEmenda;

class KmlNetworkGenerator
{
    private const EARTH_RADIUS_KM = 6371.0;
    private const CTOS_PER_CAIXA = 4;
    private const URBAN_CELL_SIZE_METERS = 400;
    private const URBAN_MIN_CELL_DENSITY_RATIO = 0.2;
    private const URBAN_MAX_CELL_DISTANCE = 1;
    private const STREET_SEGMENT_JOIN_METERS = 30;
    private const CTO_BASE_CODE = 'CTO';
    private const CAIXA_BASE_CODE = 'CE';
    private const OVERPASS_URLS = [
        'https://maps.mail.ru/osm/tools/overpass/api/interpreter',
        'https://overpass-api.de/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
    ];

    private int $ctoCount = 0;
    private int $totalCtos = 0;
    private int $totalCaixas = 0;
    private int $ctoCapacity = 8;
    private int $ctoIntervalMeters = 250;
    private array $pendingCtoCoords = [];
    private array $generatedCtos = [];
    private array $generatedCaixas = [];
    private string $streetName = '';
    private string $currentPrefix = '';
    private string $currentCity = '';
    private string $currentState = '';
    private ?array $cityPolygon = null;
    private int $skippedOutOfBound = 0;
    private int $skippedTooClose = 0;
    private array $streetCtoCoords = [];

    private function overpassQuery(string $query): array
    {
        $lastError = '';

        foreach (self::OVERPASS_URLS as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => 'data=' . urlencode($query),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded',
                    'User-Agent: MyISP-FTTH-Generator/1.0',
                ],
            ]);

            $body = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            if ($error) {
                $lastError = "cURL error on {$url}: {$error}";
                continue;
            }

            if ($httpCode === 429) {
                $lastError = "Rate limited on {$url}";
                continue;
            }

            if ($httpCode !== 200) {
                $lastError = "HTTP {$httpCode} on {$url}";
                continue;
            }

            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $lastError = "Invalid JSON from {$url}: " . json_last_error_msg();
                continue;
            }

            return $data;
        }

        throw new \RuntimeException("Nenhum servidor Overpass disponivel. Ultimo erro: {$lastError}");
    }

    public function fetchStreetsFromOverpass(string $cityName, string $state = ''): array
    {
        $searchName = $state ? "{$cityName}, {$state}, Brazil" : "{$cityName}, Brazil";

        // Primeiro usa Nominatim para obter o poligono administrativo do municipio.
        // Isso garante que apenas ruas dentro da cidade sejam retornadas
        // (o bounding box puro inclui municipios vizinhos).
        $geo = $this->geocodeWithNominatim($searchName);

        if ($geo && !empty($geo['polygon'])) {
            $this->cityPolygon = $geo['polygon'];

            $streets = $this->fetchStreetsByPolygon($geo['polygon']);
            $streets = $this->filterStreetsByPolygon($streets, $geo['polygon']);
            $streets = $this->filterUrbanStreets($streets);

            if (!empty($streets)) {
                return $streets;
            }

            $streets = $this->fetchStreetsByBounds($geo['south'], $geo['west'], $geo['north'], $geo['east']);
            $streets = $this->filterStreetsByPolygon($streets, $geo['polygon']);
            return $this->filterUrbanStreets($streets);
        }

        if ($geo) {
            if (!empty($geo['polygon'])) {
                $this->cityPolygon = $geo['polygon'];
                $streets = $this->fetchStreetsByBounds($geo['south'], $geo['west'], $geo['north'], $geo['east']);
                $streets = $this->filterStreetsByPolygon($streets, $geo['polygon']);
                return $this->filterUrbanStreets($streets);
            }

            $streets = $this->fetchStreetsByBounds($geo['south'], $geo['west'], $geo['north'], $geo['east']);
            if (!empty($streets)) {
                return $this->filterUrbanStreets($streets);
            }
        }

        // Fallback: busca por area no OSM
        $queries = [
            '[out:json][timeout:60];area["name"="' . $searchName . '"]["admin_level"~"^(7|8)$"]->.searchArea;(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](area.searchArea););out body;>;out skel qt;',
            '[out:json][timeout:60];area["name"="' . $searchName . '"]->.searchArea;(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](area.searchArea););out body;>;out skel qt;',
        ];

        foreach ($queries as $query) {
            try {
                $data = $this->overpassQuery($query);
                $streets = $this->parseOverpassResponse($data);

                if (!empty($streets)) {
                    return $this->filterUrbanStreets($streets);
                }
            } catch (\RuntimeException $e) {
                continue;
            }
        }

        return [];
    }

    public function getCityPolygon(): ?array
    {
        return $this->cityPolygon;
    }

    private function geocodeWithNominatim(string $query): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($query) . '&format=json&limit=1&polygon_geojson=1&polygon_threshold=0.01';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['User-Agent: MyISP-FTTH/1.0'],
        ]);

        $body = curl_exec($ch);
        curl_close($ch);

        if (!$body) {
            return null;
        }

        $data = json_decode($body, true);

        if (empty($data[0]['boundingbox'])) {
            return null;
        }

        $bb = $data[0]['boundingbox'];

        return [
            'south' => (float) $bb[0],
            'north' => (float) $bb[1],
            'west' => (float) $bb[2],
            'east' => (float) $bb[3],
            'polygon' => $this->extractPolygon($data[0]),
        ];
    }

    private function extractPolygon(array $result): ?array
    {
        $geojson = $result['geojson'] ?? null;

        if (!$geojson || !isset($geojson['type'])) {
            return null;
        }

        $ring = null;

        if ($geojson['type'] === 'Polygon') {
            $ring = $geojson['coordinates'][0] ?? null;
        } elseif ($geojson['type'] === 'MultiPolygon') {
            $largest = null;
            $largestArea = 0;

            foreach ($geojson['coordinates'] as $polygon) {
                $candidate = $polygon[0] ?? [];

                if (count($candidate) < 4) {
                    continue;
                }

                $area = $this->polygonArea($candidate);

                if ($area > $largestArea) {
                    $largestArea = $area;
                    $largest = $candidate;
                }
            }

            $ring = $largest;
        }

        if (!$ring || count($ring) < 4) {
            return null;
        }

        $points = [];

        foreach ($ring as $coord) {
            $points[] = [
                'lat' => (float) $coord[1],
                'lng' => (float) $coord[0],
            ];
        }

        return $points;
    }

    private function polygonArea(array $ring): float
    {
        $area = 0.0;
        $count = count($ring);

        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;
            $area += $ring[$i][0] * $ring[$j][1];
            $area -= $ring[$j][0] * $ring[$i][1];
        }

        return abs($area / 2);
    }

    public function fetchStreetsByPolygon(array $polygon): array
    {
        $points = [];

        foreach ($polygon as $point) {
            $points[] = "{$point['lat']} {$point['lng']}";
        }

        $polyFilter = 'poly:"' . implode(' ', $points) . '"';

        $query = '[out:json][timeout:120];';
        $query .= '(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](' . $polyFilter . '););';
        $query .= 'out body;>;out skel qt;';

        $data = $this->overpassQuery($query);
        return $this->parseOverpassResponse($data);
    }

    public function fetchStreetsByBounds(float $south, float $west, float $north, float $east): array
    {
        $query = '[out:json][timeout:60];';
        $query .= '(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](' . $south . ',' . $west . ',' . $north . ',' . $east . '););';
        $query .= 'out body;>;out skel qt;';

        $data = $this->overpassQuery($query);
        return $this->parseOverpassResponse($data);
    }

    private function filterStreetsByPolygon(array $streets, array $polygon): array
    {
        if (empty($streets)) {
            return [];
        }

        $filtered = [];

        foreach ($streets as $street) {
            $keptNodes = [];
            $nodes = $street['nodes'] ?? [];

            foreach ($nodes as $node) {
                $lat = $node['lat'] ?? $node[0] ?? null;
                $lng = $node['lng'] ?? $node[1] ?? null;

                if ($lat === null || $lng === null) {
                    continue;
                }

                if ($this->isPointInPolygon((float) $lat, (float) $lng, $polygon)) {
                    $keptNodes[] = $node;
                }
            }

            if (count($keptNodes) >= 2) {
                $filtered[] = [
                    'name' => $street['name'],
                    'nodes' => $keptNodes,
                ];
            }
        }

        return $filtered;
    }

    private function filterUrbanStreets(array $streets): array
    {
        if (empty($streets)) {
            return [];
        }

        $nodes = [];
        foreach ($streets as $si => $street) {
            foreach ($street['nodes'] ?? [] as $node) {
                $lat = $node['lat'] ?? $node[0] ?? null;
                $lng = $node['lng'] ?? $node[1] ?? null;

                if ($lat === null || $lng === null) {
                    continue;
                }

                $nodes[] = [
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                    'si' => $si,
                ];
            }
        }

        $count = count($nodes);
        if ($count === 0) {
            return [];
        }

        // Agrupa os nós em células de ~400m e conta a densidade de cada célula.
        // O maior aglomerado de células conectadas e suficientemente densas
        // corresponde à mancha urbana da sede; estradas rurais (rodovias que
        // saem da cidade, estradas de distritos isolados) têm células esparsas
        // e não entram no aglomerado.
        $latDeg = self::URBAN_CELL_SIZE_METERS / 111320.0;

        $cells = [];
        $cellOfNode = [];
        foreach ($nodes as $i => $node) {
            $lngDeg = self::URBAN_CELL_SIZE_METERS / (111320.0 * cos(deg2rad($node['lat'])));
            $cx = (int) floor($node['lng'] / $lngDeg);
            $cy = (int) floor($node['lat'] / $latDeg);
            $key = "{$cx}:{$cy}";
            $cellOfNode[$i] = $key;
            $cells[$key] = ($cells[$key] ?? 0) + 1;
        }

        if (empty($cells)) {
            return $streets;
        }

        $maxDensity = max($cells);
        $minDensity = max(4, (int) floor($maxDensity * self::URBAN_MIN_CELL_DENSITY_RATIO));

        // BFS a partir da célula mais densa, expandindo apenas para células
        // vizinhas (Chebyshev <= 1) com densidade suficiente.
        $seedKey = array_search($maxDensity, $cells, true);
        $keptCells = [];
        $queue = [$seedKey];
        $visited = [$seedKey => true];
        $keptCells[$seedKey] = true;

        while (!empty($queue)) {
            $key = array_pop($queue);
            [$cx, $cy] = explode(':', $key);

            for ($dx = -self::URBAN_MAX_CELL_DISTANCE; $dx <= self::URBAN_MAX_CELL_DISTANCE; $dx++) {
                for ($dy = -self::URBAN_MAX_CELL_DISTANCE; $dy <= self::URBAN_MAX_CELL_DISTANCE; $dy++) {
                    if ($dx === 0 && $dy === 0) {
                        continue;
                    }

                    $nKey = ($cx + $dx) . ':' . ($cy + $dy);
                    if (isset($visited[$nKey])) {
                        continue;
                    }
                    $visited[$nKey] = true;

                    if (($cells[$nKey] ?? 0) >= $minDensity) {
                        $keptCells[$nKey] = true;
                        $queue[] = $nKey;
                    }
                }
            }
        }

        // Mantém apenas os nós dentro da mancha urbana. Ruas longas (ex.: uma
        // rodovia que atravessa a cidade) são truncadas ao trecho urbano.
        $filtered = [];
        foreach ($streets as $si => $street) {
            $keptNodes = [];
            foreach ($street['nodes'] ?? [] as $node) {
                $lat = $node['lat'] ?? $node[0] ?? null;
                $lng = $node['lng'] ?? $node[1] ?? null;

                if ($lat === null || $lng === null) {
                    continue;
                }

                $lngDeg = self::URBAN_CELL_SIZE_METERS / (111320.0 * cos(deg2rad((float) $lat)));
                $cx = (int) floor((float) $lng / $lngDeg);
                $cy = (int) floor((float) $lat / $latDeg);
                $key = $cx . ':' . $cy;

                if (isset($keptCells[$key])) {
                    $keptNodes[] = $node;
                }
            }

            if (count($keptNodes) >= 2) {
                $filtered[] = [
                    'name' => $street['name'],
                    'nodes' => $keptNodes,
                ];
            }
        }

        return $filtered;
    }

    private function isPointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $inside = false;
        $count = count($polygon);

        for ($i = 0, $j = $count - 1; $i < $count; $j = $i++) {
            $latI = $polygon[$i]['lat'];
            $lngI = $polygon[$i]['lng'];
            $latJ = $polygon[$j]['lat'];
            $lngJ = $polygon[$j]['lng'];

            $intersects = (($lngI > $lng) !== ($lngJ > $lng))
                && ($lat < ($latJ - $latI) * ($lng - $lngI) / ($lngJ - $lngI) + $latI);

            if ($intersects) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

    private function parseOverpassResponse(array $data): array
    {
        $nodes = [];
        foreach ($data['elements'] ?? [] as $element) {
            if ($element['type'] === 'node') {
                $nodes[$element['id']] = [
                    'lat' => $element['lat'],
                    'lng' => $element['lon'],
                ];
            }
        }

        $streets = [];
        foreach ($data['elements'] ?? [] as $element) {
            if ($element['type'] !== 'way') {
                continue;
            }

            $streetNodes = [];
            foreach ($element['nodes'] ?? [] as $nodeId) {
                if (isset($nodes[$nodeId])) {
                    $streetNodes[] = $nodes[$nodeId];
                }
            }

            if (count($streetNodes) >= 2) {
                $streets[] = [
                    'name' => $element['tags']['name'] ?? 'Sem nome',
                    'nodes' => $streetNodes,
                ];
            }
        }

        return $streets;
    }

    public function generateFromStreets(array $streets, string $prefix = '', string $city = '', string $state = '', int $ctoCapacity = 8, int $ctoIntervalMeters = 250, ?array $polygon = null): array
    {
        $this->reset();
        $this->currentPrefix = $prefix;
        $this->currentCity = $city;
        $this->currentState = $state;
        $this->ctoCapacity = $ctoCapacity > 0 ? $ctoCapacity : 8;
        $this->ctoIntervalMeters = $ctoIntervalMeters >= 50 && $ctoIntervalMeters <= 1000 ? $ctoIntervalMeters : 250;
        $this->cityPolygon = $polygon;

        // OSM entrega cada rua quebrada em varios ways (um por quadra/cruzamento).
        // Precisamos costurar os segmentos de mesmo nome que se tocam pelas
        // pontas para gerar CTOs ao longo da rua inteira respeitando o intervalo.
        $mergedStreets = $this->mergeStreetsByContinuity($streets);

        foreach ($mergedStreets as $streetIndex => $street) {
            $this->streetName = $street['name'] ?? "Rua {$streetIndex}";
            $nodes = $street['nodes'] ?? [];

            if (count($nodes) < 2) {
                continue;
            }

            $this->processStreet($nodes, $prefix);
        }

        $this->flushPendingCaixa();

        return [
            'ctos' => $this->generatedCtos,
            'caixas' => $this->generatedCaixas,
            'stats' => [
                'total_ctos' => $this->totalCtos,
                'total_caixas' => $this->totalCaixas,
                'total_streets' => count($mergedStreets),
                'total_distance_km' => $this->calculateTotalDistance($mergedStreets),
                'skipped_out_of_bound' => $this->skippedOutOfBound,
                'skipped_too_close' => $this->skippedTooClose,
            ],
        ];
    }

    private function mergeStreetsByContinuity(array $streets): array
    {
        $groups = [];
        foreach ($streets as $street) {
            $name = trim((string) ($street['name'] ?? ''));
            if ($name === '') {
                $name = 'Sem nome';
            }

            $groups[$name][] = $street['nodes'] ?? [];
        }

        $merged = [];
        foreach ($groups as $name => $segments) {
            foreach ($this->chainSegments($segments) as $chain) {
                if (count($chain) >= 2) {
                    $merged[] = [
                        'name' => $name,
                        'nodes' => $chain,
                    ];
                }
            }
        }

        return $merged;
    }

    private function chainSegments(array $segments): array
    {
        $segments = array_values(array_filter($segments, fn ($s) => count($s) >= 2));
        if (empty($segments)) {
            return [];
        }

        // Remove segmentos exatamente duplicados.
        $unique = [];
        $seen = [];
        foreach ($segments as $seg) {
            $key = implode('|', array_map(
                fn ($n) => round((float) ($n['lat'] ?? $n[0]), 6) . ',' . round((float) ($n['lng'] ?? $n[1]), 6),
                $seg
            ));
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $seg;
            }
        }
        $segments = $unique;

        $count = count($segments);
        $used = array_fill(0, $count, false);
        $chains = [];

        for ($i = 0; $i < $count; $i++) {
            if ($used[$i]) {
                continue;
            }
            $used[$i] = true;
            $chain = $segments[$i];

            $changed = true;
            while ($changed) {
                $changed = false;

                $chainFirst = $chain[0];
                $chainLast = $chain[count($chain) - 1];

                for ($j = 0; $j < $count; $j++) {
                    if ($used[$j]) {
                        continue;
                    }

                    $seg = $segments[$j];
                    $segFirst = $seg[0];
                    $segLast = $seg[count($seg) - 1];

                    if ($this->pointsEqual($chainLast, $segFirst)) {
                        $chain = array_merge($chain, array_slice($seg, 1));
                        $used[$j] = true;
                        $changed = true;
                        break;
                    }
                    if ($this->pointsEqual($chainLast, $segLast)) {
                        $chain = array_merge($chain, array_slice(array_reverse($seg), 1));
                        $used[$j] = true;
                        $changed = true;
                        break;
                    }
                    if ($this->pointsEqual($chainFirst, $segLast)) {
                        $chain = array_merge(array_slice($seg, 0, -1), $chain);
                        $used[$j] = true;
                        $changed = true;
                        break;
                    }
                    if ($this->pointsEqual($chainFirst, $segFirst)) {
                        $chain = array_merge(array_slice(array_reverse($seg), 0, -1), $chain);
                        $used[$j] = true;
                        $changed = true;
                        break;
                    }
                }
            }

            $chains[] = $chain;
        }

        return $chains;
    }

    private function pointsEqual(array $a, array $b): bool
    {
        $aLat = (float) ($a['lat'] ?? $a[0]);
        $aLng = (float) ($a['lng'] ?? $a[1]);
        $bLat = (float) ($b['lat'] ?? $b[0]);
        $bLng = (float) ($b['lng'] ?? $b[1]);

        return $this->haversine($aLat, $aLng, $bLat, $bLng) <= self::STREET_SEGMENT_JOIN_METERS;
    }

    public function generateFromCoordinates(array $coordinates, string $streetName = 'Rua Principal', string $prefix = '', int $ctoCapacity = 8, int $ctoIntervalMeters = 250): array
    {
        $this->reset();
        $this->streetName = $streetName;

        $streets = [
            [
                'name' => $streetName,
                'nodes' => $coordinates,
            ],
        ];

        return $this->generateFromStreets($streets, $prefix, '', '', $ctoCapacity, $ctoIntervalMeters);
    }

    private function processStreet(array $nodes, string $prefix): void
    {
        $accumulatedDistance = 0.0;
        $lastCtoDistance = 0.0;
        $lastPoint = null;
        $createdInStreet = false;
        $streetNodeCount = 0;

        foreach ($nodes as $nodeIndex => $node) {
            $lat = $node['lat'] ?? $node[0] ?? null;
            $lng = $node['lng'] ?? $node[1] ?? null;

            if ($lat === null || $lng === null) {
                continue;
            }

            $streetNodeCount++;

            $currentPoint = ['lat' => (float) $lat, 'lng' => (float) $lng];

            if ($lastPoint !== null) {
                $segmentDistance = $this->haversine(
                    $lastPoint['lat'], $lastPoint['lng'],
                    $currentPoint['lat'], $currentPoint['lng']
                );
                $accumulatedDistance += $segmentDistance;

                while (($accumulatedDistance - $lastCtoDistance) >= $this->ctoIntervalMeters) {
                    $remainingInSegment = $accumulatedDistance - $lastCtoDistance;
                    $overshoot = $remainingInSegment - $this->ctoIntervalMeters;

                    $fraction = $segmentDistance > 0
                        ? ($segmentDistance - $overshoot) / $segmentDistance
                        : 0;

                    $ctoLat = $lastPoint['lat'] + ($currentPoint['lat'] - $lastPoint['lat']) * $fraction;
                    $ctoLng = $lastPoint['lng'] + ($currentPoint['lng'] - $lastPoint['lng']) * $fraction;

                    $createdInStreet = $this->createCto($ctoLat, $ctoLng, $prefix, $accumulatedDistance) || $createdInStreet;
                    $lastCtoDistance += $this->ctoIntervalMeters;
                }
            }

            $lastPoint = $currentPoint;
        }

        if (!$createdInStreet && $streetNodeCount > 0 && $lastPoint !== null) {
            foreach ($nodes as $node) {
                $validLat = $node['lat'] ?? $node[0] ?? null;
                $validLng = $node['lng'] ?? $node[1] ?? null;

                if ($validLat === null || $validLng === null) {
                    continue;
                }

                $this->createCto((float) $validLat, (float) $validLng, $prefix, 0.0);
                break;
            }
        }
    }

    private function createCto(float $lat, float $lng, string $prefix, float $distance): bool
    {
        if ($this->cityPolygon !== null && !$this->isPointInPolygon($lat, $lng, $this->cityPolygon)) {
            $this->skippedOutOfBound++;
            return false;
        }

        foreach ($this->streetCtoCoords[$this->streetName] ?? [] as $prevCoord) {
            if ($this->haversine($lat, $lng, $prevCoord['lat'], $prevCoord['lng']) < $this->ctoIntervalMeters) {
                $this->skippedTooClose++;
                return false;
            }
        }

        $this->streetCtoCoords[$this->streetName][] = ['lat' => $lat, 'lng' => $lng];

        $code = $prefix . self::CTO_BASE_CODE . str_pad($this->totalCtos + 1, 4, '0', STR_PAD_LEFT);

        $cto = Cto::create([
            'name' => "CTO {$this->currentCity} - {$this->streetName} #" . ($this->totalCtos + 1),
            'code' => $code,
            'latitude' => $lat,
            'longitude' => $lng,
            'capacity' => $this->ctoCapacity,
            'used_ports' => 0,
            'street' => $this->streetName,
            'city' => $this->currentCity,
            'state' => $this->currentState,
            'status' => 'active',
            'distance_from_start' => round($distance, 2),
        ]);

        $this->generatedCtos[] = $cto;
        $this->pendingCtoCoords[] = ['lat' => $lat, 'lng' => $lng];
        $this->ctoCount++;
        $this->totalCtos++;

        if ($this->ctoCount >= self::CTOS_PER_CAIXA) {
            $this->flushPendingCaixa();
        }

        return true;
    }

    private function flushPendingCaixa(): void
    {
        if (empty($this->pendingCtoCoords)) {
            return;
        }

        $centroid = $this->calculateCentroid($this->pendingCtoCoords);
        $code = ($this->currentPrefix ?: '') . self::CAIXA_BASE_CODE . str_pad($this->totalCaixas + 1, 3, '0', STR_PAD_LEFT);

        $caixa = CaixaEmenda::create([
            'name' => "CE {$this->currentCity} - {$this->streetName} #{$code}",
            'code' => $code,
            'latitude' => $centroid['lat'],
            'longitude' => $centroid['lng'],
            'capacity' => 48,
            'used_ports' => 0,
            'street' => $this->streetName,
            'city' => $this->currentCity,
            'state' => $this->currentState,
            'status' => 'active',
        ]);

        $pendingCtos = array_slice($this->generatedCtos, -$this->ctoCount);
        foreach ($pendingCtos as $cto) {
            $cto->update(['caixa_emenda_id' => $caixa->id]);
        }

        $this->generatedCaixas[] = $caixa;
        $this->totalCaixas++;
        $this->ctoCount = 0;
        $this->pendingCtoCoords = [];
    }

    private function calculateCentroid(array $coords): array
    {
        $latSum = 0.0;
        $lngSum = 0.0;

        foreach ($coords as $coord) {
            $latSum += $coord['lat'];
            $lngSum += $coord['lng'];
        }

        $count = count($coords);

        return [
            'lat' => round($latSum / $count, 7),
            'lng' => round($lngSum / $count, 7),
        ];
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

    private function calculateTotalDistance(array $streets): float
    {
        $total = 0.0;

        foreach ($streets as $street) {
            $nodes = $street['nodes'] ?? [];
            for ($i = 1; $i < count($nodes); $i++) {
                $prev = $nodes[$i - 1];
                $curr = $nodes[$i];
                $total += $this->haversine(
                    $prev['lat'] ?? $prev[0],
                    $prev['lng'] ?? $prev[1],
                    $curr['lat'] ?? $curr[0],
                    $curr['lng'] ?? $curr[1]
                );
            }
        }

        return round($total, 2);
    }

    private function reset(): void
    {
        $this->ctoCount = 0;
        $this->totalCtos = 0;
        $this->totalCaixas = 0;
        $this->pendingCtoCoords = [];
        $this->generatedCtos = [];
        $this->generatedCaixas = [];
        $this->cityPolygon = null;
        $this->skippedOutOfBound = 0;
        $this->skippedTooClose = 0;
        $this->streetCtoCoords = [];
    }
}
