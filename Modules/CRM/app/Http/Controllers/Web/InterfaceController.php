<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class InterfaceController extends Controller
{
    public function index(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $selectedServer = null;
        $interfaces = [];
        $resources = [];

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::find($serverId);
            if ($selectedServer) {
                try {
                    $service = new MikrotikService();
                    $service->connect($selectedServer);
                    $interfaces = $service->getInterfaces();
                    $resources = $service->getSystemResources();
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', "Erro ao conectar: " . $e->getMessage());
                }
            }
        }

        $resource = $resources[0] ?? [];

        return view('crm::mikrotik.interfaces', compact('servers', 'selectedServer', 'interfaces', 'resource'));
    }
}
