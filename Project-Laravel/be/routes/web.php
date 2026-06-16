<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-email', function (Request $request) {
    $email = $request->query('email');
    $token = $request->query('token');

    if (!$email || !$token) {
        return response()->json(['message' => 'Parameter tidak lengkap'], 400);
    }

    $verification = DB::table('email_verifications')
        ->where('email', $email)
        ->where('token', $token)
        ->where('expires_at', '>', now())
        ->first();

    if (!$verification) {
        return response()->json(['message' => 'Link verifikasi tidak valid atau sudah kedaluwarsa'], 410);
    }

    $user = User::where('email', $email)->first();
    if (!$user) {
        return response()->json(['message' => 'User tidak ditemukan'], 404);
    }

    $user->update(['is_verified' => true]);

    DB::table('email_verifications')->where('email', $email)->delete();

    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
    return redirect($frontendUrl . '/login?verified=1');
});
