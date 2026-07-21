<?php

namespace Modules\Core\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\Address;

class AddressController extends Controller
{
    public function index()
    {
        return Address::paginate();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'addressable_type' => 'required|string',
            'addressable_id' => 'required|integer',
            'street' => 'required|string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
            'zipcode' => 'required|string|max:9',
            'notes' => 'nullable|string',
        ]);

        $address = Address::create($validated);

        return response()->json($address, 201);
    }

    public function show($id)
    {
        return Address::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $address = Address::findOrFail($id);

        $validated = $request->validate([
            'street' => 'string|max:255',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'string|max:255',
            'city' => 'string|max:255',
            'state' => 'string|size:2',
            'zipcode' => 'string|max:9',
            'notes' => 'nullable|string',
        ]);

        $address->update($validated);

        return response()->json($address);
    }

    public function destroy($id)
    {
        $address = Address::findOrFail($id);
        $address->delete();

        return response()->noContent();
    }
}
