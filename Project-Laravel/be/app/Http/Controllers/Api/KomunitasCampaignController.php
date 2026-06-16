<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\FotoUpdate;
use App\Models\Notifikasi;
use App\Models\PencairanDana;
use App\Models\UpdateCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KomunitasCampaignController extends Controller
{
    use ApiResponse;

    /**
     * f. GET /api/v1/komunitas/campaign/riwayat  (JWT: komunitas)
     */
    public function riwayat(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;
        $perPage   = (int) $request->query('per_page', 15);

        $query = Campaign::where('id_komunitas', $komunitas->id_komunitas)
            ->withCount('pencairan')
            ->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if (!$query->clone()->exists()) {
            return $this->error('ERR-CAMPAIGN-HISTORY-01', 'Belum ada campaign', 404);
        }

        $page = $query->paginate($perPage, [
            'id_campaign', 'judul', 'status', 'dana_terkumpul',
            'target_dana', 'saldo_tersedia', 'saldo_terkunci',
            'jumlah_pencairan_approve', 'created_at', 'alasan_penolakan',
        ]);

        return $this->paginated($page);
    }

    public function pencairanPerCampaign(Request $request, int $id): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        $campaign = Campaign::where('id_campaign', $id)
            ->where('id_komunitas', $komunitas->id_komunitas)
            ->first(['id_campaign', 'judul', 'dana_terkumpul', 'saldo_tersedia',
                      'saldo_terkunci', 'jumlah_pencairan_approve', 'target_dana', 'status']);

        if (!$campaign) {
            return $this->error('ERR-CAMPAIGN-05', 'Campaign tidak ditemukan', 404);
        }

        $pencairan = PencairanDana::where('id_campaign', $id)
            ->orderByDesc('tanggal_pengajuan')
            ->get(['id_pencairan', 'urutan_ke', 'nominal_diajukan', 'nominal_disetujui',
                   'status', 'alasan_penolakan', 'tanggal_pengajuan', 'tanggal_keputusan']);

        $jumlahPencairan = count($pencairan);

        return $this->success([
            'campaign'   => $campaign,
            'pencairan'  => $pencairan,
            'sisa_kesempatan' => max(0, 5 - $jumlahPencairan),
        ]);
    }

    /**
     * h. POST /api/v1/komunitas/campaign/ajukan  (JWT: komunitas)
     */
    public function ajukan(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        // ERR-CAMP-01: field wajib belum diisi
        try {
            $data = $request->validate([
                'judul'             => ['required', 'string', 'max:255'],
                'id_kategori'       => ['required', 'integer', 'exists:kategori_campaign,id_kategori'],
                'deskripsi'         => ['nullable', 'string'],
                'target_dana'       => ['required', 'integer'],
                'tanggal_mulai'     => ['nullable', 'date'],
                'tanggal_selesai'   => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
                'foto_campaign_url' => ['required', 'string', 'max:255'],
                'kode_wilayah'      => ['required', 'string', 'exists:wilayah,kode'],
                'target_audiens'    => ['nullable', 'string', 'max:150'],
                'tipe_distribusi'   => ['required', 'in:individual,kolektif'],
                'url_rab'           => ['nullable', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            return $this->error('ERR-CAMP-01', 'Field wajib belum diisi', 400);
        }

        // ERR-CAMP-03: target dana minimal Rp10.000.000 (mirror chk_target_dana_minimal)
        if ($data['target_dana'] < Campaign::TARGET_DANA_MINIMAL) {
            return $this->error('ERR-CAMP-03', 'Target dana minimal Rp10.000.000', 400);
        }

        // ERR-CAMP-02: format file RAB tidak valid (hanya PDF)
        if (!empty($data['url_rab']) && !str_ends_with(strtolower($data['url_rab']), '.pdf')) {
            return $this->error('ERR-CAMP-02', 'Format file RAB tidak valid', 400);
        }

        // Campaign individual wajib punya target_audiens (mirror chk_target_audiens_individual)
        if ($data['tipe_distribusi'] === 'individual' && empty($data['target_audiens'])) {
            return $this->error('ERR-CAMP-01', 'Field wajib belum diisi', 400);
        }

        $campaign = DB::transaction(function () use ($komunitas, $data) {
            $campaign = Campaign::create(array_merge($data, [
                'id_komunitas' => $komunitas->id_komunitas,
                'status'       => Campaign::STATUS_MENUNGGU_REVIEW,
            ]));

            Notifikasi::create([
                'id_penerima_komunitas' => $komunitas->id_komunitas,
                'judul'                 => 'Campaign diajukan',
                'pesan'                 => 'Campaign "' . $campaign->judul . '" menunggu review superadmin.',
                'tipe'                  => 'campaign',
                'related_campaign_id'   => $campaign->id_campaign,
                'is_read'               => false,
                'created_at'            => now(),
                'expires_at'            => Carbon::now()->addDays(Notifikasi::ttlDaysFor('campaign')),
            ]);

            return $campaign;
        });

        return $this->success([
            'id_campaign' => $campaign->id_campaign,
            'message'     => 'Campaign diajukan, menunggu review superadmin',
        ], 201);
    }

    /**
     * k. POST /api/v1/komunitas/campaign/{id}/update-post  (JWT: komunitas)
     */
    public function updatePost(Request $request, int $id): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        $campaign = Campaign::where('id_campaign', $id)
            ->where('id_komunitas', $komunitas->id_komunitas)
            ->first();
        if (!$campaign) {
            return $this->error('ERR-CAMP-05', 'Campaign tidak ditemukan', 404);
        }

        // ERR-MON-04: tidak dapat update campaign selesai/ditolak
        if (in_array($campaign->status, [Campaign::STATUS_SELESAI, Campaign::STATUS_DITOLAK, Campaign::STATUS_DITUTUP_PERMANEN])) {
            return $this->error('ERR-MON-04', 'Tidak dapat membuat update pada campaign selesai/ditolak', 403);
        }

        // ERR-MON-02: judul & konten wajib
        $validator = validator($request->all(), [
            'judul_update' => ['required', 'string', 'max:255'],
            'konten'       => ['required', 'string'],
            'is_pinned'    => ['sometimes', 'boolean'],
            'foto_urls'    => ['sometimes', 'array'],
            'foto_urls.*'  => ['string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return $this->error('ERR-MON-02', 'Judul dan konten wajib diisi', 400);
        }
        $data = $validator->validated();

        // ERR-MON-03: max 10 foto
        $fotoUrls = $data['foto_urls'] ?? [];
        if (count($fotoUrls) > 10) {
            return $this->error('ERR-MON-03', 'Jumlah foto melebihi batas maksimal 10', 400);
        }

        $update = DB::transaction(function () use ($campaign, $komunitas, $data, $fotoUrls) {
            $update = UpdateCampaign::create([
                'id_campaign'  => $campaign->id_campaign,
                'id_komunitas' => $komunitas->id_komunitas,
                'judul_update' => $data['judul_update'],
                'konten'       => $data['konten'],
                'is_pinned'    => $data['is_pinned'] ?? false,
            ]);

            foreach (array_values($fotoUrls) as $i => $url) {
                FotoUpdate::create([
                    'id_update'   => $update->id_update,
                    'foto_url'    => $url,
                    'urutan'      => $i + 1, // unik per update (uq_update_urutan)
                    'uploaded_at' => now(),
                ]);
            }

            // Notifikasi follower (mirror trg_notif_follower_update) — contoh sederhana
            foreach ($komunitas->followers()->where('is_active', true)->get() as $f) {
                Notifikasi::create([
                    'id_penerima_user'  => $f->id_user,
                    'judul'             => 'Update campaign baru',
                    'pesan'             => $campaign->judul . ': ' . $update->judul_update,
                    'tipe'              => 'update_campaign',
                    'related_update_id' => $update->id_update,
                    'is_read'           => false,
                    'created_at'        => now(),
                    'expires_at'        => Carbon::now()->addDays(Notifikasi::ttlDaysFor('update_campaign')),
                ]);
            }

            return $update;
        });

        return $this->success([
            'id_update' => $update->id_update,
            'message'   => 'Update berhasil dipublikasikan',
        ], 201);
    }

    /**
     * m. POST /api/v1/komunitas/campaign/{id}/klarifikasi  (JWT: komunitas)
     */
    public function klarifikasi(Request $request, int $id): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        $campaign = Campaign::where('id_campaign', $id)->first();
        if (!$campaign) {
            return $this->error('ERR-CAMP-05', 'Campaign tidak ditemukan', 404);
        }

        // ERR-LAPOR-11: akses ditolak (bukan pemilik campaign)
        if ($campaign->id_komunitas !== $komunitas->id_komunitas) {
            return $this->error('ERR-LAPOR-11', 'Akses ditolak', 403);
        }

        // ERR-LAPOR-09: teks klarifikasi wajib
        $teks = trim((string) $request->input('teks_klarifikasi', ''));
        if ($teks === '') {
            return $this->error('ERR-LAPOR-09', 'Teks klarifikasi wajib diisi', 400);
        }

        DB::transaction(function () use ($komunitas, $campaign, $teks) {
            Notifikasi::create([
                'id_penerima_komunitas' => $komunitas->id_komunitas,
                'judul'                 => 'Klarifikasi campaign dikirim',
                'pesan'                 => 'Klarifikasi untuk campaign "' . $campaign->judul . '" telah dikirim ke superadmin.',
                'tipe'                  => 'campaign',
                'related_campaign_id'   => $campaign->id_campaign,
                'is_read'               => false,
                'created_at'            => now(),
                'expires_at'            => Carbon::now()->addDays(Notifikasi::ttlDaysFor('campaign')),
            ]);
        });

        return $this->success(['message' => 'Klarifikasi berhasil dikirim']);
    }

    public function ajukanPencairan(Request $request): JsonResponse
    {
        $komunitas = $request->user()->komunitas;

        try {
            $data = $request->validate([
                'id_campaign' => ['required', 'integer', 'exists:campaign,id_campaign'],
                'nominal'     => ['required', 'integer', 'min:1'],
                'keterangan'  => ['required', 'string', 'max:1000'],
                'url_proposal'=> ['required', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            return $this->error('ERR-DISBURSE-01', 'Field wajib belum diisi', 400);
        }

        $campaign = Campaign::where('id_campaign', $data['id_campaign'])
            ->where('id_komunitas', $komunitas->id_komunitas)
            ->first();

        if (!$campaign) {
            return $this->error('ERR-CAMPAIGN-05', 'Campaign tidak ditemukan', 404);
        }

        if ($data['nominal'] > (int) $campaign->saldo_tersedia) {
            return $this->error('ERR-DISBURSE-02', 'Nominal melebihi saldo tersedia', 400);
        }

        $existingCount = PencairanDana::where('id_campaign', $campaign->id_campaign)->count();
        $urutanKe = $existingCount + 1;

        if ($urutanKe > 5) {
            return $this->error('ERR-DISBURSE-03', 'Batas maksimal pencairan (5x) telah tercapai', 400);
        }

        $komunitasData = DB::table('komunitas')
            ->where('id_komunitas', $komunitas->id_komunitas)
            ->first(['nama_bank', 'nomor_rekening']);

        $pencairan = PencairanDana::create([
            'id_campaign'          => $campaign->id_campaign,
            'id_komunitas'         => $komunitas->id_komunitas,
            'urutan_ke'            => $urutanKe,
            'nominal_diajukan'     => $data['nominal'],
            'keterangan'           => $data['keterangan'],
            'url_proposal'         => $data['url_proposal'],
            'nama_bank_tujuan'     => $komunitasData->nama_bank ?? '',
            'nomor_rekening_tujuan'=> $komunitasData->nomor_rekening ?? '',
            'status'               => PencairanDana::STATUS_MENUNGGU_REVIEW,
            'tanggal_pengajuan'    => now(),
        ]);

        return $this->success([
            'id_pencairan' => $pencairan->id_pencairan,
            'message'      => 'Pengajuan pencairan berhasil dikirim dan menunggu review',
        ], 201);
    }
}
