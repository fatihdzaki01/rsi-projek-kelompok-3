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

    public function publicCampaign(Request $request, int $id)
    {
        $perPage = min((int) $request->input('per_page', 15), 100);
        $page = max(1, (int) $request->input('page', 1));

        $campaign = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->select(
                'c.id_campaign',
                'c.judul',
                'c.deskripsi',
                'c.status',
                'c.target_dana',
                'c.dana_terkumpul',
                'c.tanggal_mulai',
                'c.tanggal_selesai',
                'c.created_at',
                'c.tipe_distribusi',
                'c.target_audiens',
                'c.total_penerima_manfaat',
                'k.id_komunitas',
                'k.nama_lembaga',
                'kc.nama_kategori'
            )
            ->where('c.id_campaign', $id)
            ->first();

        if (!$campaign) {
            return $this->error('Campaign tidak ditemukan.', 404);
        }

        $jumlahDonatur = DB::table('donasi')
            ->where('id_campaign', $id)
            ->where('status_pembayaran', 'berhasil')
            ->distinct('id_user')
            ->count('id_user');

        $daysRemaining = 0;
        if ($campaign->tanggal_selesai) {
            $daysRemaining = max(0, now()->diffInDays(\Carbon\Carbon::parse($campaign->tanggal_selesai), false));
        }

        $progressPersen = $campaign->target_dana > 0
            ? min(100, round(($campaign->dana_terkumpul / $campaign->target_dana) * 100, 2))
            : 0;

        $donorQuery = DB::table('donasi as d')
            ->join('users as u', 'u.id_user', '=', 'd.id_user')
            ->where('d.id_campaign', $id)
            ->where('d.status_pembayaran', 'berhasil')
            ->select(
                'd.is_anonim',
                'd.nama_tampil',
                'u.nama_lengkap',
                'd.created_at'
            )
            ->orderByDesc('d.created_at');

        $totalDonor = $donorQuery->count();
        $recentDonors = $donorQuery
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn ($r) => [
                'nama'       => $r->is_anonim ? 'Anonim' : ($r->nama_tampil ?? $r->nama_lengkap),
                'created_at' => $r->created_at,
            ]);

        $jumlahPencairan = DB::table('pencairan_dana')
            ->where('id_campaign', $id)
            ->whereIn('status', ['disetujui', 'selesai'])
            ->count();

        return $this->success([
            'id_campaign'      => $campaign->id_campaign,
            'judul'            => $campaign->judul,
            'status'           => $campaign->status,
            'nama_lembaga'     => $campaign->nama_lembaga,
            'nama_kategori'    => $campaign->nama_kategori,
            'target_dana'      => (int) $campaign->target_dana,
            'dana_terkumpul'   => (int) $campaign->dana_terkumpul,
            'progress_persen'  => $progressPersen,
            'jumlah_donatur'   => $jumlahDonatur,
            'hari_tersisa'     => $daysRemaining,
            'tanggal_mulai'    => $campaign->tanggal_mulai,
            'tanggal_selesai'  => $campaign->tanggal_selesai,
            'tipe_distribusi'  => $campaign->tipe_distribusi,
            'target_audiens'   => $campaign->target_audiens,
            'total_penerima_manfaat' => (int) ($campaign->total_penerima_manfaat ?? 0),
            'target_penerima_label' => $campaign->tipe_distribusi === 'kolektif'
                ? 'Kolektif'
                : ($campaign->total_penerima_manfaat ?? '-'),
            'jumlah_pencairan' => $jumlahPencairan,
            'donatur_terbaru'  => $recentDonors,
            'donatur_pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $totalDonor,
                'last_page'    => (int) ceil($totalDonor / max($perPage, 1)),
            ],
        ], 'Monitoring campaign berhasil dimuat.');
    }

    public function internalCampaign(Request $request, int $id)
    {
        $perPage = min((int) $request->input('per_page', 50), 100);
        $page = max(1, (int) $request->input('page', 1));
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
                'c.tipe_distribusi',
                'c.target_audiens',
                'c.total_penerima_manfaat',
                'c.alasan_penolakan',
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
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $totalDonations = DB::table('donasi')
            ->where('id_campaign', $id)
            ->count();

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

        $totalDonatur = DB::table('donasi')
            ->where('id_campaign', $id)
            ->where('status_pembayaran', 'berhasil')
            ->distinct('id_user')
            ->count('id_user');

        return $this->success([
            'campaign' => array_merge((array) $campaign, [
                'target_penerima_label' => ($campaign->tipe_distribusi ?? '') === 'kolektif'
                    ? 'Kolektif'
                    : ($campaign->total_penerima_manfaat ?? '-'),
                'jumlah_pencairan' => DB::table('pencairan_dana')
                    ->where('id_campaign', $id)
                    ->whereIn('status', ['disetujui', 'selesai'])
                    ->count(),
            ]),
            'summary' => [
                'total_donasi_berhasil' => (int) ($summary->total_dana_masuk ?? 0),
                'total_donatur'         => $totalDonatur,
                'total_dicairkan'       => (int) ($summary->total_dana_dicairkan ?? 0),
                'saldo_tersisa'         => (int) ($summary->saldo_tersisa ?? 0),
                'potongan_platform'     => (int) ($summary->potongan_platform ?? 0),
            ],
            'donations' => $donations,
            'donations_pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalDonations,
                'last_page' => (int) ceil($totalDonations / max($perPage, 1)),
            ],
            'withdrawals' => $withdrawals,
        ], 'Monitoring internal campaign berhasil dimuat.');
    }
}