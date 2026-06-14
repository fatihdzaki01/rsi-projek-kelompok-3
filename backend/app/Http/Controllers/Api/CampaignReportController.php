<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CampaignReportController extends Controller
{
    public function store(Request $request, $id)
    {
        $user = $request->user();
        $userId = $user->id_user ?? $user->id;

        if (($user->role ?? null) !== 'user') {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Hanya User yang dapat melaporkan campaign',
                'errors' => [
                    'code' => 'ERR-CAMPAIGN-REPORT-01'
                ]
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'alasan_laporan' => ['required', 'string', 'max:255'],
            'deskripsi_laporan' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Alasan laporan wajib diisi',
                'errors' => [
                    'code' => 'ERR-CAMPAIGN-REPORT-02',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $campaign = DB::table('campaign')
            ->where('id_campaign', $id)
            ->first();

        if (!$campaign) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Campaign tidak ditemukan',
                'errors' => [
                    'code' => 'ERR-CAMPAIGN-REPORT-03'
                ]
            ], 404);
        }

        if (!in_array($campaign->status, ['aktif', 'selesai'])) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Campaign tidak dapat dilaporkan',
                'errors' => [
                    'code' => 'ERR-CAMPAIGN-REPORT-04'
                ]
            ], 403);
        }

        $existingReport = DB::table('laporan_campaign')
            ->where('id_campaign', $id)
            ->where('id_user', $userId)
            ->first();

        if ($existingReport) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Anda sudah melaporkan campaign ini',
                'errors' => [
                    'code' => 'ERR-CAMPAIGN-REPORT-05'
                ]
            ], 409);
        }

        $reportId = DB::table('laporan_campaign')->insertGetId([
            'id_campaign' => $id,
            'id_user' => $userId,
            'alasan_laporan' => $request->alasan_laporan,
            'deskripsi_laporan' => $request->deskripsi_laporan,
            'status' => 'menunggu_review',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_laporan');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id_laporan' => $reportId,
                'id_campaign' => (int) $id,
                'id_user' => $userId,
                'alasan_laporan' => $request->alasan_laporan,
                'status' => 'menunggu_review',
            ],
            'message' => 'Laporan campaign berhasil dikirim',
            'errors' => null
        ], 201);
    }
}