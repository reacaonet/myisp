<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Contract;

class ContractController extends Controller
{
    public function index()
    {
        return Contract::with(['client', 'plan'])->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'plan_id' => 'required|exists:plans,id',
            'activation_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'in:active,inactive,suspended,canceled',
            'billing_type' => 'required|in:boleto,pix,credit_card,debit_contract',
            'due_day' => 'required|integer|between:1,31',
            'pppoe_user' => 'nullable|string|max:255',
            'pppoe_password' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $contract = Contract::create($validated);

        return response()->json($contract->load(['client', 'plan']), 201);
    }

    public function show($id)
    {
        return Contract::with(['client', 'plan'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
            'plan_id' => 'exists:plans,id',
            'activation_date' => 'date',
            'due_date' => 'nullable|date',
            'status' => 'in:active,inactive,suspended,canceled',
            'billing_type' => 'in:boleto,pix,credit_card,debit_contract',
            'due_day' => 'integer|between:1,31',
            'pppoe_user' => 'nullable|string|max:255',
            'pppoe_password' => 'nullable|string|max:255',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $contract->update($validated);

        return response()->json($contract->load(['client', 'plan']));
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();

        return response()->noContent();
    }
}
