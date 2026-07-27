<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class FirewallController extends Controller
{
    public function natRules(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $selectedServer = null;
        $natRules = [];

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::find($serverId);
            if ($selectedServer) {
                try {
                    $service = new MikrotikService();
                    $service->connect($selectedServer);
                    $natRules = $service->getFirewallNat();
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', "Erro ao conectar: " . $e->getMessage());
                }
            }
        }

        return view('infra::mikrotik.nat-rules', compact('servers', 'selectedServer', 'natRules'));
    }

    public function addressList(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $selectedServer = null;
        $addressList = [];
        $listName = $request->get('list_name', 'blocked_sites');

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::find($serverId);
            if ($selectedServer) {
                try {
                    $service = new MikrotikService();
                    $service->connect($selectedServer);
                    $addressList = $service->getFirewallAddressList($listName);
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', "Erro ao conectar: " . $e->getMessage());
                }
            }
        }

        return view('infra::mikrotik.address-list', compact('servers', 'selectedServer', 'addressList', 'listName'));
    }
}
