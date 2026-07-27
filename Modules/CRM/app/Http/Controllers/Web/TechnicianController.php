<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\Core\Models\UserGroup;

class TechnicianController extends Controller
{
    public function index()
    {
        $tecnicoGroupId = UserGroup::where('slug', 'tecnico')->value('id');
        $technicians = User::where('user_group_id', $tecnicoGroupId)->latest()->paginate(15);
        return view('crm::technicians.index', compact('technicians'));
    }

    public function create()
    {
        return view('crm::technicians.create');
    }

    public function store(Request $request)
    {
        $tecnicoGroupId = UserGroup::where('slug', 'tecnico')->value('id');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'cargo' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['user_group_id'] = $tecnicoGroupId;

        User::create($validated);

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $technician = User::findOrFail($id);
        return view('crm::technicians.edit', compact('technician'));
    }

    public function update(Request $request, $id)
    {
        $technician = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $technician->id,
            'password' => 'nullable|string|min:6',
            'cargo' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $technician->update($validated);

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $technician = User::findOrFail($id);
        $technician->delete();

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico removido com sucesso.');
    }
}
