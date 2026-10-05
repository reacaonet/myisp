<?php

namespace Modules\Core\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Modules\Core\Services\TenantContext;

/**
 * "Meu perfil" do usuario web (guard `web`).
 *
 * A tela edita apenas o que o dono da conta controla: nome, email, telefones,
 * cidade/UF e avatar. Grupo, situacao ativa e vinculo de empresa/filial ficam
 * de fora de proposito — sao decididos por quem tem `group.permission:users`, e
 * o perfil nao pode virar um caminho para se promover nem para se reativar.
 */
class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('core::profile.edit', [
            'user' => $user,
            'company' => TenantContext::company(),
            'branch' => TenantContext::branch(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'cellphone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|size:2',
            'avatar' => 'nullable|image|mimes:jpeg,png,webp,gif|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'cellphone' => $validated['cellphone'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => isset($validated['state']) && $validated['state'] !== ''
                ? mb_strtoupper($validated['state'])
                : null,
        ];

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $this->deleteAvatar($user->avatar);
            $data['avatar'] = null;
        }

        $emailChanged = $user->email !== $data['email'];

        $user->update($data);

        // Trocar o email deixa a conta sem verificacao. Como `verified` protege
        // as rotas do painel, zerar aqui traria o usuario de volta para o login
        // sem caminho de reenvio; o admin precisa reativar a conta.
        if ($emailChanged && $user->email_verified_at !== null) {
            $user->forceFill(['email_verified_at' => null])->save();
        }

        return redirect()->route('core.profile.edit')
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    protected function deleteAvatar(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
