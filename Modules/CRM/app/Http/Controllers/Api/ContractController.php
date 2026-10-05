<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\Request;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;

class ContractController extends Controller
{
    public function index()
    {
        return Contract::query()->scoped()->with(['client', 'plan'])->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', $this->scopedClientRule()],
            'plan_id' => 'required|exists:plans,id',
            'activation_date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'in:active,inactive,suspended,canceled',
            'billing_type' => 'required|in:boleto,pix,credit_card,debit_contract',
            'due_day' => 'required|integer|between:1,31',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $contract = Contract::create($validated);

        return response()->json($contract->load(['client', 'plan']), 201);
    }

    public function show($id)
    {
        return Contract::query()->scoped()->with(['client', 'plan'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findScopedOrFail((int) $id);

        $validated = $request->validate([
            'client_id' => [$this->scopedClientRule()],
            'plan_id' => 'exists:plans,id',
            'activation_date' => 'date',
            'due_date' => 'nullable|date',
            'status' => 'in:active,inactive,suspended,canceled',
            'billing_type' => 'in:boleto,pix,credit_card,debit_contract',
            'due_day' => 'integer|between:1,31',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $contract->update($validated);

        return response()->json($contract->load(['client', 'plan']));
    }

    public function destroy($id)
    {
        Contract::findScopedOrFail((int) $id)->delete();

        return response()->noContent();
    }

    /**
     * `contracts` nao tem empresa: o contrato pertence ao cliente, e o cliente
     * e que carrega o escopo de filial. Aceitar `exists:clients,id` deixava
     * qualquer loja criar contrato em nome de cliente de outra.
     */
    private function scopedClientRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) {
            if (! $value) {
                $fail('Escolha um cliente.');

                return;
            }

            if (! Client::scoped()->whereKey($value)->exists()) {
                $fail('Cliente invalido.');
            }
        };
    }
}
