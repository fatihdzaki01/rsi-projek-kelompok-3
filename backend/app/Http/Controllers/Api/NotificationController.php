<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    private function success($data, string $message = 'Berhasil')
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ]);
    }

    private function error(string $message, int $code = 400, $errors = null)
    {
        return response()->json([
            'status' => 'error',
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    public function index(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 20), 100);
        $user = $request->user();

        $query = DB::table('notifikasi')
            ->where(function ($q) use ($user) {
                $q->where('id_penerima_user', $user->id_user)
                  ->orWhereNull('id_penerima_user');
            })
            ->where('is_archived', false)
            ->orderByDesc('created_at');

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->query('tipe'));
        }

        if ($request->boolean('unread_only', false)) {
            $query->where('is_read', false);
        }

        $data = $query->paginate($perPage);

        $unreadCount = DB::table('notifikasi')
            ->where('id_penerima_user', $user->id_user)
            ->where('is_read', false)
            ->where('is_archived', false)
            ->count();

        return $this->success([
            'notifications' => $data,
            'unread_count' => $unreadCount,
        ], 'Notifikasi berhasil dimuat.');
    }

    public function markRead(int $id)
    {
        $notif = DB::table('notifikasi')->where('id_notif', $id)->first();

        if (!$notif) {
            return $this->error('Notifikasi tidak ditemukan.', 404);
        }

        DB::table('notifikasi')
            ->where('id_notif', $id)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->success(null, 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllRead(Request $request)
    {
        $user = $request->user();

        DB::table('notifikasi')
            ->where('id_penerima_user', $user->id_user)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->success(null, 'Semua notifikasi ditandai sudah dibaca.');
    }
}
