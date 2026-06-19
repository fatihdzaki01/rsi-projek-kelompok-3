<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PencairanController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/communities/withdrawals
     */
    public function index(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        // Ambil rekening aktif
        $rekeningAktif = null;
        if ($komunitas->nama_bank && $komunitas->nomor_rekening) {
            $rekeningAktif = [
                'nama_bank' => $komunitas->nama_bank,
                'nomor_rekening' => $komunitas->nomor_rekening,
                'foto_buku_rekening_url' => $komunitas->foto_buku_rekening_url,
            ];
        }

        // Ambil daftar campaign yang bisa dicairkan (saldo_tersedia > 0)
        $campaignTersedia = Campaign::where('id_komunitas', $komunitas->id_komunitas)
            ->where('saldo_tersedia', '>', 0)
            ->whereIn('status', [Campaign::STATUS_AKTIF, Campaign::STATUS_SELESAI])
            ->select('id_campaign', 'judul', 'saldo_tersedia')
            ->orderBy('id_campaign', 'desc')
            ->get();

        // Ambil riwayat pencairan untuk komunitas ini
        $riwayat = DB::table('pencairan_dana')
            ->join('campaign', 'pencairan_dana.id_campaign', '=', 'campaign.id_campaign')
            ->where('pencairan_dana.id_komunitas', $komunitas->id_komunitas)
            ->select(
                'pencairan_dana.id_pencairan',
                'pencairan_dana.nominal_diajukan',
                'pencairan_dana.status',
                'pencairan_dana.tanggal_pengajuan',
                'pencairan_dana.alasan_penolakan',
                'campaign.judul as judul_campaign'
            )
            ->orderByDesc('pencairan_dana.tanggal_pengajuan')
            ->get();

        return $this->success([
            'rekening_aktif' => $rekeningAktif,
            'campaigns' => $campaignTersedia,
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * POST /api/v1/communities/withdrawals
     */
    public function store(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        try {
            $data = $request->validate([
                'id_campaign' => ['required', 'integer', 'exists:campaign,id_campaign'],
                'nominal_diajukan' => ['required', 'integer', 'min:10000'], // Misal minimal 10.000
                'keterangan' => ['required', 'string'],
                'url_proposal' => ['required', 'string', 'max:255', 'url'],
            ]);
        } catch (ValidationException $e) {
            return $this->error('ERR-WDL-01', 'Data tidak valid', 400, $e->errors());
        }

        $campaign = Campaign::where('id_campaign', $data['id_campaign'])
            ->where('id_komunitas', $komunitas->id_komunitas)
            ->first();

        if (!$campaign) {
            return $this->error('ERR-WDL-02', 'Campaign tidak ditemukan atau bukan milik komunitas Anda', 403);
        }

        if ($data['nominal_diajukan'] > $campaign->saldo_tersedia) {
            return $this->error('ERR-WDL-03', 'Nominal diajukan melebihi saldo tersedia', 400);
        }

        if (!$komunitas->nama_bank || !$komunitas->nomor_rekening) {
            return $this->error('ERR-WDL-04', 'Rekening komunitas belum diatur', 400);
        }

        // Cek apakah ada pencairan yang sedang menunggu review untuk campaign ini
        $pending = DB::table('pencairan_dana')
            ->where('id_campaign', $campaign->id_campaign)
            ->where('status', 'menunggu_review')
            ->exists();

        if ($pending) {
            return $this->error('ERR-WDL-05', 'Masih ada pengajuan pencairan yang menunggu review untuk campaign ini', 409);
        }

        $pencairan = DB::transaction(function () use ($data, $campaign, $komunitas) {
            // Dapatkan urutan_ke
            $maxUrutan = DB::table('pencairan_dana')
                ->where('id_campaign', $campaign->id_campaign)
                ->max('urutan_ke');
            $urutan = $maxUrutan ? $maxUrutan + 1 : 1;

            // Kurangi saldo_tersedia, tambah saldo_terkunci
            $campaign->saldo_tersedia -= $data['nominal_diajukan'];
            $campaign->saldo_terkunci += $data['nominal_diajukan'];
            $campaign->save();

            // Insert ke tabel pencairan_dana
            $id = DB::table('pencairan_dana')->insertGetId([
                'id_campaign' => $campaign->id_campaign,
                'id_komunitas' => $komunitas->id_komunitas,
                'urutan_ke' => $urutan,
                'nominal_diajukan' => $data['nominal_diajukan'],
                'keterangan' => $data['keterangan'],
                'url_proposal' => $data['url_proposal'],
                'nama_bank_tujuan' => $komunitas->nama_bank,
                'nomor_rekening_tujuan' => $komunitas->nomor_rekening,
                'status' => 'menunggu_review',
                'tanggal_pengajuan' => now(),
            ]);

            return $id;
        });

        return $this->success([
            'id_pencairan' => $pencairan,
            'message' => 'Pencairan dana berhasil diajukan dan menunggu review Superadmin',
        ], 201);
    }
}
