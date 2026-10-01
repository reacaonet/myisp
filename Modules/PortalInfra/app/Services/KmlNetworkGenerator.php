<?php

namespace Modules\PortalInfra\Services;

use Modules\PortalInfra\Models\CaixaEmenda;
use Modules\PortalInfra\Models\Cto;
use Modules\PortalInfra\Models\FtthSplitter;

class KmlNetworkGenerator
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * CTOs que uma CEO atende. A CEO sempre nasce atendendo 8, mas o tecnico
     * informa quantas no momento de gerar a rede, porque depende de quantos
     * projetos de bairro existem naquele ponto.
     */
    private const CTOS_PER_CAIXA_PADRAO = 8;

    /**
     * Saidas por splitter dentro da CEO. A CEO concentra splitters 1x8, cada um
     * alimentando 8 CTOs. Com 32 CTOs sao 4 splitters, que e o maximo de
     * projetos que uma CEO costuma atender.
     */
    private const SAIDAS_POR_SPLITTER_CEO = 8;

    private const URBAN_CELL_SIZE_METERS = 400;

    private const URBAN_MIN_CELL_DENSITY_RATIO = 0.2;

    private const URBAN_MAX_CELL_DISTANCE = 1;

    private const STREET_SEGMENT_JOIN_METERS = 30;

    /**
     * Lado da celula que agrupa as casas em um ponto de demanda. Uma celula
     * vira no maximo um bloco de CTOs, entao o tamanho define o quao separadas
     * as CTOs ficam entre si.
     */
    private const DEMAND_CELL_METERS = 150;

    /**
     * Lado da celula usada para separar a cidade em bairros na hora de ordenar
     * a geracao. Como as CTOs saem bairro a bairro, um bairro so ganha a
     * segunda CTO depois de todo bairro vizinho ter ganhado a sua, e nenhuma
     * quadra concentrada consegue tomar o lugar dos demais.
     */
    private const NEIGHBORHOOD_CELL_METERS = 500;

    /**
     * Uma CTO e um poste na rua, nunca dentro de um lote. Se o agrupamento de
     * casas nao tiver rua por perto, ele e descartado em vez de inventar CTO
     * no meio do terreno.
     */
    private const MAX_SNAP_TO_STREET_METERS = 250;

    /**
     * Alcance de uma CTO: 150m para cada lado da rua, 300m no total.
     *
     * E este numero, e nao o intervalo do formulario, que separa duas CTOs.
     * Uma CTO dentro do alcance de outra nao acrescenta cobertura nenhuma, e o
     * tecnico nao quer dois postes lado a lado: em rua com 350m de intervalo, a
     * CTO seguinte nasce a 350m porque 350 > 150.
     */
    private const CTO_COVERAGE_RADIUS_METERS = 150.0;

    /**
     * Folga sobre o intervalo informado no formulario. O intervalo e medido ao
     * longo da rua, entao a linha reta entre duas CTOs consecutivas sempre e
     * menor ou igual a ele. Usar o intervalo cheio na deduplicacao mataria CTOs
     * legitimas em rua curva.
     */
    private const CTO_SPACING_TOLERANCE = 0.85;

    /**
     * Passadas do backfill. Cada passada cria uma CTO por rua ainda
     * descoberta, entao uma rua muito longa pode precisar de varias.
     */
    private const BACKFILL_MAX_PASSOS = 12;

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

    private int $totalSplitters = 0;

    private int $ctoCapacity = 16;

    private int $ctosPerCaixa = self::CTOS_PER_CAIXA_PADRAO;

    private int $ctoIntervalMeters = 250;

    private array $pendingCtoCoords = [];

    private array $generatedCtos = [];

    private array $generatedCaixas = [];

    private array $generatedSplitters = [];

    private string $streetName = '';

    private string $currentPrefix = '';

    private string $currentCity = '';

    private string $currentState = '';

    private ?array $cityPolygon = null;

    private int $skippedOutOfBound = 0;

    private int $skippedTooClose = 0;

    /** Agrupamentos de casas que nao tinham rua proxima para receber a CTO. */
    private int $skippedNoStreet = 0;

    private array $streetCtoCoords = [];

    private array $chainCtoCoords = [];

    /** Indice espacial dos nos de rua, usado para encaixar a CTO na rua. */
    private array $streetNodeIndex = [];

    private float $streetIndexCellMeters = 0.0;

    /** Indice espacial das CTOs ja criadas, para respeitar o intervalo global. */
    private array $placedCtoIndex = [];

    private float $placedCtoCellMeters = 0.0;

    private function overpassQuery(string $query): array
    {
        $lastError = '';

        foreach (self::OVERPASS_URLS as $url) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => 'data='.urlencode($query),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 150,
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
                $lastError = "Invalid JSON from {$url}: ".json_last_error_msg();

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

        if ($geo && ! empty($geo['polygon'])) {
            $this->cityPolygon = $geo['polygon'];

            $streets = $this->fetchStreetsByPolygon($geo['polygon']);
            $streets = $this->filterStreetsByPolygon($streets, $geo['polygon']);
            $streets = $this->filterUrbanStreets($streets);

            if (! empty($streets)) {
                return $streets;
            }

            $streets = $this->fetchStreetsByBounds($geo['south'], $geo['west'], $geo['north'], $geo['east']);
            $streets = $this->filterStreetsByPolygon($streets, $geo['polygon']);

            return $this->filterUrbanStreets($streets);
        }

        if ($geo) {
            if (! empty($geo['polygon'])) {
                $this->cityPolygon = $geo['polygon'];
                $streets = $this->fetchStreetsByBounds($geo['south'], $geo['west'], $geo['north'], $geo['east']);
                $streets = $this->filterStreetsByPolygon($streets, $geo['polygon']);

                return $this->filterUrbanStreets($streets);
            }

            $streets = $this->fetchStreetsByBounds($geo['south'], $geo['west'], $geo['north'], $geo['east']);
            if (! empty($streets)) {
                return $this->filterUrbanStreets($streets);
            }
        }

        // Fallback: busca por area no OSM
        $queries = [
            '[out:json][timeout:60];area["name"="'.$searchName.'"]["admin_level"~"^(7|8)$"]->.searchArea;(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](area.searchArea););out body;>;out skel qt;',
            '[out:json][timeout:60];area["name"="'.$searchName.'"]->.searchArea;(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"](area.searchArea););out body;>;out skel qt;',
        ];

        foreach ($queries as $query) {
            try {
                $data = $this->overpassQuery($query);
                $streets = $this->parseOverpassResponse($data);

                if (! empty($streets)) {
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
        $url = 'https://nominatim.openstreetmap.org/search?q='.urlencode($query).'&format=json&limit=1&polygon_geojson=1&polygon_threshold=0.01';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['User-Agent: MyISP-FTTH/1.0'],
        ]);

        $body = curl_exec($ch);
        curl_close($ch);

        if (! $body) {
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

        if (! $geojson || ! isset($geojson['type'])) {
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

        if (! $ring || count($ring) < 4) {
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

        $polyFilter = 'poly:"'.implode(' ', $points).'"';

        $query = '[out:json][timeout:120];';
        $query .= '(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"]('.$polyFilter.'););';
        $query .= 'out body;>;out skel qt;';

        $data = $this->overpassQuery($query);

        return $this->parseOverpassResponse($data);
    }

    public function fetchStreetsByBounds(float $south, float $west, float $north, float $east): array
    {
        $query = '[out:json][timeout:60];';
        $query .= '(way["highway"~"^(residential|primary|secondary|tertiary|unclassified|living_street)$"]["name"]('.$south.','.$west.','.$north.','.$east.'););';
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

        while (! empty($queue)) {
            $key = array_pop($queue);
            [$cx, $cy] = explode(':', $key);

            for ($dx = -self::URBAN_MAX_CELL_DISTANCE; $dx <= self::URBAN_MAX_CELL_DISTANCE; $dx++) {
                for ($dy = -self::URBAN_MAX_CELL_DISTANCE; $dy <= self::URBAN_MAX_CELL_DISTANCE; $dy++) {
                    if ($dx === 0 && $dy === 0) {
                        continue;
                    }

                    $nKey = ($cx + $dx).':'.($cy + $dy);
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
                $key = $cx.':'.$cy;

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
                $inside = ! $inside;
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

    public function generateFromStreets(array $streets, string $prefix = '', string $city = '', string $state = '', int $ctoCapacity = 16, int $ctoIntervalMeters = 250, ?array $polygon = null, int $ctosPerCaixa = self::CTOS_PER_CAIXA_PADRAO): array
    {
        $this->reset();
        $this->currentPrefix = $prefix;
        $this->currentCity = $city;
        $this->currentState = $state;
        $this->ctoCapacity = $ctoCapacity > 0 ? $ctoCapacity : 16;
        $this->ctoIntervalMeters = $ctoIntervalMeters >= 50 && $ctoIntervalMeters <= 1000 ? $ctoIntervalMeters : 250;
        $this->placedCtoCellMeters = $this->minimumCtoSeparation();
        $this->ctosPerCaixa = $ctosPerCaixa > 0 ? $ctosPerCaixa : self::CTOS_PER_CAIXA_PADRAO;
        $this->cityPolygon = $polygon;

        // OSM entrega cada rua quebrada em varios ways (um por quadra/cruzamento).
        // Precisamos costurar os segmentos de mesmo nome que se tocam pelas
        // pontas para gerar CTOs ao longo da rua inteira respeitando o intervalo.
        $mergedStreets = $this->mergeStreetsByContinuity($streets);
        $mergedStreets = $this->orderStreetsGeographically($mergedStreets);

        foreach ($mergedStreets as $streetIndex => $street) {
            $this->streetName = $street['name'] ?? "Rua {$streetIndex}";
            $nodes = $street['nodes'] ?? [];

            if (count($nodes) < 2) {
                continue;
            }

            $this->processStreet($nodes, $prefix, $streetIndex);
        }

        $this->flushPendingCaixa();

        // Preenche ruas que ficaram sem nenhuma CTO. Cidades sem cobertura de
        // predios no OSM entram por aqui, e sem esta etapa bairros inteiros
        // saiam de fora so porque as ruas vizinhas ja ocupavam o ponto.
        $this->backfillStreetsWithoutCtos($mergedStreets, $prefix);
        $this->flushPendingCaixa();

        return [
            'ctos' => $this->generatedCtos,
            'caixas' => $this->generatedCaixas,
            'splitters' => $this->generatedSplitters,
            'stats' => [
                'total_ctos' => $this->totalCtos,
                'total_caixas' => $this->totalCaixas,
                'total_splitters' => $this->totalSplitters,
                'ctos_per_caixa' => $this->ctosPerCaixa,
                'cto_capacity' => $this->ctoCapacity,
                'total_streets' => count($mergedStreets),
                'total_distance_km' => $this->calculateTotalDistance($mergedStreets),
                'skipped_out_of_bound' => $this->skippedOutOfBound,
                'skipped_too_close' => $this->skippedTooClose,
            ],
        ];
    }

    /**
     * Passa por todos os bairros em ordem geografica e, dentro de cada um,
     * descarta as CTOs que sobraram perto demais de outra. A fila por bairro
     * e o que corrige a fileira de CTOs coladas: duas casas muito proximas
     * caem no mesmo agrupamento e so uma CTO sobrevive.
     */
    private function sortAnchorsGeographically(array $anchors, float $minSeparation): array
    {
        usort($anchors, function (array $a, array $b) {
            return [$a['neighborhood'], $a['lat'], $a['lng']]
                <=> [$b['neighborhood'], $b['lat'], $b['lng']];
        });

        $kept = [];
        $keptInNeighborhood = [];

        foreach ($anchors as $anchor) {
            $tooClose = false;

            foreach ($keptInNeighborhood[$anchor['neighborhood']] ?? [] as $existing) {
                $distance = $this->haversine(
                    $anchor['lat'],
                    $anchor['lng'],
                    $existing['lat'],
                    $existing['lng']
                );

                if ($distance < $minSeparation) {
                    $tooClose = true;
                    break;
                }
            }

            if ($tooClose) {
                $this->skippedTooClose++;

                continue;
            }

            $keptInNeighborhood[$anchor['neighborhood']][] = $anchor;
            $kept[] = $anchor;
        }

        return $kept;
    }

    /**
     * Ordem as ruas por posicao geografica antes de gerar.
     *
     * A CEO e criada a partir da media das suas CTOs. Se as ruas forem processadas
     * na ordem em que o OSM devolveu (agrupadas por nome, ou seja, aleatorio em
     * relacao a geografia), as CAs nascem em qualquer lugar e varias delas caem
     * praticamente na mesma rua. Ordenando por bairro, cada CEO fica perto das
     * suas CTOs e as CAs se espalham pela cidade.
     */
    private function orderStreetsGeographically(array $mergedStreets): array
    {
        usort($mergedStreets, function (array $a, array $b) {
            $latA = $a['latitude'] ?? $this->firstLatitude($a);
            $latB = $b['latitude'] ?? $this->firstLatitude($b);
            $lngA = $a['longitude'] ?? $this->firstLongitude($a);
            $lngB = $b['longitude'] ?? $this->firstLongitude($b);

            return [$latA, $lngA] <=> [$latB, $lngB];
        });

        foreach ($mergedStreets as $index => $street) {
            $mergedStreets[$index]['latitude'] = $this->firstLatitude($street);
            $mergedStreets[$index]['longitude'] = $this->firstLongitude($street);
        }

        return $mergedStreets;
    }

    private function firstLatitude(array $street): float
    {
        $node = $street['nodes'][0] ?? null;

        if (! is_array($node)) {
            return 0.0;
        }

        return (float) ($node['lat'] ?? $node[0] ?? 0.0);
    }

    private function firstLongitude(array $street): float
    {
        $node = $street['nodes'][0] ?? null;

        if (! is_array($node)) {
            return 0.0;
        }

        return (float) ($node['lng'] ?? $node[1] ?? 0.0);
    }

    /**
     * Cobre o trecho que sobrou sem atendimento.
     *
     * O teste e por COBERTURA, e nao por "a rua tem ou nao tem CTO". Uma avenida
     * de 2km pode ter tres CTOs no comeco e ainda assim deixar a ponta a mais de
     * 150m do ultimo poste. Pular essas ruas deixava 2 a 3 pontos de cada cidade
     * do Maranhao sem ninguem atendendo, sempre na extremidade de uma rua longa.
     *
     * Quando sobra trecho descoberto, a CTO nasce no ponto mais distante das CTOs
     * que ja existem, o que costuma ser a ponta livre da rua. Se nao houver
     * posicao valida ali, createCto recusa e a rua fica para a proxima rodada.
     */
    private function backfillStreetsWithoutCtos(array $mergedStreets, string $prefix): int
    {
        $backfilled = 0;

        foreach ($mergedStreets as $streetIndex => $street) {
            $nodes = $street['nodes'] ?? [];

            if (count($nodes) < 2) {
                continue;
            }

            $this->streetName = $street['name'] ?? "Rua {$streetIndex}";

            // Comprimento total e ponto no meio do percurso.
            $length = 0.0;
            for ($i = 1; $i < count($nodes); $i++) {
                $a = $nodes[$i - 1];
                $b = $nodes[$i];
                $length += $this->haversine(
                    (float) ($a['lat'] ?? $a[0]),
                    (float) ($a['lng'] ?? $a[1]),
                    (float) ($b['lat'] ?? $b[0]),
                    (float) ($b['lng'] ?? $b[1])
                );
            }

            $half = $length / 2;
            $walked = 0.0;
            $midpoint = null;

            for ($i = 1; $i < count($nodes) && $midpoint === null; $i++) {
                $a = $nodes[$i - 1];
                $b = $nodes[$i];
                $segment = $this->haversine(
                    (float) ($a['lat'] ?? $a[0]),
                    (float) ($a['lng'] ?? $a[1]),
                    (float) ($b['lat'] ?? $b[0]),
                    (float) ($b['lng'] ?? $b[1])
                );

                if ($walked + $segment >= $half && $segment > 0) {
                    $fraction = ($half - $walked) / $segment;
                    $midpoint = [
                        'lat' => ((float) ($a['lat'] ?? $a[0])) + (((float) ($b['lat'] ?? $b[0])) - ((float) ($a['lat'] ?? $a[0]))) * $fraction,
                        'lng' => ((float) ($a['lng'] ?? $a[1])) + (((float) ($b['lng'] ?? $b[1])) - ((float) ($a['lng'] ?? $a[1]))) * $fraction,
                    ];
                }

                $walked += $segment;
            }

            // Rua degenerada (todos os pontos iguais): usa o primeiro no.
            if ($midpoint === null) {
                $first = $nodes[0];
                $midpoint = [
                    'lat' => (float) ($first['lat'] ?? $first[0]),
                    'lng' => (float) ($first['lng'] ?? $first[1]),
                ];
            }

            if ($this->streetHasUncoveredPoint($nodes)) {
                $best = $this->farthestPointOnStreet($nodes);

                if ($best !== null) {
                    if ($this->createCto($best['lat'], $best['lng'], $prefix, round($length / 2, 2), $streetIndex)) {
                        $backfilled++;
                    }

                    continue;
                }
            }

            if (! empty($this->streetCtoCoords[$this->streetName])) {
                continue;
            }

            if ($this->createCto($midpoint['lat'], $midpoint['lng'], $prefix, round($length / 2, 2), $streetIndex)) {
                $backfilled++;
            }
        }

        // Uma passada so nao basta: a CTO nova pode cobrir o buraco mais
        // proximo e ainda deixar a outra ponta da rua descoberta. Repetimos
        // ate a cidade fechar, e o teto evita laco infinito quando nao existe
        // posicao valida para o que sobrou.
        for ($passo = 0; $passo < self::BACKFILL_MAX_PASSOS; $passo++) {
            $pendentes = [];

            foreach ($mergedStreets as $streetIndex => $street) {
                $nodes = $street['nodes'] ?? [];

                if (count($nodes) < 2 || ! $this->streetHasUncoveredPoint($nodes)) {
                    continue;
                }

                $best = $this->farthestPointOnStreet($nodes);

                if ($best !== null && ! $this->isCtoPositionTaken($best['lat'], $best['lng'])) {
                    $pendentes[] = [$streetIndex, $street['name'] ?? "Rua {$streetIndex}", $best];
                }
            }

            if (empty($pendentes)) {
                break;
            }

            $novas = 0;

            foreach ($pendentes as [$streetIndex, $streetName, $ponto]) {
                $this->streetName = $streetName;
                $length = $this->streetLength($nodes ?? []);

                if ($this->createCto($ponto['lat'], $ponto['lng'], $prefix, round($length / 2, 2), $streetIndex)) {
                    $novas++;
                    $backfilled++;
                }
            }

            if ($novas === 0) {
                break;
            }
        }

        return $backfilled;
    }

    private function streetLength(array $nodes): float
    {
        $length = 0.0;

        for ($i = 1; $i < count($nodes); $i++) {
            $a = $nodes[$i - 1];
            $b = $nodes[$i];
            $length += $this->haversine(
                (float) ($a['lat'] ?? $a[0]),
                (float) ($a['lng'] ?? $a[1]),
                (float) ($b['lat'] ?? $b[0]),
                (float) ($b['lng'] ?? $b[1])
            );
        }

        return $length;
    }

    /**
     * A rua tem algum ponto a mais de 150m de uma CTO? Uma CTO atende 150m
     * para cada lado, entao medimos ponto a ponto e nao pelo no mais proximo.
     */
    private function streetHasUncoveredPoint(array $nodes): bool
    {
        foreach ($nodes as $node) {
            $lat = (float) ($node['lat'] ?? $node[0]);
            $lng = (float) ($node['lng'] ?? $node[1]);

            if ($this->distanceToNearestCto($lat, $lng) > self::CTO_COVERAGE_RADIUS_METERS) {
                return true;
            }
        }

        return false;
    }

    /**
     * No da rua mais distante de todas as CTOs existentes. E onde a CTO nova
     * mais rende: na ponta livre de uma rua longa.
     */
    private function farthestPointOnStreet(array $nodes): ?array
    {
        $best = null;
        $bestDistance = -1.0;

        foreach ($nodes as $node) {
            $lat = (float) ($node['lat'] ?? $node[0]);
            $lng = (float) ($node['lng'] ?? $node[1]);
            $distance = $this->distanceToNearestCto($lat, $lng);

            if ($distance > $bestDistance) {
                $bestDistance = $distance;
                $best = ['lat' => $lat, 'lng' => $lng];
            }
        }

        return $best;
    }

    /**
     * Distancia de um ponto ate a CTO ja colocada mais proxima. Infinity
     * quando ainda nao existe nenhuma CTO.
     */
    private function distanceToNearestCto(float $lat, float $lng): float
    {
        $nearest = INF;
        $cell = $this->placedCtoCellMeters;

        $row = (int) floor($lat / $this->latitudeStep($cell));
        $column = (int) floor($lng / $this->longitudeStep($lat, $cell));

        for ($r = $row - 1; $r <= $row + 1; $r++) {
            for ($c = $column - 1; $c <= $column + 1; $c++) {
                foreach ($this->placedCtoIndex[$r.':'.$c] ?? [] as $placed) {
                    $distance = $this->haversine($lat, $lng, $placed['lat'], $placed['lng']);

                    if ($distance < $nearest) {
                        $nearest = $distance;
                    }
                }
            }
        }

        return $nearest;
    }

    /**
     * Indice espacial dos nos de rua. Sem ele, snapshot de cada agrupamento de
     * casas contra todas as ruas da cidade seria quadrático e a geracao levaria
     * minutos.
     */
    private function buildStreetNodeIndex(array $mergedStreets): void
    {
        $this->streetNodeIndex = [];
        $this->streetIndexCellMeters = max(50.0, (float) self::DEMAND_CELL_METERS);

        foreach ($mergedStreets as $streetIndex => $street) {
            $streetName = $street['name'] ?? "Rua {$streetIndex}";
            $cumulative = 0.0;
            $previous = null;

            foreach ($street['nodes'] ?? [] as $node) {
                $lat = $node['lat'] ?? $node[0] ?? null;
                $lng = $node['lng'] ?? $node[1] ?? null;

                if ($lat === null || $lng === null) {
                    continue;
                }

                $lat = (float) $lat;
                $lng = (float) $lng;

                if ($previous !== null) {
                    $cumulative += $this->haversine($previous['lat'], $previous['lng'], $lat, $lng);
                }

                $key = $this->gridKey($lat, $lng, $this->streetIndexCellMeters);

                $this->streetNodeIndex[$key][] = [
                    'lat' => $lat,
                    'lng' => $lng,
                    'distance' => $cumulative,
                    'street' => $streetName,
                    'street_index' => $streetIndex,
                ];

                $previous = ['lat' => $lat, 'lng' => $lng];
            }
        }
    }

    /**
     * Procura nos de rua em aneis crescentes ao redor do agrupamento de casas
     * e devolve ate $wanted nos distintos, respeitando a distancia minima.
     */
    private function nearestStreetNodes(float $lat, float $lng, int $wanted, float $minSeparation, float $maxMeters): array
    {
        if ($this->streetNodeIndex === []) {
            return [];
        }

        $cell = $this->streetIndexCellMeters;
        $row = (int) floor($lat / $this->latitudeStep($cell));
        $column = (int) floor($lng / $this->longitudeStep($lat, $cell));
        $maxRing = (int) ceil($maxMeters / $cell) + 1;
        $found = [];

        for ($ring = 0; $ring <= $maxRing; $ring++) {
            for ($r = $row - $ring; $r <= $row + $ring; $r++) {
                for ($c = $column - $ring; $c <= $column + $ring; $c++) {
                    if ($ring > 0 && abs($r - $row) !== $ring && abs($c - $column) !== $ring) {
                        continue;
                    }

                    foreach ($this->streetNodeIndex[$r.':'.$c] ?? [] as $node) {
                        $found[] = [
                            'node' => $node,
                            'distance' => $this->haversine($lat, $lng, $node['lat'], $node['lng']),
                        ];
                    }
                }
            }

            // Nao ha nada mais perto do que ja encontramos neste anel.
            if ($found !== [] && min(array_column($found, 'distance')) <= $ring * $cell) {
                break;
            }
        }

        $found = array_values(array_filter(
            $found,
            fn (array $candidate) => $candidate['distance'] <= $maxMeters
        ));

        usort($found, fn (array $a, array $b) => $a['distance'] <=> $b['distance']);

        $picked = [];

        foreach ($found as $candidate) {
            $node = $candidate['node'];

            if ($this->cityPolygon !== null && ! $this->isPointInPolygon($node['lat'], $node['lng'], $this->cityPolygon)) {
                continue;
            }

            $isDistinct = true;

            foreach ($picked as $chosen) {
                if ($this->haversine($node['lat'], $node['lng'], $chosen['lat'], $chosen['lng']) < $minSeparation) {
                    $isDistinct = false;
                    break;
                }
            }

            if (! $isDistinct) {
                continue;
            }

            $picked[] = $node;

            if (count($picked) >= $wanted) {
                break;
            }
        }

        return $picked;
    }

    private function latitudeStep(float $cellMeters): float
    {
        return $cellMeters / 111320.0;
    }

    private function longitudeStep(float $lat, float $cellMeters): float
    {
        return $cellMeters / max(1.0, 111320.0 * cos(deg2rad($lat)));
    }

    private function gridKey(float $lat, float $lng, float $cellMeters): string
    {
        return ((int) floor($lat / $this->latitudeStep($cellMeters)))
            .':'.((int) floor($lng / $this->longitudeStep($lat, $cellMeters)));
    }

    /**
     * Espacamento minimo entre duas CTOs de qq rua: o alcance da CTO, 150m.
     *
     * O intervalo do formulario e outra medida: e o passo percorrido AO LONGO DA
     * RUA, aplicado em processStreet(). Sao coisas diferentes, e confundi-las
     * quebra a geracao de dois jeitos opostos.
     *
     * Com os 30m de deduplicacao, um centro urbano em que as quadras ficam a
     * menos de 30m uma da outra enche o mapa de postes lado a lado, que e
     * exatamente o que o tecnico pediu para nao acontecer.
     *
     * Ja exigir o intervalo entre ruas diferentes tambem nao serve: com 350m,
     * toda rua do centro de Santo Antonio dos Lopes fica a menos de 350m de uma
     * CTO vizinha, nenhuma CTO nova cabe, e 31 dos 35 trechos saem sem
     * atendimento estando a 220m do poste mais proximo.
     *
     * Com 150m as duas coisas fecham: nenhuma CTO nasce dentro do alcance da
     * outra, e uma rua a 220m ainda ganha a CTO que faltava.
     */
    private function minimumCtoSeparation(): float
    {
        return self::CTO_COVERAGE_RADIUS_METERS;
    }

    /**
     * Varre as nove celulas vizinhas do indice de CTOs criadas. Sem o indice
     * isso seria O(n) por CTO e a geracao de cidade grande travaria.
     */
    private function isCtoPositionTaken(float $lat, float $lng): bool
    {
        $minimum = $this->minimumCtoSeparation();
        $cell = $this->placedCtoCellMeters;

        $row = (int) floor($lat / $this->latitudeStep($cell));
        $column = (int) floor($lng / $this->longitudeStep($lat, $cell));

        for ($r = $row - 1; $r <= $row + 1; $r++) {
            for ($c = $column - 1; $c <= $column + 1; $c++) {
                foreach ($this->placedCtoIndex[$r.':'.$c] ?? [] as $placed) {
                    if ($this->haversine($lat, $lng, $placed['lat'], $placed['lng']) < $minimum) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function registerCtoPosition(float $lat, float $lng): void
    {
        $key = $this->gridKey($lat, $lng, $this->placedCtoCellMeters);
        $this->placedCtoIndex[$key][] = ['lat' => $lat, 'lng' => $lng];
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
                fn ($n) => round((float) ($n['lat'] ?? $n[0]), 6).','.round((float) ($n['lng'] ?? $n[1]), 6),
                $seg
            ));
            if (! isset($seen[$key])) {
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

    public function generateFromCoordinates(array $coordinates, string $streetName = 'Rua Principal', string $prefix = '', int $ctoCapacity = 16, int $ctoIntervalMeters = 250, int $ctosPerCaixa = self::CTOS_PER_CAIXA_PADRAO): array
    {
        $this->reset();
        $this->streetName = $streetName;

        $streets = [
            [
                'name' => $streetName,
                'nodes' => $coordinates,
            ],
        ];

        return $this->generateFromStreets($streets, $prefix, '', '', $ctoCapacity, $ctoIntervalMeters, null, $ctosPerCaixa);
    }

    private function processStreet(array $nodes, string $prefix, int $chainIndex = 0): void
    {
        $accumulatedDistance = 0.0;
        $lastCtoDistance = 0.0;
        $lastPoint = null;
        $createdInStreet = false;
        $streetLength = 0.0;
        $midPoint = null;

        // O OSM as vezes entrega a mesma rua desenhada em quadriculas paralelas
        // que NAO se encostam, e a costura por pontas nao consegue uni-las. Cada
        // quadricula e processada como se fosse uma rota comeca do zero, e o
        // poste do fim de uma cai perto do poste do inicio da outra. Guardamos
        // as CTOs desta rua para poder recusar essa repeticao.
        $existingOnStreet = $this->streetCtoCoords[$this->streetName] ?? [];
        $streetFloor = $this->ctoIntervalMeters * self::CTO_SPACING_TOLERANCE;

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
                $streetLength += $segmentDistance;

                if ($midPoint === null && $accumulatedDistance >= $streetLength / 2) {
                    $midPoint = $currentPoint;
                }

                while (($accumulatedDistance - $lastCtoDistance) >= $this->ctoIntervalMeters) {
                    $remainingInSegment = $accumulatedDistance - $lastCtoDistance;
                    $overshoot = $remainingInSegment - $this->ctoIntervalMeters;

                    $fraction = $segmentDistance > 0
                        ? ($segmentDistance - $overshoot) / $segmentDistance
                        : 0;

                    $ctoLat = $lastPoint['lat'] + ($currentPoint['lat'] - $lastPoint['lat']) * $fraction;
                    $ctoLng = $lastPoint['lng'] + ($currentPoint['lng'] - $lastPoint['lng']) * $fraction;

                    if ($this->isTooCloseToStreetCto($ctoLat, $ctoLng, $existingOnStreet, $streetFloor)) {
                        $this->skippedTooClose++;
                        $lastCtoDistance += $this->ctoIntervalMeters;

                        continue;
                    }

                    $createdInStreet = $this->createCto($ctoLat, $ctoLng, $prefix, $accumulatedDistance, $chainIndex) || $createdInStreet;
                    $existingOnStreet[] = ['lat' => $ctoLat, 'lng' => $ctoLng];
                    $lastCtoDistance += $this->ctoIntervalMeters;
                }
            }

            $lastPoint = $currentPoint;
        }

        if ($createdInStreet || $lastPoint === null) {
            return;
        }

        // Rua curta: nao cabe mais de um poste dentro do intervalo, mas ela
        // ainda e uma rua e precisa de uma CTO. Antes essa rua era encerrada
        // num unico ponto e, se esse ponto ja tivesse CTO vizinha, a rua
        // inteira saia de fora do projeto.
        $candidate = $midPoint ?? $lastPoint;

        if ($this->isTooCloseToStreetCto($candidate['lat'], $candidate['lng'], $existingOnStreet, $streetFloor)) {
            $this->skippedTooClose++;

            return;
        }

        $this->createCto($candidate['lat'], $candidate['lng'], $prefix, round($streetLength / 2, 2), $chainIndex);
    }

    /**
     * Uma CTO na mesma rua, a menos do intervalo, e o poste repetido de outro
     * fragmento da mesma rua. So vale recusar entre ruas iguais: entre ruas
     * diferentes a proximidade e geometria da cidade, nao erro de geracao.
     */
    private function isTooCloseToStreetCto(float $lat, float $lng, array $placed, float $floor): bool
    {
        foreach ($placed as $cto) {
            if ($this->haversine($lat, $lng, $cto['lat'], $cto['lng']) < $floor) {
                return true;
            }
        }

        return false;
    }

    private function createCto(float $lat, float $lng, string $prefix, float $distance, int $chainIndex = 0): bool
    {
        if ($this->cityPolygon !== null && ! $this->isPointInPolygon($lat, $lng, $this->cityPolygon)) {
            $this->skippedOutOfBound++;

            return false;
        }

        // A metragem do formulario e a unica fonte de espacamento, e vale para
        // a cidade inteira: duas CTOs so podem nascer separadas quando
        // passou por aqui. Antes a deduplicacao olhava 30 m e so dentro da
        // mesma cadeia ou do mesmo nome de rua, e por isso saiam tres CTOs
        // coladas em menos de cem metros quando duas quadras diferentes se
        // cruzavam, e o intervalo configurado nunca era respeitado.
        if ($this->isCtoPositionTaken($lat, $lng)) {
            $this->skippedTooClose++;

            return false;
        }

        $this->chainCtoCoords[$chainIndex][] = ['lat' => $lat, 'lng' => $lng];
        $this->streetCtoCoords[$this->streetName][] = ['lat' => $lat, 'lng' => $lng];
        $this->registerCtoPosition($lat, $lng);

        $code = $prefix.self::CTO_BASE_CODE.str_pad($this->totalCtos + 1, 4, '0', STR_PAD_LEFT);

        $cto = Cto::create([
            'name' => "CTO {$this->currentCity} - {$this->streetName} #".($this->totalCtos + 1),
            'code' => $code,
            'latitude' => $lat,
            'longitude' => $lng,
            'capacity' => $this->ctoCapacity,
            'used_ports' => 0,
            'street' => $this->streetName,
            'city' => $this->currentCity,
            'state' => $this->currentState,
            // Projeto planejado nao e rede construida: o tecnico ativa depois
            // de lancar a fibra.
            'status' => 'inactive',
            'distance_from_start' => round($distance, 2),
        ]);

        $this->generatedCtos[] = $cto;
        $this->pendingCtoCoords[] = ['lat' => $lat, 'lng' => $lng];
        $this->ctoCount++;
        $this->totalCtos++;

        if ($this->ctoCount >= $this->ctosPerCaixa) {
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
        $pendingCtos = array_slice($this->generatedCtos, -$this->ctoCount);
        $existing = $this->nearestCaixa($centroid['lat'], $centroid['lng']);

        // Duas CEs lado a lado nao acrescentam nada: a segunda repete o
        // alcance da primeira. Quando o grupo fecha em cima de uma CE que ja
        // existe, as CTOs entram nela em vez de abrir outra na mesma rua. Era o
        // que deixava Pio XII com 3 pares de CEs coladas e Buriticupu com 7.
        if ($existing !== null) {
            $this->attachPendingCtosToCaixa($existing, $pendingCtos);

            $this->ctoCount = 0;
            $this->pendingCtoCoords = [];

            return;
        }

        $code = ($this->currentPrefix ?: '').self::CAIXA_BASE_CODE.str_pad($this->totalCaixas + 1, 3, '0', STR_PAD_LEFT);

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
            'status' => 'inactive',
        ]);

        $pendingCtos = array_slice($this->generatedCtos, -$this->ctoCount);
        foreach ($pendingCtos as $cto) {
            $cto->update(['caixa_emenda_id' => $caixa->id]);
        }

        $splitters = $this->createSplittersForCaixa($caixa, $pendingCtos);
        $caixa->update([
            'splitter_config' => $this->describeSplitters($splitters),
        ]);

        $this->generatedCaixas[] = $caixa;
        $this->totalCaixas++;
        $this->ctoCount = 0;
        $this->pendingCtoCoords = [];
    }

    /**
     * CE ja aberta dentro do alcance desta CTO..Null quando o ponto esta livre
     * e uma CE nova pode nascer ali.
     */
    private function nearestCaixa(float $lat, float $lng): ?CaixaEmenda
    {
        $nearest = null;
        $nearestDistance = $this->minimumCtoSeparation();

        foreach ($this->generatedCaixas as $caixa) {
            $distance = $this->haversine($lat, $lng, (float) $caixa->latitude, (float) $caixa->longitude);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearest = $caixa;
            }
        }

        return $nearest;
    }

    /**
     * CTOs cujo grupo fechou em cima de uma CE existente entram nela, ganhando
     * splitters, em vez de abrir uma segunda CEO no mesmo ponto.
     */
    private function attachPendingCtosToCaixa(CaixaEmenda $caixa, array $pendingCtos): void
    {
        foreach ($pendingCtos as $cto) {
            $cto->update(['caixa_emenda_id' => $caixa->id]);
        }

        $splitters = $this->createSplittersForCaixa($caixa, $pendingCtos);

        $anteriores = FtthSplitter::where('parent_type', 'caixa')
            ->where('parent_id', $caixa->id)
            ->get()
            ->all();

        $caixa->update([
            'splitter_config' => $this->describeSplitters(array_merge($anteriores, $splitters)),
        ]);
    }

    /**
     * A CEO concentra os splitters que alimentam as suas CTOs: um 1x8 para cada
     * grupo de 8 CTOs. Com 8 CTOs sai um splitter, com 32 saem 4, que e como
     * uma CEO atende varios projetos de bairro ao mesmo tempo.
     */
    private function createSplittersForCaixa(CaixaEmenda $caixa, array $ctos): array
    {
        if (empty($ctos)) {
            return [];
        }

        $grupos = array_chunk($ctos, self::SAIDAS_POR_SPLITTER_CEO);
        $saidas = count($grupos) * self::SAIDAS_POR_SPLITTER_CEO;
        $criados = [];

        foreach ($grupos as $indice => $grupo) {
            $this->totalSplitters++;

            $base = ($this->currentPrefix ?: '').'SPT'.str_pad($this->totalSplitters, 4, '0', STR_PAD_LEFT);

            $splitter = FtthSplitter::create([
                'name' => sprintf('Splitter 1x%d %s', $saidas, $caixa->code),
                'code' => $this->uniqueSplitterCode($base),
                'parent_type' => 'caixa',
                'parent_id' => $caixa->id,
                // Deslocamento pequeno para o losango do splitter nao sumir
                // exatamente em cima do quadrado da CEO.
                'latitude' => $caixa->latitude + ($indice * 0.00015),
                'longitude' => $caixa->longitude,
                'input_ports' => 1,
                'output_ports' => $saidas,
                'ratio' => '1x'.$saidas,
                'status' => 'ativo',
            ]);

            $criados[] = $splitter;
            $this->generatedSplitters[] = $splitter;
        }

        return $criados;
    }

    /**
     * ftth_splitters.code tem indice unico e o contador zera a cada geracao,
     * entao gerar a mesma cidade de novo precisa de um sufixo em vez de
     * estourar o indice.
     */
    private function uniqueSplitterCode(string $base): string
    {
        $code = $base;
        $tentativas = 1;

        while (FtthSplitter::withTrashed()->where('code', $code)->exists() && $tentativas < 1000) {
            $tentativas++;
            $code = $base.'-'.$tentativas;
        }

        return $code;
    }

    private function describeSplitters(array $splitters): ?string
    {
        if (empty($splitters)) {
            return null;
        }

        $ratios = array_values(array_unique(array_map(fn ($s) => $s->ratio, $splitters)));

        return count($splitters).'x '.implode(' + ', $ratios);
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

        // haversine() devolve metros, e a coluna se chama total_distance_km.
        // Sem esta divisao a tela mostrava 11864 km para uma cidade de 11 km.
        return round($total / 1000, 3);
    }

    private function reset(): void
    {
        $this->ctoCount = 0;
        $this->totalCtos = 0;
        $this->totalCaixas = 0;
        $this->totalSplitters = 0;
        $this->pendingCtoCoords = [];
        $this->generatedCtos = [];
        $this->generatedCaixas = [];
        $this->generatedSplitters = [];
        $this->cityPolygon = null;
        $this->skippedOutOfBound = 0;
        $this->skippedTooClose = 0;
        $this->skippedNoStreet = 0;
        $this->streetNodeIndex = [];
        $this->placedCtoIndex = [];
        $this->placedCtoCellMeters = $this->minimumCtoSeparation();
        $this->streetCtoCoords = [];
        $this->chainCtoCoords = [];
    }
}
