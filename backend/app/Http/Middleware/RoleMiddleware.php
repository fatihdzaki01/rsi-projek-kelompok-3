<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Sesi tidak valid. Silakan login kembali.',
                'errors' => ['auth' => ['Unauthenticated']],
            ], 401);
        }

        $allowedRoles = array_map('strtoupper', $roles);
        $userRole = strtoupper($user->role);

        if (!in_array($userRole, $allowedRoles, true)) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Akses ditolak. Role tidak memiliki izin.',
                'errors' => ['role' => ['Forbidden']],
            ], 403);
        }

        return $next($request);
    }
}