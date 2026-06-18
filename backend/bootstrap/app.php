<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*')) {
                $message = 'Terlalu banyak permintaan. Silakan coba lagi nanti.';
                $code = 429;

                if ($request->is('api/v1/auth/login') || $request->is('api/auth/login')) {
                    $message = 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.';
                    $code = 429;
                } elseif ($request->is('*resend-verification*')) {
                    $message = 'Batas pengiriman ulang email verifikasi telah tercapai. Silakan coba lagi nanti.';
                }

                return response()->json([
                    'status' => 'error',
                    'data' => null,
                    'message' => $message,
                    'errors' => null,
                ], $code);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'data' => null,
                    'message' => 'Unauthenticated',
                    'errors' => null,
                ], 401);
            }
        });
    })->create();
