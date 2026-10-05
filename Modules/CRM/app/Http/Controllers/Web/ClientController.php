<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Modules\Core\Models\Branch;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;

class ClientController extends Controller
{
    /** Campos livres varridos pela busca textual. */
    private const SEARCHABLE = ['name', 'document', 'email', 'cellphone', 'phone', 'login', 'codigo'];

    /** Selects com dominio fechado, validados antes de chegar na query. */
    private const STATUSES = ['active', 'inactive', 'suspended', 'canceled'];

    private const TYPES = ['individual', 'legal'];

    private const SUBSCRIBER_TYPES = ['pf', 'pj'];

    private const USAGE_TYPES = ['comercial', 'institucional', 'residencial'];

    private const CONTRACT_FILTERS = ['active', 'without', 'suspended'];

    private const ORDERS = ['recent', 'oldest', 'name', 'name_desc'];

    public function index(Request $request)
    {
        $groups = $this->groupOptions();
        $filters = $this->filters($request, $groups);

        $query = $this->baseQuery();

        $this->applyFilters($query, $filters);

        $clients = $query
            ->when($filters['order'] === 'oldest', fn ($q) => $q->oldest())
            ->when($filters['order'] === 'name', fn ($q) => $q->orderBy('name'))
            ->when($filters['order'] === 'name_desc', fn ($q) => $q->orderByDesc('name'))
            ->when($filters['order'] === 'recent', fn ($q) => $q->latest())
            ->paginate(15)
            ->withQueryString();

        return view('crm::clients.index', [
            'clients' => $clients,
            'branches' => $this->branchQuery()->get(),
            'groups' => $groups,
            'filters' => $filters,
        ]);
    }

    /**
     * Base da listagem: a rede inteira e visivel, a filial e filtro e nao bloqueio.
     *
     * @return Builder<Client>
     */
    private function baseQuery(): Builder
    {
        return $this->scopedQuery(['addresses', 'branch']);
    }

    /**
     * Unica origem de query de cliente por empresa. Toda acao que recebe um id
     * passa por aqui, para um id de outra empresa virar 404 em vez de acesso.
     *
     * @param  array<int, string>  $with
     * @return Builder<Client>
     */
    private function scopedQuery(array $with = []): Builder
    {
        // `forTenant` e o funil que corta por filial. Reimplementar o filtro
        // aqui com `forCompany` mantinha o usuario de uma loja enxergando a
        // agenda da matriz, que esta na mesma empresa.
        return Client::query()->with($with)->forTenant();
    }

    /**
     * @param  array<int, string>  $with
     */
    private function findScoped(array $with, $id): Client
    {
        return $this->scopedQuery($with)->whereKey($id)->firstOrFail();
    }

    /**
     * Só o que veio no request e caiu num dominio conhecido. Evita montar
     * query com valor arbitrario e mantem os selects da tela em sincronia.
     */
    private function filters(Request $request, Collection $groups): array
    {
        return [
            'search' => trim((string) $request->get('search', '')),
            'branch_id' => $request->filled('branch_id') ? $request->integer('branch_id') : null,
            'status' => $this->oneOf($request->get('status'), self::STATUSES),
            'type' => $this->oneOf($request->get('type'), self::TYPES),
            'tipo_assinante' => $this->oneOf($request->get('tipo_assinante'), self::SUBSCRIBER_TYPES),
            'tipo_utilizacao' => $this->oneOf($request->get('tipo_utilizacao'), self::USAGE_TYPES),
            'grupo' => $this->oneOf($request->get('grupo'), $groups->all()),
            'contract' => $this->oneOf($request->get('contract'), self::CONTRACT_FILTERS),
            'order' => $this->oneOf($request->get('order'), self::ORDERS) ?? 'recent',
        ];
    }

