<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        $query = Client::with('addresses');

        if (! TenantContext::isCrossTenant()) {
            $query->forCompany(TenantContext::companyId());

            if (TenantContext::branchId()) {
                $query->forBranch(TenantContext::branchId());
            }
        }

        return $query->paginate();
    }

    public function store(Request $request)
    {
        $tenant = $this->tenantUniqueScope();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => ['required', 'string', 'max:20', Rule::unique('clients', 'document')->where($tenant)],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'type' => 'required|in:individual,legal',
            'state_registration' => 'nullable|string|max:20',
            'status' => 'in:active,inactive,suspended,canceled',
            'notes' => 'nullable|string',
        ]);

        $client = Client::create($validated);

        return response()->json($client, 201);
    }

    public function show($id)
    {
        return Client::with(['addresses', 'contracts.plan'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $tenant = $this->tenantUniqueScope($client);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'document' => ['string', 'max:20', Rule::unique('clients', 'document')->ignore($id)->where($tenant)],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'type' => 'in:individual,legal',
            'state_registration' => 'nullable|string|max:20',
            'status' => 'in:active,inactive,suspended,canceled',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        return response()->json($client);
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return response()->noContent();
    }

    public function addresses($id)
    {
        $client = Client::findOrFail($id);

        return $client->addresses;
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
