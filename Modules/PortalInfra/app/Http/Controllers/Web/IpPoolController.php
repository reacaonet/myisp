<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class IpPoolController extends Controller
{
    public function index(Request $request)
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $selectedServer = null;
        $pools = [];

        if ($serverId = $request->get('server_id')) {
            $selectedServer = MikrotikServer::find($serverId);
            if ($selectedServer) {
                try {
                    $service = new MikrotikService();
                    $service->connect($selectedServer);
                    $pools = $service->listIpPools();
                    $service->disconnect();
                } catch (\Exception $e) {
                    return back()->with('error', "Erro ao conectar: " . $e->getMessage());
                }
            }
        }

        return view('infra::mikrotik.ip-pools', compact('servers', 'selectedServer', 'pools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:mikrotik_servers,id',
            'name' => 'required|string|max:255',
            'ranges' => 'required|string|max:500',
        ]);

        $server = MikrotikServer::findOrFail($validated['server_id']);

        try {
            $service = new MikrotikService();
            $service->connect($server);
            $service->addIpPool($validated['name'], $validated['ranges']);
            $service->disconnect();

            return redirect()->route('infra.mikrotik.ip-pools', ['server_id' => $server->id])
                ->with('success', "IP Pool '{$validated['name']}' criado com sucesso.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', "Erro ao criar pool: " . $e->getMessage());
        }
    }

    public function destroy(Request $request, $serverId)
    {
        $validated = $request->validate([
            'pool_name' => 'required|string',
        ]);

        $server = MikrotikServer::findOrFail($serverId);

        try {
            $service = new MikrotikService();
            $service->connect($server);
            $service->removeIpPool($validated['pool_name']);
            $service->disconnect();

            return redirect()->route('infra.mikrotik.ip-pools', ['server_id' => $server->id])
                ->with('success', "IP Pool '{$validated['pool_name']}' removido com sucesso.");
        } catch (\Exception $e) {
            return back()->with('error', "Erro ao remover pool: " . $e->getMessage());
        }
    }
}
