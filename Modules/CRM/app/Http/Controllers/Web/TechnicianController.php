<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Technician;

class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = Technician::latest()->paginate(15);
        return view('crm::technicians.index', compact('technicians'));
    }

    public function create()
    {
        return view('crm::technicians.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'nullable|string|max:255|unique:technicians,login',
            'senha' => 'nullable|string|min:6',
            'cargo' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        Technician::create($validated);

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $technician = Technician::findOrFail($id);
        return view('crm::technicians.edit', compact('technician'));
    }

    public function update(Request $request, $id)
    {
        $technician = Technician::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'login' => 'nullable|string|max:255|unique:technicians,login,' . $technician->id,
            'senha' => 'nullable|string|min:6',
            'cargo' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        $technician->update($validated);

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $technician = Technician::findOrFail($id);
        $technician->delete();

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico removido com sucesso.');
    }
}
