<?php

namespace Modules\Ftth\Services;

use Modules\Ftth\Models\Cto;
use Modules\Ftth\Models\CaixaEmenda;

class KmlNetworkGenerator
{
    private const EARTH_RADIUS_KM = 6371.0;
    private const CTO_INTERVAL_METERS = 250;
    private const CTOS_PER_CAIXA = 4;
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
    private array $pendingCtoCoords = [];
    private array $generatedCtos = [];
    private array $generatedCaixas = [];
    private string $streetName = '';
    private string $currentPrefix = '';
    private string $currentCity = '';
    private string $currentState = '';

    private function overpassQuery(string $query): array
    {
        $lastError = '';

        foreach (self::OVERPASS_URLS as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => 'data=' . urlencode($query),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 120,
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

        // Primeiro tenta por area no OSM
        $queries = [
            '[out:json][timeout:120];area["name"="' . $searchName . '"]["admin_level"~"^(7|8)$"]->.searchArea;(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](area.searchArea););out body;>;out skel qt;',
            '[out:json][timeout:120];area["name"="' . $searchName . '"]->.searchArea;(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](area.searchArea););out body;>;out skel qt;',
        ];

        foreach ($queries as $query) {
            $data = $this->overpassQuery($query);
            $streets = $this->parseOverpassResponse($data);

            if (!empty($streets)) {
                return $streets;
            }
        }

        // Fallback: usa Nominatim para pegar bounding box
        $bbox = $this->geocodeWithNominatim($searchName);

        if ($bbox) {
            return $this->fetchStreetsByBounds($bbox['south'], $bbox['west'], $bbox['north'], $bbox['east']);
        }

        return [];
    }

    private function geocodeWithNominatim(string $query): ?array
    {
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($query) . '&format=json&limit=1';
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
        ];
    }

    public function fetchStreetsByBounds(float $south, float $west, float $north, float $east): array
    {
        $query = '[out:json][timeout:120];';
        $query .= '(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](' . $south . ',' . $west . ',' . $north . ',' . $east . '););';
        $query .= 'out body;>;out skel qt;';

        $data = $this->overpassQuery($query);
        return $this->parseOverpassResponse($data);
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

    public function generateFromStreets(array $streets, string $prefix = '', string $city = '', string $state = ''): array
    {
        $this->reset();
        $this->currentPrefix = $prefix;
        $this->currentCity = $city;
        $this->currentState = $state;

        foreach ($streets as $streetIndex => $street) {
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
                'total_streets' => count($streets),
                'total_distance_km' => $this->calculateTotalDistance($streets),
            ],
        ];
    }

    public function generateFromCoordinates(array $coordinates, string $streetName = 'Rua Principal', string $prefix = ''): array
    {
        $this->reset();
        $this->streetName = $streetName;

        $streets = [
            [
                'name' => $streetName,
                'nodes' => $coordinates,
            ],
        ];

        return $this->generateFromStreets($streets, $prefix);
    }

    private function processStreet(array $nodes, string $prefix): void
    {
        $accumulatedDistance = 0.0;
        $lastCtoDistance = 0.0;
        $lastPoint = null;

        foreach ($nodes as $nodeIndex => $node) {
            $lat = $node['lat'] ?? $node[0] ?? null;
            $lng = $node['lng'] ?? $node[1] ?? null;

            if ($lat === null || $lng === null) {
                continue;
            }

            $currentPoint = ['lat' => (float) $lat, 'lng' => (float) $lng];

            if ($lastPoint !== null) {
                $segmentDistance = $this->haversine(
                    $lastPoint['lat'], $lastPoint['lng'],
                    $currentPoint['lat'], $currentPoint['lng']
                );
                $accumulatedDistance += $segmentDistance;

                while (($accumulatedDistance - $lastCtoDistance) >= self::CTO_INTERVAL_METERS) {
                    $remainingInSegment = $accumulatedDistance - $lastCtoDistance;
                    $overshoot = $remainingInSegment - self::CTO_INTERVAL_METERS;

                    $fraction = $segmentDistance > 0
                        ? ($segmentDistance - $overshoot) / $segmentDistance
                        : 0;

                    $ctoLat = $lastPoint['lat'] + ($currentPoint['lat'] - $lastPoint['lat']) * $fraction;
                    $ctoLng = $lastPoint['lng'] + ($currentPoint['lng'] - $lastPoint['lng']) * $fraction;

                    $this->createCto($ctoLat, $ctoLng, $prefix, $accumulatedDistance);
                    $lastCtoDistance += self::CTO_INTERVAL_METERS;
                }
            }

            $lastPoint = $currentPoint;
        }
    }

    private function createCto(float $lat, float $lng, string $prefix, float $distance): void
    {
        $code = $prefix . self::CTO_BASE_CODE . str_pad($this->totalCtos + 1, 4, '0', STR_PAD_LEFT);

        $cto = Cto::create([
            'name' => "CTO {$this->currentCity} - {$this->streetName} #" . ($this->totalCtos + 1),
            'code' => $code,
            'latitude' => $lat,
            'longitude' => $lng,
            'capacity' => 8,
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
    }
}
