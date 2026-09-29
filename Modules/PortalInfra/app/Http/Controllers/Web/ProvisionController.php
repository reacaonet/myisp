<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\ProvisioningRecord;
use Modules\CRM\Services\MikrotikService;

class ProvisionController extends Controller
{
    public function index(Request $request)
    {
        $query = ProvisioningRecord::with(['mikrotikServer', 'client', 'contract']);

        if (! TenantContext::isCrossTenant()) {
            $query->whereIn('mikrotik_server_id', MikrotikServer::scoped()->pluck('id'));
        }

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
        $servers = MikrotikServer::scoped()->where('is_active', true)->orderBy('name')->get();

        return view('infra::provisioning.index', compact('records', 'servers'));
    }

    /**
     * Cliente do provisionamento precisa estar na mesma empresa e na mesma filial
     * do servidor escolhido: e a filial que define em qual RB o usuario entra.
     *
     * @return array{0: ?Client, 1: ?Plan, 2: ?Contract}
     */
    private function clientForServer($clientId, MikrotikServer $server): array
    {
        if (empty($clientId)) {
            return [null, null, null];
        }

        $client = Client::query()
            ->where('company_id', $server->company_id)
            ->find($clientId);

        if (! $client) {
            throw ValidationException::withMessages([
                'client_id' => 'Cliente nao pertence a esta empresa.',
            ]);
        }

        if ((int) $client->branch_id !== (int) $server->branch_id) {
            throw ValidationException::withMessages([
                'mikrotik_server_id' => 'Servidor nao pertence a filial do cliente selecionado.',
            ]);
        }

        $contract = Contract::with('plan')
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        return [$client, $contract?->plan, $contract];
    }

