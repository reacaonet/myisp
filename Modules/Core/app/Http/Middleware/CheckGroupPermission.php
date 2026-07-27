<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckGroupPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::user();

        if (!$user || !$user->hasPermission($permission)) {
            abort(403, 'Acesso negado.');
        }

        return $next($request);
    }
}
