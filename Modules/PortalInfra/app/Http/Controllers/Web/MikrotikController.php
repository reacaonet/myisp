<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class MikrotikController extends Controller
{
    public function pppoeActive(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $selectedServer = null;
        $activeSessions = [];

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::find($serverId);
            if ($selectedServer) {
                try {
                    $service = new MikrotikService();
                    $service->connect($selectedServer);
                    $activeSessions = $service->getActiveUsers('pppoe')['pppoe'] ?? [];
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', "Erro ao conectar: " . $e->getMessage());
                }
            }
        }

        return view('infra::mikrotik.pppoe-active', compact('servers', 'selectedServer', 'activeSessions'));
    }

    public function hotspotActive(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $selectedServer = null;
        $activeSessions = [];

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::find($serverId);
            if ($selectedServer) {
                try {
                    $service = new MikrotikService();
                    $service->connect($selectedServer);
                    $activeSessions = $service->getActiveUsers('hotspot')['hotspot'] ?? [];
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', "Erro ao conectar: " . $e->getMessage());
                }
            }
        }

        return view('infra::mikrotik.hotspot-active', compact('servers', 'selectedServer', 'activeSessions'));
    }

    public function kickPppoe(Request $request, $serverId)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $server = MikrotikServer::findOrFail($serverId);

        try {
            $service = new MikrotikService();
            $service->connect($server);
            $service->api->comm('/ppp/active/remove', [
                '.id' => $validated['session_id'],
            ]);
            $service->disconnect();

            return back()->with('success', 'Sessao PPPoE desconectada com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', "Erro ao desconectar: " . $e->getMessage());
        }
    }

    public function kickHotspot(Request $request, $serverId)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $server = MikrotikServer::findOrFail($serverId);

        try {
            $service = new MikrotikService();
            $service->connect($server);
            $service->api->comm('/ip/hotspot/active/remove', [
                '.id' => $validated['session_id'],
            ]);
            $service->disconnect();

            return back()->with('success', 'Sessao Hotspot desconectada com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', "Erro ao desconectar: " . $e->getMessage());
        }
    }
}
