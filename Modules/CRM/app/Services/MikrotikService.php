<?php

namespace Modules\CRM\Services;

use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Models\ProvisioningRecord;
use Exception;

class MikrotikService
{
    private ?MikrotikApi $api = null;
    private ?MikrotikServer $server = null;

    public function connect(MikrotikServer $server): bool
    {
        $this->server = $server;
        $this->api = new MikrotikApi();

        $connected = $this->api->connect(
            $server->ip,
            $server->login,
            $server->senha
        );

        if (!$connected) {
            $detail = $this->api->lastError ?: 'erro desconhecido';
            throw new Exception("Nao foi possivel conectar ao servidor MikroTik {$server->name} ({$server->ip}) - {$detail}");
        }

        return true;
    }

    public function disconnect(): void
    {
        if ($this->api) {
            $this->api->disconnect();
            $this->api = null;
        }
    }

    public function testConnection(MikrotikServer $server): array
    {
        try {
            $this->connect($server);
            $identity = $this->api->comm('/system/identity/print');
            $this->disconnect();

            $name = $identity[0]['name'] ?? 'MikroTik';

            return [
                'success' => true,
                'identity' => $name,
            ];
        } catch (Exception $e) {
            $this->disconnect();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function addPppoeUser(
        string $login,
        string $password,
        string $profile,
        ?string $mac = null,
        ?string $comment = null,
        ?string $ip = null,
        ?int $clientId = null
    ): bool {
        $this->ensureConnected();

        $args = [
            'name' => $login,
            'password' => $password,
            'service' => 'pppoe',
            'profile' => $profile,
        ];

        if ($mac) {
            $args['caller-id'] = $mac;
        }

        if ($comment) {
            $args['comment'] = $comment;
        }

        if ($ip) {
            $args['address'] = $ip;
        }

        $response = $this->api->comm('/ppp/secret/add', $args);

        $this->recordProvisioning('pppoe', 'add', $login, $args, $response, $clientId);

        return true;
    }

    public function removePppoeUser(string $login): bool
    {
        $this->ensureConnected();

        $secrets = $this->api->comm('/ppp/secret/print', [
            '?name' => $login,
        ]);

        if (empty($secrets)) {
            return false;
        }

        $response = $this->api->comm('/ppp/secret/remove', [
            '.id' => $secrets[0]['.id'],
        ]);

        $this->recordProvisioning('pppoe', 'remove', $login, [], $response);

        return true;
    }

    public function updatePppoeUser(
        string $login,
        ?string $password = null,
        ?string $profile = null,
        ?string $ip = null,
        ?string $mac = null,
        ?int $clientId = null
    ): bool {
        $this->ensureConnected();

        $secrets = $this->api->comm('/ppp/secret/print', [
            '?name' => $login,
        ]);

        if (empty($secrets)) {
            return false;
        }

        $args = [
            '.id' => $secrets[0]['.id'],
        ];

        if ($password !== null) {
            $args['password'] = $password;
        }

        if ($profile !== null) {
            $args['profile'] = $profile;
        }

        if ($ip !== null && $ip !== '') {
            $args['address'] = $ip;
        }

        if ($mac !== null && $mac !== '') {
            $args['caller-id'] = $mac;
        }

        $response = $this->api->comm('/ppp/secret/set', $args);

        $this->recordProvisioning('pppoe', 'update', $login, $args, $response, $clientId);

        return true;
    }

    public function addHotspotUser(
        string $login,
        string $password,
        string $profile,
        ?string $mac = null,
        ?string $comment = null,
        ?string $ip = null,
        ?int $clientId = null
    ): bool {
        $this->ensureConnected();

        $args = [
            'name' => $login,
            'password' => $password,
            'profile' => $profile,
        ];

        if ($ip) {
            $args['address'] = $ip;
        }

        if ($mac) {
            $args['mac-address'] = $mac;
        }

        if ($comment) {
            $args['comment'] = $comment;
        }

        $response = $this->api->comm('/ip/hotspot/user/add', $args);

        $this->recordProvisioning('hotspot', 'add', $login, $args, $response, $clientId);

        return true;
    }

    public function removeHotspotUser(string $login): bool
    {
        $this->ensureConnected();

        $users = $this->api->comm('/ip/hotspot/user/print', [
            '?name' => $login,
        ]);

        if (empty($users)) {
            return false;
        }

        $response = $this->api->comm('/ip/hotspot/user/remove', [
            '.id' => $users[0]['.id'],
        ]);

        $this->recordProvisioning('hotspot', 'remove', $login, [], $response);

        return true;
    }

    public function updateHotspotUser(
        string $login,
        ?string $password = null,
        ?string $profile = null,
        ?string $ip = null,
        ?string $mac = null,
        ?int $clientId = null
    ): bool {
        $this->ensureConnected();

        $users = $this->api->comm('/ip/hotspot/user/print', [
            '?name' => $login,
        ]);

        if (empty($users)) {
            return false;
        }

        $args = [
            '.id' => $users[0]['.id'],
        ];

        if ($password !== null) {
            $args['password'] = $password;
        }

        if ($profile !== null) {
            $args['profile'] = $profile;
        }

        if ($ip !== null && $ip !== '') {
            $args['address'] = $ip;
        }

        if ($mac !== null && $mac !== '') {
            $args['mac-address'] = $mac;
        }

        $response = $this->api->comm('/ip/hotspot/user/set', $args);

        $this->recordProvisioning('hotspot', 'update', $login, $args, $response, $clientId);

        return true;
    }

    public function disconnectHotspotActive(string $login): bool
    {
        $this->ensureConnected();

        $actives = $this->api->comm('/ip/hotspot/active/print', [
            '?user' => $login,
        ]);

        if (empty($actives)) {
            return false;
        }

        $response = $this->api->comm('/ip/hotspot/active/remove', [
            '.id' => $actives[0]['.id'],
        ]);

        return true;
    }

    public function disconnectPppoeActive(string $login): bool
    {
        $this->ensureConnected();

        $actives = $this->api->comm('/ppp/active/print', [
            '?name' => $login,
        ]);

        if (empty($actives)) {
            return false;
        }

        $response = $this->api->comm('/ppp/active/remove', [
            '.id' => $actives[0]['.id'],
        ]);

        return true;
    }

    public function getActiveUsers(string $type = 'all'): array
    {
        $this->ensureConnected();

        if ($type === 'pppoe' || $type === 'all') {
            $pppoe = $this->api->comm('/ppp/active/print');
        } else {
            $pppoe = [];
        }

        if ($type === 'hotspot' || $type === 'all') {
            $hotspot = $this->api->comm('/ip/hotspot/active/print');
        } else {
            $hotspot = [];
        }

        return [
            'pppoe' => $pppoe,
            'hotspot' => $hotspot,
        ];
    }

    public function getPppoeSecrets(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ppp/secret/print');
    }

    public function getHotspotUsers(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ip/hotspot/user/print');
    }

    public function getPppoeProfiles(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ppp/profile/print');
    }

    public function ensurePppoeProfile(
        string $name,
        ?int $downloadKbps = null,
        ?int $uploadKbps = null,
        ?string $pool = null,
        ?string $localAddress = null,
        ?string $dns = null
    ): string {
        $this->ensureConnected();

        $dns = $dns ?: '8.8.8.8,8.8.4.4';

        $existing = $this->api->comm('/ppp/profile/print', [
            '?name' => $name,
        ]);

        if (!empty($existing)) {
            $update = [];

            if ($localAddress) {
                $update['local-address'] = $localAddress;
            }

            if ($pool) {
                $update['remote-address'] = $pool;
            }

            if (empty($existing[0]['dns-server'] ?? '') && $dns) {
                $update['dns-server'] = $dns;
            }

            if (!empty($update)) {
                $update['.id'] = $existing[0]['.id'];
                $this->api->comm('/ppp/profile/set', $update);
            }

            return $name;
        }

        $args = ['name' => $name];

        if (($downloadKbps && $downloadKbps > 0) || ($uploadKbps && $uploadKbps > 0)) {
            $args['rate-limit'] = (int) $downloadKbps . 'k/' . (int) $uploadKbps . 'k';
        }

        if ($pool) {
            $args['remote-address'] = $pool;
        }

        if ($localAddress) {
            $args['local-address'] = $localAddress;
        }

        $args['dns-server'] = $dns;

        $this->api->comm('/ppp/profile/add', $args);

        return $name;
    }

    public function resolveLanInfo(): array
    {
        $this->ensureConnected();

        $lanInterface = $this->determineLanInterface();
        $ip = null;

        if ($lanInterface) {
            $addresses = $this->api->comm('/ip/address/print', [
                '.proplist' => 'address,interface',
            ]);

            foreach ($addresses as $a) {
                if (($a['interface'] ?? null) === $lanInterface) {
                    $ip = $a['address'] ?? null;
                    $ip = explode('/', (string) $ip)[0];
                    break;
                }
            }
        }

        return ['interface' => $lanInterface, 'ip' => $ip];
    }

    public function resolvePppoePool(): array
    {
        $this->ensureConnected();

        $lan = $this->resolveLanInfo();
        $ip = $lan['ip'] ?? '10.0.0.1';

        $parts = array_map('intval', explode('.', $ip));
        $third = ($parts[2] ?? 0) + 1;

        if ($third > 254) {
            $third = 1;
        }

        $base = ($parts[0] ?? 10) . '.' . ($parts[1] ?? 0) . '.' . $third;
        $poolName = 'pool-pppoe';

        $pools = $this->api->comm('/ip/pool/print', [
            '.proplist' => 'name',
        ]);

        $found = false;

        foreach ($pools as $p) {
            if (($p['name'] ?? null) === $poolName) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->api->comm('/ip/pool/add', [
                'name' => $poolName,
                'ranges' => $base . '.10-' . $base . '.254',
            ]);
        }

        return [
            'pool' => $poolName,
            'gateway' => $base . '.1',
            'subnet' => $base . '.0/24',
            'start' => $base . '.10',
            'end' => $base . '.254',
        ];
    }

    public function ensurePppoeServer(?string $serviceName = null): array
    {
        $this->ensureConnected();

        $servers = $this->getPppoeServers();

        if (!empty($servers)) {
            foreach ($servers as $s) {
                if (($s['disabled'] ?? 'false') === 'true') {
                    $this->api->comm('/interface/pppoe-server/server/set', [
                        '.id' => $s['.id'],
                        'disabled' => 'no',
                    ]);
                }
            }

            return [
                'ok' => true,
                'created' => false,
                'message' => 'PPPoE Server ja configurado.',
            ];
        }

        $lan = $this->resolveLanInfo();

        if (empty($lan['interface'])) {
            return [
                'ok' => false,
                'created' => false,
                'message' => 'Nao foi possivel identificar a interface LAN da rede.',
            ];
        }

        $args = [
            'interface' => $lan['interface'],
            'service-name' => $serviceName ?: 'myisp-pppoe',
            'max-mtu' => 1492,
            'max-mru' => 1492,
            'authentication' => 'pap,chap,mschap1,mschap2',
            'default-profile' => 'default',
            'one-session-per-host' => 'yes',
            'disabled' => 'no',
        ];

        $this->api->comm('/interface/pppoe-server/server/add', $args);

        return [
            'ok' => true,
            'created' => true,
            'message' => 'PPPoE Server criado automaticamente na interface ' . $lan['interface'] . '.',
        ];
    }

    private function determineLanInterface(): ?string
    {
        $wanInterface = null;

        $routes = $this->api->comm('/ip/route/print', [
            '.proplist' => 'dst-address,interface,vrf-interface,gateway-status',
        ]);

        foreach ($routes as $r) {
            if (($r['dst-address'] ?? '') === '0.0.0.0/0') {
                $wanInterface = $r['vrf-interface'] ?? $r['interface'] ?? null;
                break;
            }
        }

        $addresses = $this->api->comm('/ip/address/print', [
            '.proplist' => 'address,interface,disabled,dynamic',
        ]);

        foreach ($addresses as $a) {
            if (($a['disabled'] ?? 'false') !== 'true'
                && empty($a['dynamic'] ?? '')
                && ($a['interface'] ?? null) !== $wanInterface) {
                return $a['interface'] ?? null;
            }
        }

        return $addresses[0]['interface'] ?? null;
    }

    public function getHotspotProfiles(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ip/hotspot/profile/print');
    }

    public function getHotspotUserProfiles(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ip/hotspot/user/profile/print');
    }

    public function ensureHotspotUserProfile(string $name, ?int $downloadKbps = null, ?int $uploadKbps = null): string
    {
        $this->ensureConnected();

        $existing = $this->api->comm('/ip/hotspot/user/profile/print', [
            '?name' => $name,
        ]);

        if (!empty($existing)) {
            return $name;
        }

        $args = ['name' => $name];

        if (($downloadKbps && $downloadKbps > 0) || ($uploadKbps && $uploadKbps > 0)) {
            $args['rate-limit'] = (int) $downloadKbps . 'k/' . (int) $uploadKbps . 'k';
        }

        $this->api->comm('/ip/hotspot/user/profile/add', $args);

        return $name;
    }

    public function getPppoeServers(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/interface/pppoe-server/server/print');
    }

    public function getHotspotServers(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ip/hotspot/print');
    }

    public function getSystemResources(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/system/resource/print', [
            '.proplist' => 'version,cpu,cpu-frequency,cpu-load,uptime,free-memory,free-hdd-space,total-hdd-space,total-memory,board-name',
        ]);
    }

