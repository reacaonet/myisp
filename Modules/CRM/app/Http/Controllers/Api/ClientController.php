<?php

namespace Modules\CRM\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Client;

class ClientController extends Controller
{
    public function index()
    {
        return Client::with('addresses')->paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:20|unique:clients,document',
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

        $validated = $request->validate([
            'name' => 'string|max:255',
            'document' => 'string|max:20|unique:clients,document,' . $id,
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
}
