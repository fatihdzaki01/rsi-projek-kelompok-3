<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'status'  => 'ok',
        'message' => 'Laravel API v1 berjalan!',
    ]);
});