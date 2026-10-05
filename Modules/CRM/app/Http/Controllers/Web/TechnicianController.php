<?php

namespace Modules\CRM\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\Core\Models\UserGroup;

class TechnicianController extends Controller
{
    public function index()
    {
        $technicians = $this->scopedQuery()->latest()->paginate(15);

        return view('crm::technicians.index', compact('technicians'));
    }

    public function create()
    {
        return view('crm::technicians.create');
    }

    public function store(Request $request)
    {
        $tecnicoGroupId = $this->technicianGroupId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'cargo' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['user_group_id'] = $tecnicoGroupId;
        $validated['is_active'] = $request->boolean('is_active');

        User::create($validated);

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $technician = $this->findScopedOrFail($id);

        return view('crm::technicians.edit', compact('technician'));
    }

    public function update(Request $request, $id)
    {
        $technician = $this->findScopedOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$technician->id,
            'password' => 'nullable|string|min:8',
            'cargo' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $technician->update($validated);

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $technician = $this->findScopedOrFail($id);

        // Um tecnico nao remove a propria conta: se o unico admin da loja e ele
        // mesmo, o Depois nao sobra quem volte a criar usuario.
        if ($technician->id === auth()->id()) {
            return back()->with('error', 'Voce nao pode remover a propria conta de tecnico.');
        }

        $technician->delete();

        return redirect()->route('crm.technicians.index')
            ->with('success', 'Tecnico removido com sucesso.');
    }

    /**
     * Este CRUD cuida de tecnicos, e tecnico e um `User` no grupo `tecnico`.
     *
     * Sem este recorte, `edit`/`update`/`destroy` recebiam qualquer id da tabela
     * `users`: quem tinha `group.permission:technicians` editava, desativava e
     * apagava qualquer usuario do sistema, inclusive o superadmin, bastando
     * passar o id na URL.
     */
    protected function scopedQuery(): Builder
    {
        return User::query()->where('user_group_id', $this->technicianGroupId());
    }

    protected function findScopedOrFail(int $id): User
    {
        return $this->scopedQuery()->findOrFail($id);
    }

    protected function technicianGroupId(): ?int
    {
        return UserGroup::where('slug', 'tecnico')->value('id');
    }
}
