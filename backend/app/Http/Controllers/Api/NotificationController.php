<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['nullable', 'in:read,unread'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Parameter notifikasi tidak valid',
                'errors' => [
                    'code' => 'ERR-NOTIF-04',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $user = $request->user();
        $userId = $user->id_user ?? $user->id;
        $perPage = $request->input('per_page', 10);

        $query = Notifikasi::query();

        if ($user->role === 'KOMUNITAS') {
            $komunitasId = $user->komunitas->id_komunitas ?? null;
            $query->where('id_penerima_komunitas', $komunitasId);
        } else {
            $query->where('id_penerima_user', $userId);
        }

        if ($request->status === 'read') {
            $query->where('is_read', true);
        }

        if ($request->status === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $unreadQuery = Notifikasi::query();
        if ($user->role === 'KOMUNITAS') {
            $komunitasId = $user->komunitas->id_komunitas ?? null;
            $unreadQuery->where('id_penerima_komunitas', $komunitasId);
        } else {
            $unreadQuery->where('id_penerima_user', $userId);
        }
        $unreadCount = $unreadQuery->where('is_read', false)->count();

        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'items' => [],
                    'unread_count' => 0,
                    'pagination' => [
                        'current_page' => $notifications->currentPage(),
                        'per_page' => $notifications->perPage(),
                        'total' => $notifications->total(),
                        'last_page' => $notifications->lastPage(),
                    ],
                ],
                'message' => 'Tidak ada notifikasi',
                'errors' => null
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $notifications->items(),
                'unread_count' => $unreadCount,
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                    'last_page' => $notifications->lastPage(),
                ],
            ],
            'message' => 'Notifikasi berhasil ditampilkan',
            'errors' => null
        ], 200);
    }

    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->user();
        $userId = $user->id_user ?? $user->id;

        $notification = Notifikasi::where('id_notif', $notificationId)->first();

        if (!$notification) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Notifikasi tidak ditemukan',
                'errors' => [
                    'code' => 'ERR-NOTIF-03'
                ]
            ], 404);
        }

        $hasAccess = false;
        if ($user->role === 'KOMUNITAS') {
            $komunitasId = $user->komunitas->id_komunitas ?? null;
            $hasAccess = (int) $notification->id_penerima_komunitas === (int) $komunitasId;
        } else {
            $hasAccess = (int) $notification->id_penerima_user === (int) $userId;
        }

        if (!$hasAccess) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Anda tidak memiliki akses ke notifikasi ini',
                'errors' => [
                    'code' => 'ERR-NOTIF-02'
                ]
            ], 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id_notif' => $notification->id_notif,
                'is_read' => true,
                'read_at' => $notification->read_at,
            ],
            'message' => 'Notifikasi berhasil ditandai sudah dibaca',
            'errors' => null
        ], 200);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $userId = $user->id_user ?? $user->id;

        $markQuery = Notifikasi::query()->where('is_read', false);
        if ($user->role === 'KOMUNITAS') {
            $komunitasId = $user->komunitas->id_komunitas ?? null;
            $markQuery->where('id_penerima_komunitas', $komunitasId);
        } else {
            $markQuery->where('id_penerima_user', $userId);
        }
        $updatedCount = $markQuery->update([
            'is_read' => true,
            'read_at' => now(),
            'updated_at' => now(),
        ]);

        if ($updatedCount === 0) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'updated_count' => 0,
                ],
                'message' => 'Tidak ada notifikasi belum dibaca',
                'errors' => null
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'updated_count' => $updatedCount,
            ],
            'message' => 'Semua notifikasi berhasil ditandai sudah dibaca',
            'errors' => null
        ], 200);
    }
}