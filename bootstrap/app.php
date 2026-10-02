<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter; // <-- Importante
use Illuminate\Cache\RateLimiting\Limit;      // <-- Importante
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Exceptions\UnauthorizedException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Registramos el rate limiter 'auth' para el login
            RateLimiter::for('auth', function (Request $request) {
                return Limit::perMinute(5)->by($request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
                if ($request->is('api/*') || $request->expectsJson()) {
                    if ($e instanceof UnauthorizedException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Acción no autorizada. No posee el rol o permiso requerido.',
                    ], 403);
                }

                if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                    return response()->json(['success' => false, 'message' => 'Recurso no encontrado.'], 404);
                }
                if ($e instanceof MethodNotAllowedHttpException) {
                    return response()->json(['success' => false, 'message' => 'Método HTTP no permitido.'], 405);
                }
                if ($e instanceof AuthenticationException) {
                    return response()->json(['success' => false, 'message' => 'No autorizado.'], 401);
                }
                if ($e instanceof AccessDeniedHttpException) {
                    return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
                }

                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = config('app.debug') ? $e->getMessage() : 'Error interno del servidor.';

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    ...(config('app.debug') ? ['trace' => $e->getTrace()] : [])
                ], $status);
            }
        });
    })->create();