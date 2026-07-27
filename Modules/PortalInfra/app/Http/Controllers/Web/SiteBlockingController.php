<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class SiteBlockingController extends Controller
{
    public function index(Request $request)
    {
        $servers = MikrotikServer::orderBy('name')->get();
        $selectedServer = null;
        $blockedSites = [];

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::find($serverId);

            if ($selectedServer) {
                try {
                    $service = new MikrotikService();
                    $service->connect($selectedServer);
                    $blockedSites = $service->getFirewallAddressList('blocked_sites');
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', "Erro ao conectar: " . $e->getMessage());
                }
            }
        }

        return view('infra::site-blocking.index', compact('servers', 'selectedServer', 'blockedSites'));
    }

    public function block(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:mikrotik_servers,id',
            'address' => 'required|string|max:255',
            'list_name' => 'nullable|string|max:100',
        ]);

        $server = MikrotikServer::findOrFail($validated['server_id']);
        $listName = $validated['list_name'] ?? 'blocked_sites';

        try {
            $service = new MikrotikService();
            $service->connect($server);
            $service->addFirewallAddressList($listName, $validated['address']);
            $service->disconnect();

            return back()->with('success', "Site {$validated['address']} bloqueado com sucesso na lista {$listName}.");

        } catch (\Exception $e) {
            return back()->with('error', "Erro ao bloquear: " . $e->getMessage());
        }
    }

    public function unblock(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:mikrotik_servers,id',
            'address' => 'required|string|max:255',
            'list_name' => 'nullable|string|max:100',
        ]);

        $server = MikrotikServer::findOrFail($validated['server_id']);
        $listName = $validated['list_name'] ?? 'blocked_sites';

        try {
            $service = new MikrotikService();
            $service->connect($server);
            $service->removeFirewallAddressList($listName, $validated['address']);
            $service->disconnect();

            return back()->with('success', "Site {$validated['address']} desbloqueado com sucesso da lista {$listName}.");

        } catch (\Exception $e) {
            return back()->with('error', "Erro ao desbloquear: " . $e->getMessage());
        }
    }
}
