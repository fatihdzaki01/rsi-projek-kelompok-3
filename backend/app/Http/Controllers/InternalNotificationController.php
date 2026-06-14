<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InternalNotificationController extends Controller
{
    public function store(Request $request)
    {
        $internalToken = $request->header('X-Internal-Token');

        if (!$internalToken || $internalToken !== config('services.internal_api_token')) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Internal token tidak valid',
                'errors' => [
                    'code' => 'ERR-INTERNAL-01'
                ]
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'event_type' => ['required', 'string', 'in:campaign_baru,post_update_baru,campaign_hampir_selesai'],
            'id_komunitas' => ['required', 'integer'],
            'id_campaign' => ['nullable', 'integer'],
            'id_post_update' => ['nullable', 'integer'],
            'judul' => ['nullable', 'string', 'max:255'],
            'isi' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Jenis event notifikasi tidak valid',
                'errors' => [
                    'code' => 'ERR-NOTIF-04',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $community = DB::table('komunitas')
            ->where('id_komunitas', $request->id_komunitas)
            ->first();

        if (!$community) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Komunitas tidak ditemukan',
                'errors' => [
                    'code' => 'ERR-NOTIF-05'
                ]
            ], 404);
        }

        $followers = DB::table('follow_komunitas')
            ->where('id_komunitas', $request->id_komunitas)
            ->where('is_active', true)
            ->pluck('id_user');

        if ($followers->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'event_type' => $request->event_type,
                    'total_receiver' => 0,
                    'total_created' => 0,
                ],
                'message' => 'Tidak ada user penerima notifikasi',
                'errors' => null
            ], 200);
        }

        $payload = $this->buildNotificationContent($request);

        $created = 0;

        foreach ($followers as $userId) {
            Notifikasi::create([
                'id_user' => $userId,
                'judul' => $payload['judul'],
                'isi' => $payload['isi'],
                'jenis' => $request->event_type,
                'is_read' => false,
                'read_at' => null,
            ]);

            $created++;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'event_type' => $request->event_type,
                'total_receiver' => $followers->count(),
                'total_created' => $created,
            ],
            'message' => 'Notifikasi user berhasil dibuat',
            'errors' => null
        ], 201);
    }

    private function buildNotificationContent(Request $request): array
    {
        if ($request->event_type === 'campaign_baru') {
            return [
                'judul' => $request->judul ?? 'Campaign baru dari komunitas yang kamu ikuti',
                'isi' => $request->isi ?? 'Ada campaign baru dari komunitas yang kamu ikuti.',
            ];
        }

        if ($request->event_type === 'post_update_baru') {
            return [
                'judul' => $request->judul ?? 'Update post baru',
                'isi' => $request->isi ?? 'Ada update terbaru dari campaign komunitas yang kamu ikuti.',
            ];
        }

        return [
            'judul' => $request->judul ?? 'Campaign hampir selesai',
            'isi' => $request->isi ?? 'Campaign dari komunitas yang kamu ikuti hampir selesai.',
        ];
    }
}