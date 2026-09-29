<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class LogsController extends Controller
{
    public function index(Request $request)
    {
        $servers = MikrotikServer::scoped()->where('is_active', true)->orderBy('name')->get();
        $selectedServer = null;
        $logs = [];

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::findScoped($serverId);
            if ($selectedServer) {
                try {
                    $service = new MikrotikService;
                    $service->connect($selectedServer);
                    $logs = $service->getLogEntries(100);
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', 'Erro ao conectar: '.$e->getMessage());
                }
            }
        }

        return view('infra::mikrotik.logs', compact('servers', 'selectedServer', 'logs'));
    }
}
