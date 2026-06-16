<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

/**
 * Trait helper untuk membuat format response konsisten
 * sesuai TSD: { status: 'success'|'error', data?, error_code?, message? }
 */
trait ApiResponse
{
    protected function success($data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $data,
        ], $code);
    }

    protected function error(string $errorCode, string $message, int $httpStatus = 400): JsonResponse
    {
        return response()->json([
            'status'     => 'error',
            'error_code' => $errorCode,
            'message'    => $message,
        ], $httpStatus);
    }

    /** Bungkus paginator Laravel menjadi { data, meta } sesuai TSD. */
    protected function paginated($paginator, $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data'   => $data ?? $paginator->items(),
            'meta'   => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }
}
