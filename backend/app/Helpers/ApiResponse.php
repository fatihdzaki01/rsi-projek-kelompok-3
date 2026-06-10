<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data, string $message, int $code = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'data'    => $data,
            'message' => $message,
            'errors'  => null,
        ], $code);
    }

    public static function error(string $message, int $code, ?string $errorCode = null, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'status'     => 'error',
            'data'       => null,
            'message'    => $message,
            'error_code' => $errorCode,
            'errors'     => $errors,
        ], $code);
    }
}