<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class NetworkMonitorController extends Controller
{
    public function index()
    {
        $servers = MikrotikServer::where('is_active', true)->get();
        $serverStats = [];

        foreach ($servers as $server) {
            $service = new MikrotikService();
            try {
                $service->connect($server);
                $resources = $service->getSystemResources();
                $identity = $service->getSystemIdentity();
                $pppoeActive = $service->getActiveUsers('pppoe');
                $hotspotActive = $service->getActiveUsers('hotspot');
                $service->disconnect();

                $serverStats[$server->id] = [
                    'online' => true,
                    'identity' => $identity[0]['name'] ?? $server->name,
                    'version' => $resources[0]['version'] ?? '-',
                    'board' => $resources[0]['board-name'] ?? '-',
                    'cpu_load' => $resources[0]['cpu-load'] ?? 0,
                    'cpu_count' => $resources[0]['cpu-count'] ?? 0,
                    'uptime' => $resources[0]['uptime'] ?? '-',
                    'free_memory' => $resources[0]['free-memory'] ?? 0,
                    'total_memory' => $resources[0]['total-memory'] ?? 0,
                    'free_hdd' => $resources[0]['free-hdd-space'] ?? 0,
                    'total_hdd' => $resources[0]['total-hdd-space'] ?? 0,
                    'pppoe_count' => is_array($pppoeActive['pppoe']) ? count($pppoeActive['pppoe']) : 0,
                    'hotspot_count' => is_array($hotspotActive['hotspot']) ? count($hotspotActive['hotspot']) : 0,
                ];
            } catch (\Exception $e) {
                $serverStats[$server->id] = [
                    'online' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return view('crm::network-monitor.index', compact('servers', 'serverStats'));
    }

    public function show($id)
    {
        $server = MikrotikServer::findOrFail($id);
        $service = new MikrotikService();

        try {
            $service->connect($server);
            $resources = $service->getSystemResources();
            $identity = $service->getSystemIdentity();
            $interfaces = $service->getInterfaces();
            $pppoeActive = $service->getActiveUsers('pppoe');
            $hotspotActive = $service->getActiveUsers('hotspot');
            $clock = $service->getSystemClock();
            $service->disconnect();

            return view('crm::network-monitor.show', compact(
                'server', 'resources', 'identity', 'interfaces',
                'pppoeActive', 'hotspotActive', 'clock'
            ));
        } catch (\Exception $e) {
            $service->disconnect();
            return back()->with('error', "Erro ao conectar: " . $e->getMessage());
        }
    }

    public function activeUsers($id)
    {
        $server = MikrotikServer::findOrFail($id);
        $service = new MikrotikService();

        try {
            $service->connect($server);
            $users = $service->getActiveUsers();
            $service->disconnect();

            return view('crm::network-monitor.active-users', compact('server', 'users'));
        } catch (\Exception $e) {
            $service->disconnect();
            return back()->with('error', "Erro ao conectar: " . $e->getMessage());
        }
    }

    public function refreshStats($id)
    {
        $server = MikrotikServer::findOrFail($id);
        $service = new MikrotikService();

        try {
            $service->connect($server);
            $resources = $service->getSystemResources();
            $identity = $service->getSystemIdentity();
            $pppoeActive = $service->getActiveUsers('pppoe');
            $hotspotActive = $service->getActiveUsers('hotspot');
            $service->disconnect();

            return response()->json([
                'online' => true,
                'identity' => $identity[0]['name'] ?? $server->name,
                'cpu_load' => $resources[0]['cpu-load'] ?? 0,
                'uptime' => $resources[0]['uptime'] ?? '-',
                'free_memory' => $resources[0]['free-memory'] ?? 0,
                'total_memory' => $resources[0]['total-memory'] ?? 0,
                'free_hdd' => $resources[0]['free-hdd-space'] ?? 0,
                'total_hdd' => $resources[0]['total-hdd-space'] ?? 0,
                'pppoe_count' => is_array($pppoeActive['pppoe']) ? count($pppoeActive['pppoe']) : 0,
                'hotspot_count' => is_array($hotspotActive['hotspot']) ? count($hotspotActive['hotspot']) : 0,
            ]);
        } catch (\Exception $e) {
            return response()->json(['online' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
