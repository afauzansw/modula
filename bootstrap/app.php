<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

/**
 * One entry per role directory under routes/{role}/ — each *.php file in that
 * directory defines routes for one resource (courses, orders, payments, ...)
 * and is auto-required by the loop below. `prefix`/`name` of null (Student)
 * means the group carries no URL or route-name prefix at all — a deliberate
 * exception, see docs/PROJECT_CONTEXT.md "Route files".
 *
 * @var array<string, array{middleware: list<string>, prefix: ?string, name: ?string}>
 */
$roleRouteGroups = [
    'admin' => [
        'middleware' => ['auth', 'verified'],
        'prefix' => 'admin',
        'name' => 'admin.',
    ],
    'instructor' => [
        'middleware' => ['auth', 'verified', 'role:instructor'],
        'prefix' => 'instructor',
        'name' => 'instructor.',
    ],
    'student' => [
        'middleware' => ['auth', 'verified'],
        'prefix' => null,
        'name' => null,
    ],
];

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () use ($roleRouteGroups): void {
            Route::middleware('web')->group(function () use ($roleRouteGroups): void {
                foreach ($roleRouteGroups as $role => $config) {
                    if ($config['prefix'] !== null) {
                        Route::redirect($config['prefix'], "/{$config['prefix']}/dashboard");
                    }

                    $registrar = Route::middleware($config['middleware']);

                    if ($config['prefix'] !== null) {
                        $registrar = $registrar->prefix($config['prefix']);
                    }

                    if ($config['name'] !== null) {
                        $registrar = $registrar->name($config['name']);
                    }

                    $registrar->group(function () use ($role): void {
                        $files = glob(__DIR__."/../routes/{$role}/*.php") ?: [];
                        sort($files);

                        foreach ($files as $file) {
                            require $file;
                        }
                    });
                }
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
