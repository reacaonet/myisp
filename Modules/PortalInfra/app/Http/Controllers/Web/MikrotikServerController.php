<?php

namespace Modules\PortalInfra\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Modules\Core\Models\Branch;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\MikrotikServer;
use Modules\CRM\Services\MikrotikService;

class MikrotikServerController extends Controller
{
    public function index()
    {
        $servers = MikrotikServer::scoped()->latest()->paginate(15);

        return view('infra::mikrotik-servers.index', compact('servers'));
    }

    public function create()
    {
        return view('infra::mikrotik-servers.create', [
            'branches' => $this->branchOptions((int) TenantContext::companyId()),
            'selectedBranchId' => TenantContext::branchId(),
        ]);
    }

    public function store(Request $request)
    {
        $companyId = $this->resolveCompanyId($request);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', $this->branchRule($companyId)],
            'name' => 'required|string|max:255',
            'ip' => [
                'required',
                'max:45',
                Rule::unique('mikrotik_servers', 'ip')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'port' => 'required|integer|min:1|max:65535',
            'login' => 'required|string|max:255',
            'senha' => 'required|string|min:3',
            'type' => 'required|in:pppoe,hotspot,both',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $companyId;
        $validated['is_active'] = $request->boolean('is_active');

        MikrotikServer::create($validated);

        return redirect()->route('infra.mikrotik-servers.index')
            ->with('success', 'Servidor MikroTik cadastrado com sucesso.');
    }

/**
     * Filiais da empresa que podem receber um equipamento. A filial e o que
     * amarra o servidor aos clientes dela no provisionamento.
     *
     * A empresa vem do formulario (o superadmin escolhe), entao ela e sempre
     * filtrada. `forTenant` entra por cima e corta a filial de quem so opera uma
     * loja; sem ele o seletor oferecia a Matriz.
     *
     * @return Collection<int, Branch>
     */

    private function branchOptions(int $companyId)
    {
        return Branch::query()
            ->where('company_id', $companyId)
            ->forTenant()
            ->orderByRaw('parent_id is null desc')
            ->orderBy('name')
            ->get();
    }

    private function branchRule(int $companyId): In
    {
        return Rule::in($this->branchOptions($companyId)->pluck('id')->all());
    }

    /**
     * O IP identifica o equipamento dentro da empresa: nao ha servidor global
     * nem fallback por endereco, cada empresa tem os seus.
     */
    private function resolveCompanyId(Request $request): int
    {
        if (TenantContext::isCrossTenant()) {
            $validated = $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
            ]);

            return (int) $validated['company_id'];
        }

        return (int) TenantContext::companyId();
    }

    public function show($id)
    {
        return redirect()->route('infra.mikrotik-servers.edit', $id);
    }

    public function edit($id)
    {
        $server = MikrotikServer::findScopedOrFail($id);

        return view('infra::mikrotik-servers.edit', [
            'server' => $server,
            'branches' => $this->branchOptions($server->company_id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $server = MikrotikServer::findScopedOrFail($id);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', $this->branchRule($server->company_id)],
            'name' => 'required|string|max:255',
            'ip' => [
                'required',
                'max:45',
                Rule::unique('mikrotik_servers', 'ip')
                    ->where(fn ($q) => $q->where('company_id', $server->company_id))
                    ->ignore($server->id),
            ],
            'port' => 'required|integer|min:1|max:65535',
            'login' => 'required|string|max:255',
            'senha' => 'nullable|string|min:3',
            'type' => 'required|in:pppoe,hotspot,both',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['senha'])) {
            unset($validated['senha']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $server->update($validated);

        return redirect()->route('infra.mikrotik-servers.index')
            ->with('success', 'Servidor MikroTik atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $server = MikrotikServer::findScopedOrFail($id);
        $server->delete();

        return redirect()->route('infra.mikrotik-servers.index')
            ->with('success', 'Servidor MikroTik removido com sucesso.');
    }

    public function testConnection($id)
    {
        $server = MikrotikServer::findScopedOrFail($id);
        $service = new MikrotikService;
        $result = $service->testConnection($server);

        if ($result['success']) {
            return back()->with('success', "Conexao OK! Identidade: {$result['identity']}");
        }

        return back()->with('error', "Falha na conexao: {$result['error']}");
    }
}
