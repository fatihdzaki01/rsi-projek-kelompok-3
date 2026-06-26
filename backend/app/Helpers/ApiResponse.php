<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success($data = null, string $message = 'Success', int $statusCode = 200)
    {
        if ($data instanceof LengthAwarePaginator) {
            $data = [
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
                'links' => [
                    'first' => $data->url(1),
                    'last' => $data->url($data->lastPage()),
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl(),
                ],
            ];
        }

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