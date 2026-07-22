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
            throw new Exception("Nao foi possivel conectar ao servidor MikroTik {$server->name} ({$server->ip})");
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

            return [
                'success' => true,
                'identity' => $identity[0] ?? 'MikroTik',
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
        ?string $ip = null
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

        $this->recordProvisioning('pppoe', 'add', $login, $args, $response);

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

    public function addHotspotUser(
        string $login,
        string $password,
        string $profile,
        ?string $mac = null,
        ?string $comment = null,
        ?string $ip = null
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

        $this->recordProvisioning('hotspot', 'add', $login, $args, $response);

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

    public function getHotspotProfiles(): array
    {
        $this->ensureConnected();
        return $this->api->comm('/ip/hotspot/profile/print');
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

    private function recordProvisioning(string $type, string $action, string $login, array $params, array $response): void
    {
        ProvisioningRecord::create([
            'mikrotik_server_id' => $this->server?->id,
            'type' => $type,
            'action' => $action,
            'login' => $login,
            'params' => json_encode($params),
            'response' => json_encode($response),
            'success' => !empty($response) && ($response[0] ?? '') === '!done',
        ]);
    }

    public function getFirewallAddressList(string $listName): array
    {
        $this->sendCommand('/ip/firewall/address-list/getall', []);
        $results = [];
        while ($row = $this->readResponse()) {
            if (isset($row['list']) && $row['list'] === $listName) {
                $results[] = $row;
            }
        }
        return $results;
    }

    public function addFirewallAddressList(string $listName, string $address): void
    {
        $this->sendCommand('/ip/firewall/address-list/add', [
            'list' => $listName,
            'address' => $address,
        ]);
        $this->readResponse();
    }

    public function removeFirewallAddressList(string $listName, string $address): void
    {
        $this->sendCommand('/ip/firewall/address-list/remove', [
            '=.id' => '*F' . md5($listName . $address),
        ]);
        $this->readResponse();
    }

    public function getSystemResource(): array
    {
        $this->sendCommand('/system/resource/getall', []);
        $result = $this->readResponse();
        return $result ?: [];
    }

    public function getUptime(): string
    {
        $resource = $this->getSystemResource();
        return $resource['uptime'] ?? 'unknown';
    }

    public function getFirewallNat(): array
    {
        $this->sendCommand('/ip/firewall/nat/getall', []);
        $results = [];
        while ($row = $this->readResponse()) {
            $results[] = $row;
        }
        return $results;
    }
}
