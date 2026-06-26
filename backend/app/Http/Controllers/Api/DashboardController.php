<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use App\Models\PencairanDana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $trenDonasi = DB::table('donasi')
            ->join('campaign', 'campaign.id_campaign', '=', 'donasi.id_campaign')
            ->where('campaign.id_komunitas', $idKomunitas)
            ->where('donasi.status_pembayaran', Donasi::STATUS_BERHASIL)
            ->where('donasi.created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("
                TO_CHAR(donasi.created_at, 'YYYY-MM') as bulan,
                COALESCE(SUM(donasi.nominal), 0) as total
            ")
            ->groupByRaw("TO_CHAR(donasi.created_at, 'YYYY-MM')")
            ->orderBy('bulan')
            ->get()
            ->map(fn ($r) => [
                'bulan' => $r->bulan,
                'total' => (int) $r->total,
            ])
            ->values();

        $komposisiMetode = DB::table('donasi')
            ->join('campaign', 'campaign.id_campaign', '=', 'donasi.id_campaign')
            ->where('campaign.id_komunitas', $idKomunitas)
            ->where('donasi.status_pembayaran', Donasi::STATUS_BERHASIL)
            ->whereNotNull('donasi.metode_pembayaran')
            ->selectRaw("
                donasi.metode_pembayaran,
                COUNT(*) as jumlah,
                COALESCE(SUM(donasi.nominal), 0) as total
            ")
            ->groupBy('donasi.metode_pembayaran')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'metode' => $r->metode_pembayaran,
                'jumlah' => (int) $r->jumlah,
                'total' => (int) $r->total,
            ])
            ->values();

        $trenPenerima = $trenDonasi;

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
            'tren_donasi' => $trenDonasi,
            'tren_penerima' => $trenPenerima,
            'komposisi_metode' => $komposisiMetode,
            'daftar_pencairan_pending'      => $pencairanPending,
            'daftar_campaign_hampir_selesai'=> $campaignHampirSelesai,
        ]);
    }

    public function donationStats(Request $request, int $id): JsonResponse
    {
        $komunitas = $request->user()->komunitas;
        $campaign = Campaign::where('id_campaign', $id)
            ->where('id_komunitas', $komunitas->id_komunitas)
            ->first();

        if (!$campaign) {
            return $this->error('ERR-DS-01', 'Campaign tidak ditemukan.', 404);
        }

        $targetPenerima = $campaign->tipe_distribusi === 'kolektif'
            ? 'Kolektif'
            : ($campaign->total_penerima_manfaat ?? 0);

        $trenDonasi = DB::table('donasi')
            ->where('id_campaign', $id)
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("
                TO_CHAR(created_at, 'YYYY-MM') as bulan,
                COALESCE(SUM(nominal), 0) as total
            ")
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM')")
            ->orderBy('bulan')
            ->get()
            ->map(fn ($r) => [
                'bulan' => $r->bulan,
                'total' => (int) $r->total,
            ])
            ->values();

        $komposisiMetode = DB::table('donasi')
            ->where('id_campaign', $id)
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
            ->whereNotNull('metode_pembayaran')
            ->selectRaw("
                metode_pembayaran,
                COUNT(*) as jumlah,
                COALESCE(SUM(nominal), 0) as total
            ")
            ->groupBy('metode_pembayaran')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'metode' => $r->metode_pembayaran,
                'jumlah' => (int) $r->jumlah,
                'total' => (int) $r->total,
            ])
            ->values();

        $jumlahPelapor = DB::table('laporan_campaign')
            ->where('id_campaign', $id)
            ->count();

        $pencairanCount = PencairanDana::where('id_campaign', $id)
            ->whereIn('status', [PencairanDana::STATUS_DISETUJUI, PencairanDana::STATUS_SELESAI])
            ->count();

        $pencairanTotal = (int) PencairanDana::where('id_campaign', $id)
            ->whereIn('status', [PencairanDana::STATUS_DISETUJUI, PencairanDana::STATUS_SELESAI])
            ->sum('nominal_disetujui');

        return $this->success([
            'campaign' => [
                'id_campaign' => $campaign->id_campaign,
                'judul' => $campaign->judul,
                'target_dana' => $campaign->target_dana,
                'dana_terkumpul' => $campaign->dana_terkumpul,
                'saldo_tersedia' => $campaign->saldo_tersedia,
                'status' => $campaign->status,
            ],
            'jumlah_penerima' => $targetPenerima,
            'tren_penerimaan' => $trenDonasi,
            'jumlah_pelapor' => $jumlahPelapor,
            'komposisi_metode' => $komposisiMetode,
            'jumlah_pencairan' => $pencairanCount,
            'total_dicairkan' => $pencairanTotal,
        ]);
    }
}
