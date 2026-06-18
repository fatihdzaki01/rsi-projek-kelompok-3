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
use App\Models\DokumenKomunitas;
use App\Traits\HasImageUpload;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ResendVerificationRequest;
use App\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use HasImageUpload;

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
            'is_verified' => true,
        ]);

        return ApiResponse::success([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'is_verified' => $user->is_verified,
        ], 'Registrasi berhasil.', 201);
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Login user',
        tags: ['Auth'],
        responses: [new OA\Response(response: 200, description: 'Login berhasil')]
    )]
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
        $authUser = $request->user();

        if ($authUser->role !== User::ROLE_DONATUR) {
            return ApiResponse::error('Hanya akun Donatur yang dapat mendaftar sebagai Komunitas', null, 403);
        }

        if ($authUser->komunitas) {
            return ApiResponse::error('Anda sudah terdaftar sebagai Komunitas', null, 409);
        }

        DB::transaction(function () use ($request, $authUser) {
            $komunitas = Komunitas::create([
                'id_user'          => $authUser->id_user,
                'id_jenis_lembaga' => $request->id_jenis_lembaga,
                'nama_lembaga'     => $request->nama_lembaga,
                'deskripsi'        => $request->deskripsi,
                'kode_wilayah'     => $request->kode_wilayah,
                'alamat_detail'    => $request->alamat_detail,
                'nomor_kontak'     => $request->nomor_kontak,
                'link_medsos'      => $request->link_medsos,
                'foto_lembaga_url' => $request->foto_lembaga_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($request->nama_lembaga) . '&background=1a2744&color=fff&size=256',
                'nama_bank'        => '-',
                'nomor_rekening'   => '-',
                'foto_buku_rekening_url' => '-',
                'status'           => Komunitas::STATUS_AKTIF,
                'direview_oleh'    => User::where('role', User::ROLE_SUPERADMIN)->first()->id_user,
            ]);

            if ($request->nama_pic) {
                $authUser->update(['nama_lengkap' => $request->nama_pic]);
            }

            $authUser->update(['role' => User::ROLE_KOMUNITAS]);

            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $idJenisDok => $file) {
                    $url = $this->uploadDocument($file, 'dokumen-komunitas');

                    DokumenKomunitas::create([
                        'id_komunitas'      => $komunitas->id_komunitas,
                        'id_jenis_dok'      => (int) $idJenisDok,
                        'file_url'          => $url,
                        'status_verifikasi' => DokumenKomunitas::STATUS_MENUNGGU,
                        'uploaded_at'       => now(),
                    ]);
                }
            }
        });

        return ApiResponse::success([
            'id_user'     => $authUser->id_user,
            'email'       => $authUser->email,
            'nama_lembaga' => $request->nama_lembaga,
            'role'        => User::ROLE_KOMUNITAS,
        ], 'Registrasi komunitas berhasil.', 201);
    }

    public function logout()
    {
        $user = auth()->user();

        $user->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logout berhasil');
    }
}