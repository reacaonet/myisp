<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Manufacturer;

class ManufacturerController extends Controller
{
    public function index()
    {
        $manufacturers = Manufacturer::withCount('equipment')->latest()->paginate(15);
        return view('crm::manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        return view('crm::manufacturers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        Manufacturer::create($validated);

        return redirect()->route('crm.manufacturers.index')
            ->with('success', 'Fabricante cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        return view('crm::manufacturers.edit', compact('manufacturer'));
    }

    public function update(Request $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $manufacturer->update($validated);

        return redirect()->route('crm.manufacturers.index')
            ->with('success', 'Fabricante atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);
        $manufacturer->delete();

        return redirect()->route('crm.manufacturers.index')
            ->with('success', 'Fabricante removido com sucesso.');
    }
}