    private function oneOf($value, array $allowed): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return in_array($value, $allowed, true) ? $value : null;
    }

    /**
     * @param  Builder<Client>  $query
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        $query->when($filters['branch_id'], fn ($q, $branch) => $q->forBranch($branch));
        $query->when($filters['status'], fn ($q, $status) => $q->where('status', $status));
        $query->when($filters['type'], fn ($q, $type) => $q->where('type', $type));
        $query->when($filters['tipo_assinante'], fn ($q, $value) => $q->where('tipo_assinante', $value));
        $query->when($filters['tipo_utilizacao'], fn ($q, $value) => $q->where('tipo_utilizacao', $value));
        $query->when($filters['grupo'], fn ($q, $value) => $q->where('grupo', $value));

        if ($filters['contract'] === 'active') {
            $query->whereHas('activeContracts');
        } elseif ($filters['contract'] === 'without') {
            $query->whereDoesntHave('contracts');
        } elseif ($filters['contract'] === 'suspended') {
            $query->whereHas('contracts', fn ($q) => $q->whereIn('status', ['suspended', 'canceled']));
        }

        $search = $filters['search'];

        if ($search !== '') {
            $term = '%'.$search.'%';

            $query->where(function ($q) use ($term) {
                foreach (self::SEARCHABLE as $column) {
                    $q->orWhere($column, 'like', $term);
                }
            });
        }
    }

    /**
     * Grupos existentes na base, sem lista fixa: o grupo nasce do uso real.
     *
     * @return Collection<int, string>
     */
    private function groupOptions()
    {
        return $this->baseQuery()
            ->whereNotNull('grupo')
            ->where('grupo', '<>', '')
            ->distinct()
            ->orderBy('grupo')
            ->pluck('grupo');
    }

    /**
     * A regra do formulario usa exatamente a lista de filiais exibida no
     * select, para um parametro forjado nao aceitar filial de outra empresa
     * nem filial que o operador nao pode assumir.
     */
    private function branchRule(): In
    {
        return Rule::in($this->branchQuery()->pluck('id')->all());
    }

    public function create()
    {
        return view('crm::clients.create', ['branches' => $this->branchQuery()->get()]);
    }

    /**
     * Filiais que o operador pode escolher no cadastro. A matriz entra
     * primeiro, porque e o padrao da rede, e as demais sao as liberadas
     * para o usuario dentro da empresa atual.
     *
     * @return Builder<Branch>
     */
    private function branchQuery()
    {
        // `forTenant` restringe as filiais que aparecem no filtro e no select do
        // formulario. A versao anterior aceitava `parent_id is null`, o que
        // deixava o usuario de uma loja criar cliente na Matriz da rede.
        return Branch::query()->forTenant()
            ->orderByRaw('parent_id is null desc')
            ->orderBy('name');
    }

    public function store(Request $request)
    {
        $tenant = $this->tenantUniqueScope();

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', $this->branchRule()],
            'codigo' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'document' => ['required', 'string', 'max:20', Rule::unique('clients', 'document')->where($tenant)],
            'rg' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'login' => ['nullable', 'string', 'max:50', Rule::unique('clients', 'login')->where($tenant)],
            'senha' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'estado_civil' => 'nullable|string|max:20',
            'naturalidade' => 'nullable|string|max:100',
            'data_entrada' => 'nullable|date',
            'vcto_contrato' => 'nullable|date',
            'pai' => 'nullable|string|max:255',
            'mae' => 'nullable|string|max:255',
            'type' => 'required|in:individual,legal',
            'state_registration' => 'nullable|string|max:20',
            'nf' => 'boolean',
            'cfop' => 'nullable|string|max:10',
            'tipo_assinante' => 'nullable|string|max:20',
            'tipo_utilizacao' => 'nullable|string|max:20',
            'grupo' => 'nullable|string|max:2',
            'status' => 'in:active,inactive,suspended,canceled',
            'notes' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
        ]);

        $validated['nf'] = $request->boolean('nf');

        $client = Client::create($validated);

        $addressData = array_filter($request->only(['street', 'number', 'complement', 'referencia', 'neighborhood', 'city', 'state', 'zipcode']));
        if (! empty($addressData['street'])) {
            $client->addresses()->create($addressData);
        }

        return redirect()->route('crm.clients.index')
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function show($id)
    {
        $client = $this->findScoped([
            'addresses',
            'contracts.plan',
            'contracts.server',
            'invoices' => fn ($q) => $q->latest(),
            'serviceOrders.technician' => fn ($q) => $q->latest(),
        ], $id);

        return view('crm::clients.show', compact('client'));
    }

    public function edit($id)
    {
        $client = $this->findScoped(['addresses'], $id);

        return view('crm::clients.edit', compact('client') + ['branches' => $this->branchQuery()->get()]);
    }

    public function update(Request $request, $id)
    {
        $client = $this->findScoped([], $id);

        $tenant = $this->tenantUniqueScope($client);

        $validated = $request->validate([
            'branch_id' => ['required', 'integer', $this->branchRule()],
            'codigo' => 'nullable|string|max:20',
            'name' => 'string|max:255',
            'document' => ['string', 'max:20', Rule::unique('clients', 'document')->ignore($id)->where($tenant)],
            'rg' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'login' => ['nullable', 'string', 'max:50', Rule::unique('clients', 'login')->ignore($id)->where($tenant)],
            'senha' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'estado_civil' => 'nullable|string|max:20',
            'naturalidade' => 'nullable|string|max:100',
            'data_entrada' => 'nullable|date',
            'vcto_contrato' => 'nullable|date',
            'pai' => 'nullable|string|max:255',
            'mae' => 'nullable|string|max:255',
            'type' => 'in:individual,legal',
            'state_registration' => 'nullable|string|max:20',
            'nf' => 'boolean',
            'cfop' => 'nullable|string|max:10',
            'tipo_assinante' => 'nullable|string|max:20',
            'tipo_utilizacao' => 'nullable|string|max:20',
            'grupo' => 'nullable|string|max:2',
            'status' => 'in:active,inactive,suspended,canceled',
            'notes' => 'nullable|string',
            'street' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'referencia' => 'nullable|string|max:255',
            'neighborhood' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'zipcode' => 'nullable|string|max:9',
        ]);

        $validated['nf'] = $request->boolean('nf');

        $client->update($validated);

        if ($client->addresses()->exists()) {
            $addressData = array_filter($request->only(['street', 'number', 'complement', 'referencia', 'neighborhood', 'city', 'state', 'zipcode']));
            if (! empty($addressData['street'])) {
                $client->addresses()->first()->update($addressData);
            }
        }

        return redirect()->route('crm.clients.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function history($id)
    {
        $client = $this->findScoped([
            'addresses',
            'contracts.plan',
            'invoices',
            'serviceOrders.technician',
        ], $id);

        return view('crm::clients.history', compact('client'));
    }

    public function destroy($id)
    {
        $client = $this->findScoped([], $id);

        $client->delete();

        return redirect()->route('crm.clients.index')
            ->with('success', 'Cliente removido com sucesso.');
    }

    protected function tenantUniqueScope(?Client $client = null): callable
    {
        $companyId = $client?->company_id ?? TenantContext::companyId();
        $branchId = $client?->branch_id ?? TenantContext::branchId();

        return function ($query) use ($companyId, $branchId) {
            $query->where('company_id', $companyId)
                ->where('branch_id', $branchId);
        };
    }
}
