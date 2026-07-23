<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\MikrotikServer;
use Modules\Core\Models\Server;
use Modules\CRM\Services\MikrotikService;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $query = Contract::with(['client', 'plan', 'server', 'mikrotikServer']);

        if ($search = $request->get('search')) {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        $contracts = $query->latest()->paginate(15);

        return view('crm::contracts.index', compact('contracts'));
    }

    public function create(Request $request)
    {
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $plans = Plan::where('is_active', true)->orderBy('name')->get();
        $servers = Server::where('is_active', true)->orderBy('name')->get();
        $mikrotikServers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        $selectedClientId = $request->get('client_id');
        return view('crm::contracts.create', compact('clients', 'plans', 'servers', 'mikrotikServers', 'selectedClientId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plan_id' => 'required|exists:plans,id',
            'server_id' => 'nullable|exists:servers,id',
            'mikrotik_server_id' => 'nullable|exists:mikrotik_servers,id',
            'activation_date' => 'required|date',
            'status' => 'in:active,inactive,suspended,canceled',
            'situacao' => 'in:S,I,C,N,F,D',
            'billing_type' => 'required|in:boleto,pix,credit_card,debit_contract',
            'due_day' => 'required|integer|between:1,31',
            'pppoe_user' => 'nullable|string|max:255',
            'pppoe_password' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'route_ip' => 'nullable|string',
            'ipv6' => 'nullable|string',
            'mac_address' => 'nullable|string|max:17',
            'mac_wireless' => 'nullable|string|max:17',
            'wpa_key' => 'nullable|string|max:255',
            'tipo_conexao' => 'required|in:pppoe,hotspot,iparp,dhcp',
            'discount' => 'nullable|numeric|min:0',
            'acrescimo' => 'nullable|numeric|min:0',
            'insento' => 'boolean',
            'autobloqueio' => 'boolean',
            'alterar_senha' => 'boolean',
            'ip_pool' => 'nullable|string',
            'observacao' => 'nullable|string',
            'ip_ubnt' => 'nullable|string',
            'porta_ubnt' => 'nullable|string',
            'login_ubnt' => 'nullable|string',
            'senha_ubnt' => 'nullable|string',
            'install_street' => 'nullable|string',
            'install_number' => 'nullable|string',
            'install_complement' => 'nullable|string',
            'install_neighborhood' => 'nullable|string',
            'install_city' => 'nullable|string',
            'install_state' => 'nullable|string|size:2',
            'install_zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
        ]);

        $validated['pedido'] = $validated['pedido'] ?? 'PED-' . str_pad(Contract::max('id') + 1, 6, '0', STR_PAD_LEFT);

        $contract = Contract::create($validated);

        if (($validated['status'] ?? 'active') === 'active' && !empty($validated['mikrotik_server_id']) && !empty($validated['pppoe_user'])) {
            $this->provisionOnMikrotik($contract);
        }

        return redirect()->route('crm.contracts.index')
            ->with('success', 'Contrato criado com sucesso.');
    }

    public function show($id)
    {
        $contract = Contract::with(['client.addresses', 'plan', 'server', 'mikrotikServer', 'invoices.payments'])->findOrFail($id);
        return view('crm::contracts.show', compact('contract'));
    }

    public function edit($id)
    {
        $contract = Contract::with('client', 'plan')->findOrFail($id);
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $plans = Plan::where('is_active', true)->orderBy('name')->get();
        $servers = Server::where('is_active', true)->orderBy('name')->get();
        $mikrotikServers = MikrotikServer::where('is_active', true)->orderBy('name')->get();
        return view('crm::contracts.edit', compact('contract', 'clients', 'plans', 'servers', 'mikrotikServers'));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
            'plan_id' => 'exists:plans,id',
            'server_id' => 'nullable|exists:servers,id',
            'mikrotik_server_id' => 'nullable|exists:mikrotik_servers,id',
            'activation_date' => 'date',
            'status' => 'in:active,inactive,suspended,canceled',
            'situacao' => 'in:S,I,C,N,F,D',
            'billing_type' => 'in:boleto,pix,credit_card,debit_contract',
            'due_day' => 'integer|between:1,31',
            'pppoe_user' => 'nullable|string|max:255',
            'pppoe_password' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'route_ip' => 'nullable|string',
            'ipv6' => 'nullable|string',
            'mac_address' => 'nullable|string|max:17',
            'mac_wireless' => 'nullable|string|max:17',
            'wpa_key' => 'nullable|string|max:255',
            'tipo_conexao' => 'in:pppoe,hotspot,iparp,dhcp',
            'discount' => 'nullable|numeric|min:0',
            'acrescimo' => 'nullable|numeric|min:0',
            'insento' => 'boolean',
            'autobloqueio' => 'boolean',
            'alterar_senha' => 'boolean',
            'ip_pool' => 'nullable|string',
            'observacao' => 'nullable|string',
            'ip_ubnt' => 'nullable|string',
            'porta_ubnt' => 'nullable|string',
            'login_ubnt' => 'nullable|string',
            'senha_ubnt' => 'nullable|string',
            'install_street' => 'nullable|string',
            'install_number' => 'nullable|string',
            'install_complement' => 'nullable|string',
            'install_neighborhood' => 'nullable|string',
            'install_city' => 'nullable|string',
            'install_state' => 'nullable|string|size:2',
            'install_zipcode' => 'nullable|string|max:9',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $contract->status;
        $contract->update($validated);

        if ($oldStatus !== 'active' && $contract->status === 'active' && $contract->mikrotik_server_id && $contract->pppoe_user) {
            $this->provisionOnMikrotik($contract);
        }

        if ($oldStatus === 'active' && $contract->status === 'canceled' && $contract->mikrotik_server_id && $contract->pppoe_user) {
            $this->deprovisionOnMikrotik($contract);
        }

        return redirect()->route('crm.contracts.index')
            ->with('success', 'Contrato atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);

        if ($contract->status === 'active' && $contract->mikrotik_server_id && $contract->pppoe_user) {
            $this->deprovisionOnMikrotik($contract);
        }

        $contract->delete();

        return redirect()->route('crm.contracts.index')
            ->with('success', 'Contrato removido com sucesso.');
    }

    private function provisionOnMikrotik(Contract $contract): void
    {
        try {
            $service = new MikrotikService();
            $service->connect($contract->mikrotikServer);

            $profile = $contract->plan->name ?? 'default';

            if ($contract->tipo_conexao === 'pppoe') {
                $service->addPppoeUser(
                    $contract->pppoe_user,
                    $contract->pppoe_password ?? '123456',
                    $profile,
                    $contract->mac_address,
                    $contract->client->name ?? null,
                    $contract->ip_address
                );
            } elseif ($contract->tipo_conexao === 'hotspot') {
                $service->addHotspotUser(
                    $contract->pppoe_user,
                    $contract->pppoe_password ?? '123456',
                    $profile,
                    $contract->mac_address,
                    $contract->client->name ?? null,
                    $contract->ip_address
                );
            }

            $service->disconnect();
        } catch (\Exception $e) {
            \Log::warning("Erro ao provisionar contrato #{$contract->id} no MikroTik: " . $e->getMessage());
        }
    }

    private function deprovisionOnMikrotik(Contract $contract): void
    {
        try {
            $service = new MikrotikService();
            $service->connect($contract->mikrotikServer);

            if ($contract->tipo_conexao === 'pppoe') {
                $service->removePppoeUser($contract->pppoe_user);
            } elseif ($contract->tipo_conexao === 'hotspot') {
                $service->removeHotspotUser($contract->pppoe_user);
            }

            if ($contract->ip_address) {
                $service->removeFirewallAddressList('myisp-blocked', $contract->ip_address);
            }

            $service->disconnect();
        } catch (\Exception $e) {
            \Log::warning("Erro ao desprovisionar contrato #{$contract->id} no MikroTik: " . $e->getMessage());
        }
    }
}
