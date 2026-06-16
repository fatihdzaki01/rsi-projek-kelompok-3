<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\DB; //sementara
use App\Models\FollowKomunitas;
use Illuminate\Http\Request;

class CommunityFollowController extends Controller
{
    public function follow(Request $request, $communityId)
    {
        $user = $request->user();
        $userId = $user->id_user ?? $user->id;

        if (($user->role ?? null) !== User::ROLE_DONATUR) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Hanya User yang dapat mengikuti komunitas',
                'errors' => [
                    'code' => 'ERR-FOLLOW-01'
                ]
            ], 403);
        }

        $community = DB::table('komunitas')
            ->where('id_komunitas', $communityId)
            ->first();

        if (!$community) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Komunitas tidak ditemukan',
                'errors' => [
                    'code' => 'ERR-FOLLOW-02'
                ]
            ], 404);
        }

        $existingFollow = FollowKomunitas::where('id_user', $userId)
            ->where('id_komunitas', $communityId)
            ->first();

        if ($existingFollow && $existingFollow->is_active) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'User sudah mengikuti komunitas ini',
                'errors' => [
                    'code' => 'ERR-FOLLOW-03'
                ]
            ], 409);
        }

        if ($existingFollow) {
            $existingFollow->update([
                'is_active' => true,
                'followed_at' => now(),
                'unfollowed_at' => null,
            ]);

            $follow = $existingFollow;
        } else {
            $follow = FollowKomunitas::create([
                'id_user' => $userId,
                'id_komunitas' => $communityId,
                'is_active' => true,
                'followed_at' => now(),
                'unfollowed_at' => null,
            ]);
        }

        // Notifikasi ke komunitas
        $donorName = $user->nama_lengkap ?? $user->username ?? 'Seseorang';
        Notifikasi::kirim([
            'id_penerima_komunitas' => $communityId,
            'id_pengirim_user' => $userId,
            'judul' => 'Pengikut baru',
            'pesan' => $donorName . ' mulai mengikuti komunitas Anda.',
            'tipe' => 'follow_baru',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id_follow' => $follow->id_follow,
                'id_user' => $follow->id_user,
                'id_komunitas' => $follow->id_komunitas,
                'is_active' => $follow->is_active,
            ],
            'message' => 'Berhasil mengikuti komunitas',
            'errors' => null
        ], 200);
    }

    public function unfollow(Request $request, $communityId)
    {
        $user = $request->user();
        $userId = $user->id_user ?? $user->id;

        if (($user->role ?? null) !== User::ROLE_DONATUR) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Hanya User yang dapat berhenti mengikuti komunitas',
                'errors' => [
                    'code' => 'ERR-UNFOLLOW-01'
                ]
            ], 403);
        }

        $community = Komunitas::where('id_komunitas', $communityId)->first();

        if (!$community) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Komunitas tidak ditemukan',
                'errors' => [
                    'code' => 'ERR-UNFOLLOW-02'
                ]
            ], 404);
        }

        $follow = FollowKomunitas::where('id_user', $userId)
            ->where('id_komunitas', $communityId)
            ->where('is_active', true)
            ->first();

        if (!$follow) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'User belum mengikuti komunitas ini',
                'errors' => [
                    'code' => 'ERR-UNFOLLOW-03'
                ]
            ], 409);
        }

        $follow->update([
            'is_active' => false,
            'unfollowed_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => null,
            'message' => 'Berhasil berhenti mengikuti komunitas',
            'errors' => null
        ], 200);
    }

    public function followers(Request $request, $communityId)
    {
        $community = DB::table('komunitas')
            ->where('id_komunitas', $communityId)
            ->first();

        if (!$community) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Komunitas tidak ditemukan',
                'errors' => ['code' => 'ERR-FOLLOW-02']
            ], 404);
        }

        $followers = DB::table('follow_komunitas as fk')
            ->join('users as u', 'u.id_user', '=', 'fk.id_user')
            ->where('fk.id_komunitas', $communityId)
            ->where('fk.is_active', true)
            ->select(
                'u.id_user',
                'u.username',
                'u.nama_lengkap',
                'u.foto_profil_url',
                'fk.followed_at'
            )
            ->orderByDesc('fk.followed_at')
            ->limit(25)
            ->get();

        return ApiResponse::success([
            'total_followers' => DB::table('follow_komunitas')
                ->where('id_komunitas', $communityId)
                ->where('is_active', true)
                ->count(),
            'followers' => $followers,
        ], 'Daftar pengikut berhasil dimuat');
    }
}