<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Modules\Core\Models\UserGroup;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('group')->orderBy('name')->get();
        return view('core::users.index', compact('users'));
    }

    public function create()
    {
        $groups = UserGroup::where('is_active', true)->orderBy('name')->get();
        return view('core::users.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_group_id' => 'required|exists:user_groups,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_active'] = $request->boolean('is_active');

        $group = UserGroup::find($validated['user_group_id']);
        $validated['role'] = $group->slug;

        User::create($validated);

        return redirect()->route('core.users.index')
            ->with('success', 'Usuario criado com sucesso.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $groups = UserGroup::where('is_active', true)->orderBy('name')->get();
        return view('core::users.edit', compact('user', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'user_group_id' => 'required|exists:user_groups,id',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = bcrypt($validated['password']);
        }

        $group = UserGroup::find($validated['user_group_id']);
        $validated['role'] = $group->slug;

        $user->update($validated);

        return redirect()->route('core.users.index')
            ->with('success', 'Usuario atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return redirect()->route('core.users.index')
                ->with('error', 'Voce nao pode excluir seu proprio usuario.');
        }

        $user->delete();

        return redirect()->route('core.users.index')
            ->with('success', 'Usuario excluido com sucesso.');
    }
}
