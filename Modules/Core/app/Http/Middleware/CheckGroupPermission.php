<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Exige que o usuario logado tenha ao menos uma das permissoes informadas.
 *
 * Uso: ->middleware('group.permission:invoices') ou
 *      ->middleware('group.permission:invoices,reports') (basta uma delas).
 * O superadmin (Modules\Core\Models\UserGroup slug 'superadmin') passa sempre,
 * atraves de App\Models\User::hasPermission().
 */
class CheckGroupPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $user = Auth::user();

        $granted = collect(explode(',', implode(',', $permissions)))
            ->map(fn ($permission) => trim($permission))
            ->filter()
            ->contains(fn (string $permission) => $user && $user->hasPermission($permission));

        if (! $granted) {
            if ($request->expectsJson()) {
                abort(403, 'Acesso negado.');
            }

            abort(403, 'Acesso negado. Voce nao tem permissao para esta area.');
        }

        return $next($request);
    }
}
