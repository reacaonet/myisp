<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CRM\Models\Client;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with('addresses');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cellphone', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(15);

        return view('crm::clients.index', compact('clients'));
    }

    public function create()
    {
        return view('crm::clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:clients,document',
            'rg' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'login' => 'nullable|string|max:50|unique:clients,login',
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

        $client = Client::create($validated);

        $addressData = array_filter($request->only(['street', 'number', 'complement', 'referencia', 'neighborhood', 'city', 'state', 'zipcode']));
        if (!empty($addressData['street'])) {
            $client->addresses()->create($addressData);
        }

        return redirect()->route('crm.clients.index')
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function show($id)
    {
        $client = Client::with([
            'addresses',
            'contracts.plan',
            'contracts.server',
            'invoices' => fn($q) => $q->latest(),
            'serviceOrders.technician' => fn($q) => $q->latest(),
        ])->findOrFail($id);

        return view('crm::clients.show', compact('client'));
    }

    public function edit($id)
    {
        $client = Client::with('addresses')->findOrFail($id);
        return view('crm::clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'codigo' => 'nullable|string|max:20',
            'name' => 'string|max:255',
            'document' => 'string|max:20|unique:clients,document,' . $id,
            'rg' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'login' => 'nullable|string|max:50|unique:clients,login,' . $id,
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

        $client->update($validated);

        if ($client->addresses()->exists()) {
            $addressData = array_filter($request->only(['street', 'number', 'complement', 'referencia', 'neighborhood', 'city', 'state', 'zipcode']));
            if (!empty($addressData['street'])) {
                $client->addresses()->first()->update($addressData);
            }
        }

        return redirect()->route('crm.clients.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function history($id)
    {
        $client = Client::with([
            'addresses',
            'contracts.plan',
            'invoices',
            'serviceOrders.technician',
        ])->findOrFail($id);

        return view('crm::clients.history', compact('client'));
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return redirect()->route('crm.clients.index')
            ->with('success', 'Cliente removido com sucesso.');
    }
}
