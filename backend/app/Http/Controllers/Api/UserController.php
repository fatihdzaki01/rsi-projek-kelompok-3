<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\FollowKomunitas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function me()
    {
        $user = auth()->user();

        if ($user->role !== 'DONATUR') {
            return ApiResponse::error('Akses profil ditolak', null, 403);
        }

        return ApiResponse::success([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
            'is_verified' => $user->is_verified,
            'foto_profil_url' => $user->foto_profil_url,
            'nama_lengkap' => $user->nama_lengkap,
            'nomor_telepon' => $user->nomor_telepon,
            'jenis_kelamin' => $user->jenis_kelamin,
            'tanggal_lahir' => $user->tanggal_lahir,
            'kode_wilayah' => $user->kode_wilayah,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], 'Profil berhasil ditampilkan');
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $fotoProfilUrl = $user->foto_profil_url;

        if ($request->hasFile('foto_profil')) {
            $path = $request->file('foto_profil')->store('profile-photos', 'public');
            $fotoProfilUrl = '/storage/' . $path;
        }

        $user->update([
            'foto_profil_url' => $fotoProfilUrl,
            'nama_lengkap' => $request->nama_lengkap,
            'nomor_telepon' => $request->nomor_telepon,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir,
            'kode_wilayah' => $request->kode_wilayah,
        ]);

        return ApiResponse::success([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'email' => $user->email,
            'foto_profil_url' => $user->foto_profil_url,
            'nama_lengkap' => $user->nama_lengkap,
            'nomor_telepon' => $user->nomor_telepon,
            'jenis_kelamin' => $user->jenis_kelamin,
            'tanggal_lahir' => $user->tanggal_lahir,
            'kode_wilayah' => $user->kode_wilayah,
        ], 'Profil berhasil diperbarui');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password_hash)) {
            return ApiResponse::error('Password lama salah', null, 401);
        }

        if (Hash::check($request->password_baru, $user->password_hash)) {
            return ApiResponse::error('Password baru tidak boleh sama dengan password lama', null, 400);
        }

        $user->update([
            'password_hash' => Hash::make($request->password_baru),
        ]);

        $currentTokenId = $user->currentAccessToken()?->id;

        $user->tokens()
            ->where('id', '!=', $currentTokenId)
            ->delete();

        return ApiResponse::success(null, 'Password berhasil diperbarui');
    }

    public function donations()
    {
        $user = auth()->user();

        if ($user->role !== 'DONATUR') {
            return ApiResponse::error('Akses riwayat donasi ditolak', null, 403);
        }

        $donations = DB::table('v_user_donation_history')
            ->where('id_user', $user->id_user)
            ->orderByDesc('created_at')
            ->get();

        if ($donations->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'data' => [],
                'message' => 'Belum ada riwayat donasi',
                'errors' => null,
            ], 404);
        }

        return ApiResponse::success($donations, 'Riwayat donasi berhasil ditampilkan');
    }

    public function following()
    {
        $user = auth()->user();

        $following = FollowKomunitas::where('id_user', $user->id_user)
            ->where('is_active', true)
            ->with('komunitas:id_komunitas,nama_lembaga,foto_lembaga_url')
            ->orderByDesc('followed_at')
            ->get()
            ->map(fn ($f) => [
                'id_follow'      => $f->id_follow,
                'id_komunitas'   => $f->komunitas->id_komunitas,
                'nama_lembaga'   => $f->komunitas->nama_lembaga,
                'foto_lembaga_url' => $f->komunitas->foto_lembaga_url,
                'followed_at'    => $f->followed_at,
            ]);

        return ApiResponse::success($following, 'Daftar komunitas yang diikuti berhasil dimuat');
    }
}