<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Modules\Core\Http\Middleware\CheckGroupPermission;
use Modules\Core\Http\Middleware\ForcePasswordChange;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'group.permission' => CheckGroupPermission::class,
            'password.change' => ForcePasswordChange::class,
        ]);

        $middleware->web(append: [
            ForcePasswordChange::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            if (str_starts_with($request->path(), 'tecnico')) {
                return route('technician.portal.login');
            }
            if (str_starts_with($request->path(), 'infra')) {
                return route('infra.login');
            }
            if (str_starts_with($request->path(), 'crm/portal')) {
                return route('crm.portal.login');
            }

            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
