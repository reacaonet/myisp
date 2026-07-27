<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\UserGroup;
use Modules\Core\Models\User;

class UserGroupController extends Controller
{
    public function index()
    {
        $groups = UserGroup::withCount('users')->orderBy('name')->get();
        return view('core::user-groups.index', compact('groups'));
    }

    public function create()
    {
        $permissions = \Modules\Core\Models\GroupPermission::class::MENU_PERMISSIONS();
        return view('core::user-groups.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:user_groups,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $group = UserGroup::create($validated);

        $this->syncPermissions($group, $request);

        return redirect()->route('core.user-groups.index')
            ->with('success', "Grupo \"{$group->name}\" criado com sucesso.");
    }

    public function edit($id)
    {
        $group = UserGroup::with('permissions')->findOrFail($id);
        $allPermissions = \Modules\Core\Models\GroupPermission::class::MENU_PERMISSIONS();

        $groupPerms = $group->permissions->pluck('permission_key')->toArray();
        $groupPermsGranted = $group->permissions->where('granted', true)->pluck('permission_key')->toArray();

        $permissions = [];
        foreach ($allPermissions as $key => $label) {
            $permissions[$key] = [
                'label' => $label,
                'granted' => in_array($key, $groupPermsGranted),
            ];
        }

        return view('core::user-groups.edit', compact('group', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $group = UserGroup::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:user_groups,slug,' . $group->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $group->update($validated);

        $this->syncPermissions($group, $request);

        // Atualizar role dos usuarios deste grupo
        $group->users()->update(['role' => $group->slug]);

        return redirect()->route('core.user-groups.index')
            ->with('success', "Grupo \"{$group->name}\" atualizado com sucesso.");
    }

    public function destroy($id)
    {
        $group = UserGroup::findOrFail($id);

        if ($group->users()->count() > 0) {
            return redirect()->route('core.user-groups.index')
                ->with('error', "Nao e possivel excluir o grupo \"{$group->name}\" pois existem usuarios vinculados.");
        }

        $group->delete();

        return redirect()->route('core.user-groups.index')
            ->with('success', 'Grupo excluido com sucesso.');
    }

    private function syncPermissions(UserGroup $group, Request $request): void
    {
        $allPermissions = \Modules\Core\Models\GroupPermission::class::MENU_PERMISSIONS();

        $group->permissions()->delete();

        foreach ($allPermissions as $key => $label) {
            $granted = $request->boolean("perm_{$key}");
            \Modules\Core\Models\GroupPermission::create([
                'group_id' => $group->id,
                'permission_key' => $key,
                'granted' => $granted,
            ]);
        }
    }
}
