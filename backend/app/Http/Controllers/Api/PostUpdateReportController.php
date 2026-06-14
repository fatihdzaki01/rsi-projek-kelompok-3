<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignUpdate;
use App\Models\PostReport;
use Illuminate\Http\Request;

class PostUpdateReportController extends Controller
{
    public function store(Request $request, $updateId)
    {
        $request->validate([
            'alasan_laporan' => ['required', 'string', 'max:255'],
        ], [
            'alasan_laporan.required' => 'Alasan laporan wajib dipilih',
        ]);

        $update = CampaignUpdate::where('id_update', $updateId)->first();

        if (!$update) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Post update tidak ditemukan',
                'errors' => [
                    'code' => 'ERR-MON-14'
                ]
            ], 404);
        }

        $user = $request->user();

        /*
         * Karena endpoint ini Public/Sanctum, guest boleh report.
         * Untuk guest, pakai IP address sebagai identitas sementara.
         */
        $existingReport = PostReport::query()
            ->where('id_update', $updateId)
            ->when($user, function ($query) use ($user) {
                $query->where('id_user', $user->id_user ?? $user->id);
            })
            ->when(!$user, function ($query) use ($request) {
                $query->where('guest_ip', $request->ip());
            })
            ->first();

        if ($existingReport) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Anda sudah melaporkan post ini',
                'errors' => [
                    'code' => 'ERR-MON-11'
                ]
            ], 409);
        }

        $report = PostReport::create([
            'id_update' => $updateId,
            'id_user' => $user ? ($user->id_user ?? $user->id) : null,
            'guest_ip' => $user ? null : $request->ip(),
            'alasan_laporan' => $request->alasan_laporan,
            'status' => 'menunggu_review',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id_laporan' => $report->id_laporan,
                'id_update' => $report->id_update,
                'alasan_laporan' => $report->alasan_laporan,
                'status' => $report->status,
            ],
            'message' => 'Laporan berhasil dikirim',
            'errors' => null
        ], 200);
    }
}