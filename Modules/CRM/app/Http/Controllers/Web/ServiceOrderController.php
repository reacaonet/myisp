<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\ServiceOrder;
use Modules\CRM\Models\Client;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Plan;
use Modules\CRM\Models\Technician;

class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceOrder::with(['client', 'technician']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                  ->orWhere('servico', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($c) => $c->where('name', 'like', "%{$search}%"));
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
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $technicians = Technician::where('is_active', true)->orderBy('name')->get();
        return view('crm::service_orders.create', compact('clients', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'plan_id' => 'nullable|exists:plans,id',
            'technician_id' => 'nullable|exists:technicians,id',
            'situacao' => 'required|in:O,I,NI,M,R,A,CS,C',
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

        $validated['codigo'] = 'OS-' . str_pad(ServiceOrder::max('id') + 1, 5, '0', STR_PAD_LEFT);
        $validated['emissao'] ??= now()->toDateString();
        $validated['preco'] ??= 0;
        $validated['status'] = 'active';

        ServiceOrder::create($validated);

        return redirect()->route('crm.service-orders.index')
            ->with('success', 'Ordem de servico criada com sucesso.');
    }

    public function show($id)
    {
        $order = ServiceOrder::with(['client', 'contract.plan', 'plan', 'technician'])->findOrFail($id);
        return view('crm::service_orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order = ServiceOrder::with('client', 'contract', 'plan', 'technician')->findOrFail($id);
        $clients = Client::where('status', 'active')->orderBy('name')->get();
        $technicians = Technician::where('is_active', true)->orderBy('name')->get();
        return view('crm::service_orders.edit', compact('order', 'clients', 'technicians'));
    }

    public function update(Request $request, $id)
    {
        $order = ServiceOrder::findOrFail($id);

        $validated = $request->validate([
            'client_id' => 'exists:clients,id',
            'contract_id' => 'nullable|exists:contracts,id',
            'plan_id' => 'nullable|exists:plans,id',
            'technician_id' => 'nullable|exists:technicians,id',
            'situacao' => 'in:O,I,NI,M,R,A,CS,C',
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
        $order = ServiceOrder::findOrFail($id);
        $order->delete();

        return redirect()->route('crm.service-orders.index')
            ->with('success', 'Ordem de servico removida.');
    }
}
