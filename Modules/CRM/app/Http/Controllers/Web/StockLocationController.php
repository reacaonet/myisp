<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\StockLocation;
use App\Models\User;

class StockLocationController extends Controller
{
    public function index(Request $request)
    {
        $query = StockLocation::with('user');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $locations = $query->orderBy('name')->paginate(15);

        return view('crm::stock.locations.index', compact('locations'));
    }

    public function create()
    {
        $technicians = User::whereHas('group', fn ($q) => $q->where('name', 'tecnico'))->orderBy('name')->get();
        return view('crm::stock.locations.create', compact('technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:deposit,technician',
            'user_id' => 'nullable|required_if:type,technician|exists:users,id',
        ]);

        if ($validated['type'] === 'deposit') {
            $validated['user_id'] = null;
        }

        StockLocation::create($validated);

        return redirect()->route('crm.stock-locations.index')
            ->with('success', 'Local criado com sucesso.');
    }

    public function edit($id)
    {
        $location = StockLocation::findOrFail($id);
        $technicians = User::whereHas('group', fn ($q) => $q->where('name', 'tecnico'))->orderBy('name')->get();

        return view('crm::stock.locations.edit', compact('location', 'technicians'));
    }

    public function update(Request $request, $id)
    {
        $location = StockLocation::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:deposit,technician',
            'user_id' => 'nullable|required_if:type,technician|exists:users,id',
        ]);

        if ($validated['type'] === 'deposit') {
            $validated['user_id'] = null;
        }

        $location->update($validated);

        return redirect()->route('crm.stock-locations.index')
            ->with('success', 'Local atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $location = StockLocation::findOrFail($id);
        $location->delete();

        return redirect()->route('crm.stock-locations.index')
            ->with('success', 'Local removido com sucesso.');
    }
}
