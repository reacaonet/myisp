<?php

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Bloqueia o uso do sistema enquanto o usuario nao definir a senha
     * gerada no convite da franquia.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('password.edit', 'password.update', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Defina sua senha antes de continuar.',
                'redirect' => route('password.edit'),
            ], 403);
        }

        return redirect()->route('password.edit');
    }
}
