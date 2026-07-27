<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\ProvisioningRecord;
use Modules\CRM\Services\MikrotikService;

class ProvisionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProvisioningRecord::with(['mikrotikServer', 'client']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('login', 'ilike', "%{$search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('server_id')) {
            $query->where('mikrotik_server_id', $request->server_id);
        }

        $records = $query->latest()->paginate(20);
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();

        return view('infra::provisioning.index', compact('records', 'servers'));
    }

    public function create()
    {
        $servers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $clients = Client::orderBy('name')->get();

        return view('infra::provisioning.create', compact('servers', 'clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mikrotik_server_id' => 'required|exists:mikrotik_servers,id',
            'client_id' => 'nullable|exists:clients,id',
            'type' => 'required|in:pppoe,hotspot',
            'login' => 'required|string|max:255',
            'password' => 'required|string|min:3',
            'profile' => 'required|string|max:255',
            'mac' => 'nullable|string|max:17',
            'ip' => 'nullable|ip|max:45',
        ]);

        $server = MikrotikServer::findOrFail($validated['mikrotik_server_id']);
        $service = new MikrotikService();

        try {
            $service->connect($server);

            if ($validated['type'] === 'pppoe') {
                $service->addPppoeUser(
                    $validated['login'],
                    $validated['password'],
                    $validated['profile'],
                    $validated['mac'] ?? null,
                    $validated['client_id'] ? Client::find($validated['client_id'])->name : null,
                    $validated['ip'] ?? null
                );
            } else {
                $service->addHotspotUser(
                    $validated['login'],
                    $validated['password'],
                    $validated['profile'],
                    $validated['mac'] ?? null,
                    $validated['client_id'] ? Client::find($validated['client_id'])->name : null,
                    $validated['ip'] ?? null
                );
            }

            $service->disconnect();

            return redirect()->route('infra.provisioning.index')
                ->with('success', "Usuario {$validated['login']} provisionado com sucesso no {$server->name}.");

        } catch (\Exception $e) {
            $service->disconnect();

            ProvisioningRecord::create([
                'mikrotik_server_id' => $server->id,
                'client_id' => $validated['client_id'] ?? null,
                'type' => $validated['type'],
                'action' => 'add',
                'login' => $validated['login'],
                'params' => $validated,
                'success' => false,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', "Erro ao provisionar: " . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $record = ProvisioningRecord::findOrFail($id);

        if (!$record->mikrotik_server) {
            return back()->with('error', 'Servidor MikroTik nao encontrado.');
        }

        $service = new MikrotikService();

        try {
            $service->connect($record->mikrotikServer);

            if ($record->type === 'pppoe') {
                $service->removePppoeUser($record->login);
            } else {
                $service->removeHotspotUser($record->login);
            }

            $service->disconnect();

            ProvisioningRecord::create([
                'mikrotik_server_id' => $record->mikrotik_server_id,
                'client_id' => $record->client_id,
                'type' => $record->type,
                'action' => 'remove',
                'login' => $record->login,
                'success' => true,
            ]);

            return redirect()->route('infra.provisioning.index')
                ->with('success', "Usuario {$record->login} removido com sucesso.");

        } catch (\Exception $e) {
            $service->disconnect();
            return back()->with('error', "Erro ao remover: " . $e->getMessage());
        }
    }

    public function block($id)
    {
        $record = ProvisioningRecord::findOrFail($id);

        if (!$record->mikrotik_server) {
            return back()->with('error', 'Servidor MikroTik nao encontrado.');
        }

        $service = new MikrotikService();

        try {
            $service->connect($record->mikrotikServer);

            if ($record->type === 'pppoe') {
                $service->disconnectPppoeActive($record->login);
            } else {
                $service->disconnectHotspotActive($record->login);
            }

            $service->disconnect();

            return back()->with('success', "Sessao de {$record->login} desconectada com sucesso.");

        } catch (\Exception $e) {
            $service->disconnect();
            return back()->with('error', "Erro ao desconectar: " . $e->getMessage());
        }
    }

    public function profiles($serverId)
    {
        $server = MikrotikServer::findOrFail($serverId);
        $service = new MikrotikService();

        try {
            $service->connect($server);

            $pppProfiles = $service->getPppoeProfiles();
            $hotspotProfiles = $service->getHotspotProfiles();

            $service->disconnect();

            return response()->json([
                'ppp_profiles' => $pppProfiles,
                'hotspot_profiles' => $hotspotProfiles,
            ]);

        } catch (\Exception $e) {
            $service->disconnect();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function activeUsers($serverId)
    {
        $server = MikrotikServer::findOrFail($serverId);
        $service = new MikrotikService();

        try {
            $service->connect($server);
            $users = $service->getActiveUsers();
            $service->disconnect();

            return response()->json($users);

        } catch (\Exception $e) {
            $service->disconnect();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
