<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\RegisterKomunitasRequest;
use App\Models\User;
use App\Models\Komunitas;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Http\Requests\ResendVerificationRequest;
use App\Notifications\VerifyEmail;


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
            'is_verified' => app()->environment('local'),
        ]);

        if (!app()->environment('local')) {
        $verificationToken = Str::random(64);
        DB::table('email_verifications')->insert([
            'id_user'    => $user->id_user,
            'email'      => $user->email,
            'token'      => $verificationToken,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user->notify(new VerifyEmail($verificationToken, $user->email));
        }

        Notifikasi::kirim([
            'id_penerima_user' => $user->id_user,
            'judul' => 'Selamat datang di Berbagive!',
            'pesan' => 'Hai ' . ($request->username ?? $request->nama_lengkap ?? '') . ', selamat datang di Berbagive. Mulai donasi atau ikuti komunitas favoritmu!',
            'tipe' => 'welcome',
        ]);

        // Notifikasi ke superadmin
        $superadminIds = User::where('role', User::ROLE_SUPERADMIN)->pluck('id_user');
        foreach ($superadminIds as $saId) {
            Notifikasi::kirim([
                'id_penerima_user' => $saId,
                'judul' => 'Donatur baru mendaftar',
                'pesan' => 'Donatur baru: ' . ($request->username ?? '-') . ' (' . $request->email . ') telah mendaftar.',
                'tipe' => 'user_baru',
            ]);
        }

        return ApiResponse::success([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'is_verified' => $user->is_verified,
        ], app()->environment('local') ? 'Registrasi berhasil.' : 'Registrasi berhasil. Silakan verifikasi email.', 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return ApiResponse::error('Email atau password salah', null, 401);
        }

        if ($user->role === User::ROLE_KOMUNITAS) {
            $komunitas = $user->komunitas;
            if (!$komunitas) {
                return ApiResponse::error('Data komunitas tidak ditemukan', null, 403);
            }
            if ($komunitas->status === Komunitas::STATUS_MENUNGGU) {
                return ApiResponse::error('Akun sedang dalam proses review', null, 403);
            }
            if ($komunitas->status === Komunitas::STATUS_DITOLAK) {
                return ApiResponse::error('Pendaftaran komunitas ditolak', null, 403);
            }
            if ($komunitas->status === Komunitas::STATUS_DINONAKTIFKAN) {
                return ApiResponse::error('Akun tidak aktif', null, 403);
            }
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

        DB::table('email_verifications')
            ->where('email', $user->email)
            ->delete();

        $verificationToken = Str::random(64);

        DB::table('email_verifications')->insert([
            'id_user'    => $user->id_user,
            'email'      => $user->email,
            'token'      => $verificationToken,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user->notify(new VerifyEmail($verificationToken, $user->email));

        return ApiResponse::success(null, 'Link verifikasi dikirim jika email terdaftar');
    }

    public function registerKomunitas(RegisterKomunitasRequest $request)
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'username'      => $request->nama_lembaga,
                'email'         => $request->email,
                'password_hash' => Hash::make($request->password),
                'role'          => User::ROLE_KOMUNITAS,
                'is_active'     => true,
                'is_verified'   => false,
            ]);

            Komunitas::create([
                'id_user'          => $user->id_user,
                'id_jenis_lembaga' => $request->id_jenis_lembaga,
                'nama_lembaga'     => $request->nama_lembaga,
                'deskripsi'        => $request->deskripsi,
                'kode_wilayah'     => $request->kode_wilayah,
                'alamat_detail'    => $request->alamat_detail,
                'nomor_kontak'     => $request->nomor_kontak,
                'link_medsos'      => $request->link_medsos,
                'foto_lembaga_url' => $request->foto_lembaga_url,
                'status'           => Komunitas::STATUS_MENUNGGU,
            ]);

            return $user;
        });

        $komunitas = Komunitas::where('id_user', $user->id_user)->first();

        // Notifikasi ke komunitas
        if ($komunitas) {
            Notifikasi::kirim([
                'id_penerima_komunitas' => $komunitas->id_komunitas,
                'judul' => 'Registrasi komunitas berhasil',
                'pesan' => 'Komunitas "' . $request->nama_lembaga . '" berhasil didaftarkan. Menunggu verifikasi superadmin.',
                'tipe' => 'verifikasi',
            ]);
        }

        // Notifikasi ke semua superadmin
        $superadminIds = User::where('role', User::ROLE_SUPERADMIN)->pluck('id_user');
        foreach ($superadminIds as $saId) {
            Notifikasi::kirim([
                'id_penerima_user' => $saId,
                'judul' => 'Komunitas baru mendaftar',
                'pesan' => 'Komunitas "' . $request->nama_lembaga . '" (' . $request->email . ') menunggu verifikasi.',
                'tipe' => 'komunitas_baru',
            ]);
        }

        return ApiResponse::success([
            'id_user'     => $user->id_user,
            'email'       => $user->email,
            'nama_lembaga' => $request->nama_lembaga,
            'role'        => $user->role,
        ], 'Registrasi komunitas berhasil. Silakan tunggu verifikasi superadmin.', 201);
    }

    public function logout()
    {
        $user = auth()->user();

        $user->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logout berhasil');
    }
}