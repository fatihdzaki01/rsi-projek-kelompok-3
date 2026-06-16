<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::error('User belum login', 401, 'ERR-AUTH-01');
        }

        if (!in_array($user->role, $roles)) {
            return ApiResponse::error('Akses ditolak', 403, 'ERR-AUTH-02');
        }

        return $next($request);
    }
}