    public function getSystemIdentity(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/system/identity/print');
    }

    public function getInterfaces(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/interface/print', [
            '.proplist' => 'name,type,running,disabled,rx-rate,tx-rate,rx-byte,tx-byte',
        ]);
    }

    public function getPppoeInterfaces(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/interface/pppoe-server/print');
    }

    public function getPing(string $address, int $count = 3): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ping', [
            'address' => $address,
            'count' => (string) $count,
        ]);
    }

    public function getSystemClock(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/system/clock/print');
    }

    public function getLogEntries(int $limit = 20): array
    {
        $this->ensureConnected();
        return $this->api->comm('/log/print', [
            '.proplist' => 'time,topics,message',
        ]);
    }

    public function addQueueSimple(
        string $name,
        string $target,
        string $maxRate,
        string $burstRate = '',
        string $burstThreshold = '',
        string $burstTime = ''
    ): bool {
        $this->ensureConnected();

        $args = [
            'name' => $name,
            'target' => $target,
            'max-limit' => $maxRate,
        ];

        if ($burstRate) {
            $args['burst-limit'] = $burstRate;
        }
        if ($burstThreshold) {
            $args['burst-threshold'] = $burstThreshold;
        }
        if ($burstTime) {
            $args['burst-time'] = $burstTime;
        }

        $this->api->comm('/queue/simple/add', $args);
        return true;
    }

    public function removeQueueSimple(string $name): bool
    {
        $this->ensureConnected();

        $queues = $this->api->comm('/queue/simple/print', [
            '?name' => $name,
        ]);

        if (empty($queues)) {
            return false;
        }

        $this->api->comm('/queue/simple/remove', [
            '.id' => $queues[0]['.id'],
        ]);

        return true;
    }

    public function getInterfaceStats(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/interface/print', [
            '.proplist' => 'name,type,running,disabled',
        ]);
    }

    private function ensureConnected(): void
    {
        if (!$this->api || !$this->api->isConnected()) {
            throw new Exception("Nao conectado ao servidor MikroTik");
        }
    }

    private function recordProvisioning(string $type, string $action, string $login, array $params, array $response, ?int $clientId = null): void
    {
        $existing = ProvisioningRecord::where('mikrotik_server_id', $this->server?->id)
            ->where('login', $login)
            ->latest()
            ->first();

        $data = [
            'client_id' => $clientId ?? $existing?->client_id,
            'type' => $type,
            'action' => $action,
            'login' => $login,
            'params' => json_encode($params),
            'response' => json_encode($response),
            'success' => empty($response),
        ];

        if ($existing) {
            $existing->update($data);
            return;
        }

        ProvisioningRecord::create(array_merge(['mikrotik_server_id' => $this->server?->id], $data));
    }

    public function getFirewallAddressList(string $listName): array
    {
        $this->ensureConnected();
        $all = $this->api->comm('/ip/firewall/address-list/print');
        $results = [];
        foreach ($all as $row) {
            if (isset($row['list']) && $row['list'] === $listName) {
                $results[] = $row;
            }
        }
        return $results;
    }

    public function addFirewallAddressList(string $listName, string $address): void
    {
        $this->ensureConnected();
        $this->api->comm('/ip/firewall/address-list/add', [
            'list' => $listName,
            'address' => $address,
        ]);
    }

    public function removeFirewallAddressList(string $listName, string $address): void
    {
        $this->ensureConnected();
        $entries = $this->api->comm('/ip/firewall/address-list/print', [
            '?list' => $listName,
            '?address' => $address,
        ]);
        if (!empty($entries) && isset($entries[0]['.id'])) {
            $this->api->comm('/ip/firewall/address-list/remove', [
                '.id' => $entries[0]['.id'],
            ]);
        }
    }

    public function getSystemResource(): array
    {
        $this->ensureConnected();
        return $this->getSystemResources();
    }

    public function getUptime(): string
    {
        $resource = $this->getSystemResources();
        return $resource[0]['uptime'] ?? 'unknown';
    }

    public function getFirewallNat(): array
    {
        $this->ensureConnected();
        $results = $this->api->comm('/ip/firewall/nat/print');
        return $results;
    }

    public function listWlanClients(): array
    {
        $this->ensureConnected();
        $results = $this->api->comm('/interface/wireless/registration/print');
        return $results;
    }

    public function listIpPools(): array
    {
        $this->ensureConnected();
        $results = $this->api->comm('/ip/pool/print');
        return $results;
    }

    public function addIpPool(string $name, string $addresses): bool
    {
        $this->ensureConnected();
        $this->api->comm('/ip/pool/add', [
            'name' => $name,
            'ranges' => $addresses,
        ]);
        return true;
    }

    public function removeIpPool(string $name): bool
    {
        $this->ensureConnected();
        $pools = $this->api->comm('/ip/pool/print', [
            '?name' => $name,
        ]);
        if (empty($pools) || !isset($pools[0]['.id'])) {
            return false;
        }
        $this->api->comm('/ip/pool/remove', [
            '.id' => $pools[0]['.id'],
        ]);
        return true;
    }

    public function listArp(): array
    {
        $this->ensureConnected();
        $results = $this->api->comm('/ip/arp/print');
        return $results;
    }
}
