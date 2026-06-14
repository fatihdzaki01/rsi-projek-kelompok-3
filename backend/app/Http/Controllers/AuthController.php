<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Requests\ResendVerificationRequest;


class AuthController extends Controller
{
    public function registerUser(RegisterUserRequest $request)
    {
        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            return ApiResponse::error('Email sudah digunakan', null, 409);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role' => 'DONATUR',
            'is_active' => true,
            'is_verified' => false,
        ]);

        return ApiResponse::success([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
        ], 'Registrasi berhasil. Silakan verifikasi email.', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return ApiResponse::error('Email atau password salah', null, 401);
        }

        if (!$user->is_verified) {
            return ApiResponse::error('Akun belum terverifikasi', null, 403);
        }

        if (!$user->is_active) {
            return ApiResponse::error('Akun tidak aktif', null, 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'user' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Login berhasil');
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $plainToken = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($plainToken),
                    'created_at' => now(),
                ]
            );

            $resetUrl = env('FRONTEND_URL', 'http://localhost:5173')
                . '/reset-password?email=' . urlencode($request->email)
                . '&token=' . urlencode($plainToken);

            return ApiResponse::success([
                'reset_url' => $resetUrl,
            ], 'Link dikirim jika email terdaftar');
        }

        return ApiResponse::success(null, 'Link dikirim jika email terdaftar');
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $resetData = DB::table('password_reset_tokens')
            ->where('email', $request->email ?? '')
            ->first();

        if (!$resetData) {
            return ApiResponse::error('Link reset password tidak valid atau sudah digunakan', null, 400);
        }

        if (Carbon::parse($resetData->created_at)->addMinutes(30)->isPast()) {
            return ApiResponse::error('Link reset password telah kedaluwarsa', null, 410);
        }

        if (!Hash::check($request->token, $resetData->token)) {
            return ApiResponse::error('Link reset password tidak valid atau sudah digunakan', null, 400);
        }

        $user = User::where('email', $resetData->email)->first();

        if (!$user) {
            return ApiResponse::error('Link reset password tidak valid atau sudah digunakan', null, 400);
        }

        if (Hash::check($request->password_baru, $user->password_hash)) {
            return ApiResponse::error('Password baru tidak boleh sama dengan password lama', [
                'password_baru' => ['Password baru tidak boleh sama dengan password lama'],
            ], 422);
        }

        $user->update([
            'password_hash' => Hash::make($request->password_baru),
        ]);

        $user->tokens()->delete();

        DB::table('password_reset_tokens')
            ->where('email', $resetData->email)
            ->delete();

        return ApiResponse::success(null, 'Password berhasil diperbarui');
    }

    public function resendVerification(ResendVerificationRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return ApiResponse::success(null, 'Link verifikasi dikirim jika email terdaftar');
        }

        if ($user->is_verified) {
            return ApiResponse::error('Email sudah terverifikasi', null, 409);
        }

        $verificationToken = Str::random(64);

        $verificationUrl = env('FRONTEND_URL', 'http://localhost:5173')
            . '/verify-email?email=' . urlencode($user->email)
            . '&token=' . urlencode($verificationToken);

        return ApiResponse::success([
            'verification_url' => $verificationUrl,
        ], 'Link verifikasi dikirim jika email terdaftar');
    }

    public function logout()
    {
        $user = auth()->user();

        $user->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logout berhasil');
    }
}