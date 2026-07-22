<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Equipment;
use Modules\CRM\Models\Manufacturer;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipment::with('manufacturer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('model', 'ilike', "%{$search}%")
                  ->orWhere('serial_number', 'ilike', "%{$search}%")
                  ->orWhere('mac_address', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $equipment = $query->latest()->paginate(15);
        return view('crm::equipment.index', compact('equipment'));
    }

    public function create()
    {
        $manufacturers = Manufacturer::orderBy('name')->get();
        return view('crm::equipment.create', compact('manufacturers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:equipment,serial_number',
            'barcode' => 'nullable|string|max:255|unique:equipment,barcode',
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip|max:45',
            'type' => 'required|in:onu,router,switch,access_point,antenna,cable,other',
            'invoice_number' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_until' => 'nullable|date|after_or_equal:purchase_date',
            'supplier' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:available,allocated,maintenance,defective,retired',
            'notes' => 'nullable|string',
        ]);

        $validated['available_quantity'] = $validated['quantity'];

        Equipment::create($validated);

        return redirect()->route('crm.equipment.index')
            ->with('success', 'Equipamento cadastrado com sucesso.');
    }

    public function show($id)
    {
        $equipment = Equipment::with(['manufacturer', 'assignments.client', 'assignments.contract'])->findOrFail($id);
        return view('crm::equipment.show', compact('equipment'));
    }

    public function edit($id)
    {
        $equipment = Equipment::findOrFail($id);
        $manufacturers = Manufacturer::orderBy('name')->get();
        return view('crm::equipment.edit', compact('equipment', 'manufacturers'));
    }

    public function update(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:equipment,serial_number,' . $equipment->id,
            'barcode' => 'nullable|string|max:255|unique:equipment,barcode,' . $equipment->id,
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip|max:45',
            'type' => 'required|in:onu,router,switch,access_point,antenna,cable,other',
            'invoice_number' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_until' => 'nullable|date',
            'supplier' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'status' => 'required|in:available,allocated,maintenance,defective,retired',
            'notes' => 'nullable|string',
        ]);

        $diff = $validated['quantity'] - $equipment->quantity;
        $validated['available_quantity'] = max(0, $equipment->available_quantity + $diff);

        $equipment->update($validated);

        return redirect()->route('crm.equipment.index')
            ->with('success', 'Equipamento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();

        return redirect()->route('crm.equipment.index')
            ->with('success', 'Equipamento removido com sucesso.');
    }
}
