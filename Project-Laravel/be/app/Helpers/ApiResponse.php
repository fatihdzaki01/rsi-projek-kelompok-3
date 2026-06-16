<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, string $message = 'Success', int $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ], $statusCode);
    }

    public static function error(string $message = 'Error', $errors = null, int $statusCode = 400)
    {
        return response()->json([
            'status' => 'error',
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}