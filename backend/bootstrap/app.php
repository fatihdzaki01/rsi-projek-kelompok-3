<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
         $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
        return \App\Helpers\ApiResponse::error('User belum login', 401, 'ERR-AUTH-01');
    });

    $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
        return \App\Helpers\ApiResponse::error('Akses ditolak', 403, 'ERR-AUTH-02');
    });

    $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
        return \App\Helpers\ApiResponse::error(
            'Validasi gagal',
            422,
            'ERR-VALIDATION',
            $e->errors()
        );
    });

    $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
        return \App\Helpers\ApiResponse::error('Resource tidak ditemukan', 404, 'ERR-NOT-FOUND');
    });
})->create();