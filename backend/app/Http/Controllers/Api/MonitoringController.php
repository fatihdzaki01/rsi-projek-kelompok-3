<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
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

    public function internalCampaign(Request $request, int $id)
    {
        $summary = DB::table('v_campaign_internal_detail')
            ->where('id_campaign', $id)
            ->first();

        if (!$summary) {
            return $this->error('Campaign tidak ditemukan.', 404);
        }

        $campaign = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->leftJoin('wilayah as w', 'w.kode', '=', 'c.kode_wilayah')
            ->select(
                'c.id_campaign',
                'c.judul',
                'c.deskripsi',
                'c.status',
                'c.target_dana',
                'c.dana_terkumpul',
                'c.saldo_tersedia',
                'c.saldo_terkunci',
                'c.url_rab',
                'c.created_at',
                'c.updated_at',
                'k.id_komunitas',
                'k.nama_lembaga',
                'kc.nama_kategori',
                'w.nama as nama_wilayah'
            )
            ->where('c.id_campaign', $id)
            ->first();

        $donations = DB::table('donasi as d')
            ->join('users as u', 'u.id_user', '=', 'd.id_user')
            ->select(
                'd.id_donasi',
                'd.nominal',
                'd.metode_pembayaran',
                'd.status_pembayaran',
                'd.is_anonim',
                'd.nama_tampil',
                'd.created_at',
                'u.id_user',
                'u.username',
                'u.nama_lengkap',
                'u.email'
            )
            ->where('d.id_campaign', $id)
            ->orderByDesc('d.created_at')
            ->limit(50)
            ->get();

        $withdrawals = DB::table('pencairan_dana as pd')
            ->leftJoin('laporan_penggunaan_dana as lpd', 'lpd.id_laporan', '=', 'pd.id_laporan_dana')
            ->select(
                'pd.id_pencairan',
                'pd.urutan_ke',
                'pd.nominal_diajukan',
                'pd.nominal_disetujui',
                'pd.status',
                'pd.alasan_penolakan',
                'pd.url_proposal',
                'pd.bukti_transfer_url',
                'pd.tanggal_pengajuan',
                'pd.tanggal_keputusan',
                'lpd.id_laporan',
                'lpd.deskripsi_penggunaan',
                'lpd.total_realisasi',
                'lpd.file_dokumentasi_url',
                'lpd.status_verifikasi as status_laporan'
            )
            ->where('pd.id_campaign', $id)
            ->orderByDesc('pd.tanggal_pengajuan')
            ->get();

        return $this->success([
            'campaign' => $campaign,
            'summary' => $summary,
            'donations' => $donations,
            'withdrawals' => $withdrawals,
        ], 'Monitoring internal campaign berhasil dimuat.');
    }
}