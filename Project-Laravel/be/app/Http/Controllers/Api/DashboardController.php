<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\PencairanDana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * l. GET /api/v1/komunitas/dashboard  (JWT: komunitas)
     * Tidak ada error spesifik: tampilkan 0 jika kosong.
     */
    public function index(Request $request): JsonResponse
    {
        $idKomunitas = $request->user()->komunitas->id_komunitas;

        $campaignStats = Campaign::where('id_komunitas', $idKomunitas)
            ->selectRaw("
                COUNT(*) FILTER (WHERE status = 'aktif')             AS total_campaign_aktif,
                COUNT(*) FILTER (WHERE status = 'selesai')           AS total_campaign_selesai,
                COUNT(*) FILTER (WHERE status = 'menunggu_review')   AS total_campaign_review,
                COUNT(*) FILTER (WHERE status = 'ditolak')           AS total_campaign_ditolak,
                COALESCE(SUM(dana_terkumpul), 0)                     AS total_dana_terkumpul,
                COALESCE(SUM(saldo_tersedia), 0)                     AS total_saldo_tersisa
            ")
            ->first();

        $totalDonaturUnik = Donasi::whereIn('id_campaign',
                Campaign::where('id_komunitas', $idKomunitas)->select('id_campaign'))
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
            ->distinct('id_user')
            ->count('id_user');

        $totalDanaDicairkan = (int) PencairanDana::where('id_komunitas', $idKomunitas)
            ->where('status', PencairanDana::STATUS_SELESAI)
            ->sum('nominal_disetujui');

        $pencairanPending = PencairanDana::where('id_komunitas', $idKomunitas)
            ->where('status', PencairanDana::STATUS_MENUNGGU_REVIEW)
            ->orderByDesc('tanggal_pengajuan')
            ->get(['id_pencairan', 'id_campaign', 'nominal_diajukan', 'status', 'tanggal_pengajuan']);

        $campaignHampirSelesai = Campaign::where('id_komunitas', $idKomunitas)
            ->where('status', Campaign::STATUS_AKTIF)
            ->whereColumn('dana_terkumpul', '>=', DB::raw('target_dana * 0.8'))
            ->get(['id_campaign', 'judul', 'dana_terkumpul', 'target_dana', 'tanggal_selesai']);

        return $this->success([
            'statistik' => [
                'total_campaign_aktif'   => (int) $campaignStats->total_campaign_aktif,
                'total_campaign_selesai' => (int) $campaignStats->total_campaign_selesai,
                'total_campaign_review'  => (int) $campaignStats->total_campaign_review,
                'total_campaign_ditolak' => (int) $campaignStats->total_campaign_ditolak,
                'total_donatur_unik'     => (int) $totalDonaturUnik,
                'total_dana_terkumpul'   => (int) $campaignStats->total_dana_terkumpul,
                'total_dana_dicairkan'   => (int) $totalDanaDicairkan,
                'total_saldo_tersisa'    => (int) $campaignStats->total_saldo_tersisa,
            ],
            'daftar_pencairan_pending'      => $pencairanPending,
            'daftar_campaign_hampir_selesai'=> $campaignHampirSelesai,
        ]);
    }

    public function donationStatistics(Request $request): JsonResponse
    {
        $idKomunitas = $request->user()->komunitas->id_komunitas;

        $campaignIds = Campaign::where('id_komunitas', $idKomunitas)
            ->whereIn('status', [Campaign::STATUS_AKTIF, Campaign::STATUS_SELESAI])
            ->pluck('id_campaign');

        $query = Donasi::whereIn('id_campaign', $campaignIds)
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL);

        $totalDonasi = (int) (clone $query)->sum('nominal');
        $jumlahDonatur = (int) (clone $query)->distinct('id_user')->count('id_user');
        $donasiHariIni = (int) (clone $query)->whereDate('created_at', today())->sum('nominal');
        $rataRata = $jumlahDonatur > 0 ? (int) round($totalDonasi / $jumlahDonatur) : 0;

        return $this->success([
            'total_donasi'    => $totalDonasi,
            'jumlah_donatur'  => $jumlahDonatur,
            'donasi_hari_ini' => $donasiHariIni,
            'rata_rata_donasi'=> $rataRata,
        ]);
    }

    public function donationChart(Request $request): JsonResponse
    {
        $idKomunitas = $request->user()->komunitas->id_komunitas;
        $campaignId  = $request->integer('campaign_id', 0) ?: null;
        $startDate   = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate     = $request->input('end_date', Carbon::now()->toDateString());

        $query = Donasi::selectRaw('DATE(created_at) AS tanggal, SUM(nominal) AS total_donasi')
            ->whereIn('id_campaign', function ($q) use ($idKomunitas) {
                $q->select('id_campaign')
                  ->from('campaign')
                  ->where('id_komunitas', $idKomunitas);
            })
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('tanggal');

        if ($campaignId) {
            $query->where('id_campaign', $campaignId);
        }

        $rows = $query->get();

        $chart = $rows->map(function ($row) {
            return [
                'label'   => $row->tanggal,
                'nominal' => (int) $row->total_donasi,
            ];
        });

        return $this->success(['chart' => $chart]);
    }
}
