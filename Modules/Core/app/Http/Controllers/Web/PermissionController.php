<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Core\Models\UserPermission;
use App\Models\User;

class PermissionController extends Controller
{
    public function index()
    {
        $users = User::with('permissions')->orderBy('name')->get();
        return view('core::permissions.index', compact('users'));
    }

    public function edit($userId)
    {
        $user = User::with('permissions')->findOrFail($userId);
        $allPermissions = UserPermission::MENU_PERMISSIONS();

        $userPerms = $user->permissions->pluck('permission_key')->toArray();

        $permissions = [];
        foreach ($allPermissions as $key => $label) {
            $permissions[$key] = [
                'label' => $label,
                'granted' => in_array($key, $userPerms) ? $user->permissions->where('permission_key', $key)->first()->granted : ($user->is_admin ?? false),
            ];
        }

        return view('core::permissions.edit', compact('user', 'permissions'));
    }

    public function update(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $allPermissions = UserPermission::MENU_PERMISSIONS();

        $user->permissions()->delete();

        foreach ($allPermissions as $key => $label) {
            $granted = $request->boolean("perm_{$key}");
            UserPermission::create([
                'user_id' => $user->id,
                'permission_key' => $key,
                'granted' => $granted,
            ]);
        }

        return redirect()->route('core.permissions.index')
            ->with('success', "Permissoes atualizadas para {$user->name}.");
    }
}