    public function create()
    {
        $servers = MikrotikServer::scoped()->where('is_active', true)->orderBy('name')->get();
        $clients = Client::scoped()->orderBy('name')->get();

        return view('infra::provisioning.create', [
            'servers' => $servers,
            'clients' => $clients,
            'clientBranches' => $clients->pluck('branch_id', 'id'),
            'serverBranches' => $servers->pluck('branch_id', 'id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mikrotik_server_id' => 'required|exists:mikrotik_servers,id',
            'client_id' => 'nullable|exists:clients,id',
            'type' => 'required|in:pppoe,hotspot',
            'login' => 'required|string|max:255',
            'password' => 'required|string|min:3',
            'profile' => 'nullable|string|max:255',
            'mac' => 'nullable|string|max:17',
            'ip' => 'nullable|ip|max:45',
        ]);

        $server = MikrotikServer::findScopedOrFail($validated['mikrotik_server_id']);
        $service = new MikrotikService;

        [$client, $plan, $contract] = $this->clientForServer($validated['client_id'] ?? null, $server);

        try {
            $service->connect($server);

            $profile = $validated['profile'] ?? '';
            $planInfo = null;
            $pppoeServerMessage = '';

            if ($plan && $plan->download_speed > 0) {
                if ($validated['type'] === 'pppoe') {
                    $pppoe = $service->resolvePppoePool();
                    $profile = $service->ensurePppoeProfile(
                        $plan->slug ? 'plano-'.$plan->slug : 'plano-'.$plan->id,
                        (int) $plan->download_speed,
                        (int) $plan->upload_speed,
                        $pppoe['pool'],
                        $pppoe['gateway']
                    );
                } else {
                    $profile = $service->ensureHotspotUserProfile(
                        $plan->slug ? 'plano-'.$plan->slug : 'plano-'.$plan->id,
                        (int) $plan->download_speed,
                        (int) $plan->upload_speed
                    );
                }

                $planInfo = "{$plan->name} (".round($plan->download_speed / 1000).'M/'.round($plan->upload_speed / 1000).'M)';
            } elseif (! $profile) {
                $profiles = $validated['type'] === 'pppoe'
                    ? $service->getPppoeProfiles()
                    : $service->getHotspotUserProfiles();

                $profile = $profiles[0]['name'] ?? 'default';
            }

            if ($validated['type'] === 'pppoe') {
                $service->addPppoeUser(
                    $validated['login'],
                    $validated['password'],
                    $profile,
                    $validated['mac'] ?? null,
                    $client?->name,
                    $validated['ip'] ?? null,
                    $client?->id
                );

                $pppoeSetup = $service->ensurePppoeServer();
                $pppoeServerMessage = $pppoeSetup['message'] ?? '';
            } else {
                $service->addHotspotUser(
                    $validated['login'],
                    $validated['password'],
                    $profile,
                    $validated['mac'] ?? null,
                    $client?->name,
                    $validated['ip'] ?? null,
                    $client?->id
                );
            }

            $service->disconnect();

            $message = "Usuario {$validated['login']} provisionado com sucesso no {$server->name}.";

            if ($planInfo) {
                $message .= " Plano: {$planInfo}. Perfil: {$profile}.";
            }

            if ($pppoeServerMessage) {
                $message .= " {$pppoeServerMessage}";
            }

            return redirect()->route('infra.provisioning.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            $service->disconnect();

            $failedRecord = ProvisioningRecord::where('mikrotik_server_id', $server->id)
                ->where('login', $validated['login'])
                ->latest()
                ->first();

            if ($failedRecord) {
                $failedRecord->update([
                    'client_id' => $validated['client_id'] ?? null,
                    'contract_id' => $contract?->id,
                    'type' => $validated['type'],
                    'action' => 'add',
                    'params' => $validated,
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            } else {
                ProvisioningRecord::create([
                    'mikrotik_server_id' => $server->id,
                    'client_id' => $validated['client_id'] ?? null,
                    'contract_id' => $contract?->id,
                    'type' => $validated['type'],
                    'action' => 'add',
                    'login' => $validated['login'],
                    'params' => $validated,
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }

            return back()->withInput()
                ->with('error', 'Erro ao provisionar: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        $record = ProvisioningRecord::with(['mikrotikServer', 'client'])->findOrFail($id);
        $servers = MikrotikServer::scoped()->where('is_active', true)->orderBy('name')->get();
        $clients = Client::scoped()->orderBy('name')->get();

        return view('infra::provisioning.edit', compact('record', 'servers', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $record = ProvisioningRecord::findOrFail($id);

        $validated = $request->validate([
            'mikrotik_server_id' => 'required|exists:mikrotik_servers,id',
            'client_id' => 'nullable|exists:clients,id',
            'type' => 'required|in:pppoe,hotspot',
            'password' => 'nullable|string|max:255',
            'profile' => 'nullable|string|max:255',
            'mac' => 'nullable|string|max:17',
            'ip' => 'nullable|ip|max:45',
        ]);

        $server = MikrotikServer::findScopedOrFail($validated['mikrotik_server_id']);
        $service = new MikrotikService;

        [$client, $plan, $contract] = $this->clientForServer($validated['client_id'] ?? null, $server);

        try {
            $service->connect($server);

            $profile = $validated['profile'] ?? null;

            if ($plan && $plan->download_speed > 0) {
                if ($validated['type'] === 'pppoe') {
                    $pppoe = $service->resolvePppoePool();
                    $profile = $service->ensurePppoeProfile(
                        $plan->slug ? 'plano-'.$plan->slug : 'plano-'.$plan->id,
                        (int) $plan->download_speed,
                        (int) $plan->upload_speed,
                        $pppoe['pool'],
                        $pppoe['gateway']
                    );
                } else {
                    $profile = $service->ensureHotspotUserProfile(
                        $plan->slug ? 'plano-'.$plan->slug : 'plano-'.$plan->id,
                        (int) $plan->download_speed,
                        (int) $plan->upload_speed
                    );
                }
            }

            if ($validated['type'] === 'pppoe') {
                $updated = $service->updatePppoeUser(
                    $record->login,
                    $this->resolvePassword($validated['password']),
                    $profile,
                    $validated['ip'] ?? null,
                    $validated['mac'] ?? null,
                    $client?->id
                );

                $service->ensurePppoeServer();
            } else {
                $updated = $service->updateHotspotUser(
                    $record->login,
                    $this->resolvePassword($validated['password']),
                    $profile,
                    $validated['ip'] ?? null,
                    $validated['mac'] ?? null,
                    $client?->id
                );
            }

            $service->disconnect();

            if (! $updated) {
                return back()->withInput()->with('error', "Usuario {$record->login} nao encontrado no {$server->name}.");
            }

            $record->update([
                'mikrotik_server_id' => $server->id,
                'client_id' => $validated['client_id'] ?? null,
                'contract_id' => $contract?->id,
                'type' => $validated['type'],
                'params' => array_merge((array) $record->params, [
                    'password' => $this->resolvePassword($validated['password']),
                    'profile' => $profile,
                    'mac' => $validated['mac'] ?? null,
                    'ip' => $validated['ip'] ?? null,
                ]),
                'success' => true,
                'error' => null,
            ]);

            return redirect()->route('infra.provisioning.index')
                ->with('success', "Usuario {$record->login} atualizado com sucesso no {$server->name}.");

        } catch (\Exception $e) {
            $service->disconnect();

            $record->update([
                'params' => array_merge((array) $record->params, $validated),
                'success' => false,
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', 'Erro ao atualizar: '.$e->getMessage());
        }
    }

    private function resolvePassword(?string $password): ?string
    {
        if ($password === null || $password === '' || $password === '*') {
            return null;
        }

        return $password;
    }

    public function clientPlan($clientId)
    {
        $client = Client::scoped()->find($clientId);

        if (! $client) {
            return response()->json(['plan' => null, 'contract' => null]);
        }

        $contract = Contract::with('plan')
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $contract || ! $contract->plan) {
            return response()->json(['plan' => null, 'contract' => null]);
        }

        return response()->json([
            'plan' => $contract->plan,
            'contract' => $contract,
        ]);
    }

    public function destroy($id)
    {
        $record = ProvisioningRecord::findOrFail($id);

        if (! $record->mikrotikServer) {
            return back()->with('error', 'Servidor MikroTik nao encontrado.');
        }

        $service = new MikrotikService;

        try {
            $service->connect($record->mikrotikServer);

            if ($record->type === 'pppoe') {
                $service->removePppoeUser($record->login);
            } else {
                $service->removeHotspotUser($record->login);
            }

            $service->disconnect();

            ProvisioningRecord::where('mikrotik_server_id', $record->mikrotik_server_id)
                ->where('login', $record->login)
                ->delete();

            return redirect()->route('infra.provisioning.index')
                ->with('success', "Usuario {$record->login} removido com sucesso.");

        } catch (\Exception $e) {
            $service->disconnect();

            return back()->with('error', 'Erro ao remover: '.$e->getMessage());
        }
    }

    public function block($id)
    {
        $record = ProvisioningRecord::findOrFail($id);

        if (! $record->mikrotikServer) {
            return back()->with('error', 'Servidor MikroTik nao encontrado.');
        }

        $service = new MikrotikService;

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

            return back()->with('error', 'Erro ao desconectar: '.$e->getMessage());
        }
    }

    public function profiles($serverId)
    {
        $server = MikrotikServer::findScopedOrFail($serverId);
        $service = new MikrotikService;

        try {
            $service->connect($server);

            $pppProfiles = $service->getPppoeProfiles();
            $hotspotProfiles = $service->getHotspotUserProfiles();

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
        $server = MikrotikServer::findScopedOrFail($serverId);
        $service = new MikrotikService;

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
