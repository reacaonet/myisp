<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Modules\Core\Models\UserGroup;
use Modules\Core\Services\TenantContext;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\ServiceOrder;

class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceOrder::query()->scoped()->with(['client', 'technician']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('servico', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        if ($situacao = $request->get('situacao')) {
            $query->where('situacao', $situacao);
        }

        $orders = $query->latest()->paginate(15);

        return view('crm::service_orders.index', compact('orders'));
    }

    public function create()
    {
        $clients = Client::scoped()->where('status', 'active')->orderBy('name')->get();
        $technicians = $this->getTechnicians();

        return view('crm::service_orders.create', compact('clients', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => ['required', $this->scopedClientRule()],
            'contract_id' => 'nullable|exists:contracts,id',
            'plan_id' => 'nullable|exists:plans,id',
            'technician_id' => 'nullable|exists:users,id',
            'situacao' => 'required|in:O,I,NI,M,R,A,CS,C,F',
            'servico' => 'nullable|string',
            'tipo_servico' => 'nullable|in:instalacao,manutencao,cancelamento,recuperacao,orcamento,visita_tecnica,outro',
            'emissao' => 'nullable|date',
            'hora_abertura' => 'nullable',
            'orcamento' => 'nullable|date',
            'aprovacao' => 'nullable|date',
            'saida' => 'nullable|date',
            'data_agendamento' => 'nullable|date',
            'hora_agendamento' => 'nullable',
            'problema' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'solucao' => 'nullable|string',
            'atendente' => 'nullable|string|max:255',
            'preco' => 'nullable|numeric|min:0',
            'serie' => 'nullable|string|max:50',
        ]);

        $validated['codigo'] = 'OS-'.str_pad(ServiceOrder::max('id') + 1, 5, '0', STR_PAD_LEFT);
        $validated['emissao'] ??= now()->toDateString();
        $validated['preco'] ??= 0;
        $validated['status'] = 'active';

        ServiceOrder::create($validated);

        return redirect()->route('crm.service-orders.index')
            ->with('success', 'Ordem de servico criada com sucesso.');
    }

    public function show($id)
    {
        $order = ServiceOrder::query()->scoped()->with(['client', 'contract.plan', 'plan', 'technician'])->findOrFail($id);

        return view('crm::service_orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order = ServiceOrder::query()->scoped()->with('client', 'contract', 'plan', 'technician')->findOrFail($id);
        $clients = Client::scoped()->where('status', 'active')->orderBy('name')->get();
        $technicians = $this->getTechnicians();

        return view('crm::service_orders.edit', compact('order', 'clients', 'technicians'));
    }

    public function update(Request $request, $id)
    {
        $order = ServiceOrder::findScopedOrFail((int) $id);

        $validated = $request->validate([
            'client_id' => [$this->scopedClientRule()],
            'contract_id' => 'nullable|exists:contracts,id',
            'plan_id' => 'nullable|exists:plans,id',
            'technician_id' => 'nullable|exists:users,id',
            'situacao' => 'in:O,I,NI,M,R,A,CS,C,F',
            'servico' => 'nullable|string',
            'tipo_servico' => 'nullable|in:instalacao,manutencao,cancelamento,recuperacao,orcamento,visita_tecnica,outro',
            'emissao' => 'nullable|date',
            'hora_abertura' => 'nullable',
            'orcamento' => 'nullable|date',
            'aprovacao' => 'nullable|date',
            'saida' => 'nullable|date',
            'data_agendamento' => 'nullable|date',
            'hora_agendamento' => 'nullable',
            'problema' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'solucao' => 'nullable|string',
            'atendente' => 'nullable|string|max:255',
            'preco' => 'nullable|numeric|min:0',
            'serie' => 'nullable|string|max:50',
            'status' => 'in:active,closed,canceled',
            'encerrado' => 'boolean',
        ]);

        $order->update($validated);

        return redirect()->route('crm.service-orders.index')
            ->with('success', 'Ordem de servico atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $order = ServiceOrder::findScopedOrFail((int) $id);
        $order->delete();

        return redirect()->route('crm.service-orders.index')
            ->with('success', 'Ordem de servico removida.');
    }

    public function start($id)
    {
        $order = ServiceOrder::findScopedOrFail((int) $id);
        $order->update([
            'situacao' => 'A',
            'status' => 'active',
            'saida' => now()->toDateString(),
        ]);

        return redirect()->route('crm.service-orders.show', $order)
            ->with('success', 'OS iniciada com sucesso.');
    }

    public function complete($id)
    {
        $order = ServiceOrder::findScopedOrFail((int) $id);
        $order->update([
            'situacao' => 'F',
            'status' => 'closed',
            'encerrado' => true,
        ]);

        return redirect()->route('crm.service-orders.show', $order)
            ->with('success', 'OS concluida com sucesso.');
    }

    public function assign(Request $request, $id)
    {
        $order = ServiceOrder::findScopedOrFail((int) $id);

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $order->update($validated);

        return redirect()->route('crm.service-orders.show', $order)
            ->with('success', 'Tecnico atribuido com sucesso.');
    }

    private function getTechnicians()
    {
        $tecnicoGroupId = UserGroup::where('slug', 'tecnico')->value('id');

        $query = User::where('user_group_id', $tecnicoGroupId)->where('is_active', true);

        // Tecnico de outra loja nao aparece na lista de atribuicao: a OS nao
        // pode ser repassada para quem nao enxerga o cliente dela.
        if (! TenantContext::isCrossTenant()) {
            $query->where(function ($q) {
                $q->whereHas('branches', fn ($b) => $b->whereIn('branches.id', TenantContext::allowedBranchIds()))
                    ->orWhereDoesntHave('branches');
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * `exists:clients,id` aceitava o cliente de qualquer loja: bastava o id. A
     * regra roda contra o cliente ja escopado, e por isso rejeita quem esta
     * fora da filial do usuario.
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
