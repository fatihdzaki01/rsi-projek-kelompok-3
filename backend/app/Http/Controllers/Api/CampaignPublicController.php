<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignPublicController extends Controller
{
    use ApiResponse;

    /**
     * g. GET /api/v1/campaign/{id}/donatur  (Public)
     */
    public function donatur(int $id): JsonResponse
    {
        $donatur = Donasi::where('id_campaign', $id)
            ->where('status_pembayaran', Donasi::STATUS_BERHASIL)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($d) => [
                // nama_tampil NULL jika anonim (mirror chk_nama_tampil_anonim)
                'nama_tampil' => $d->is_anonim ? null : $d->nama_tampil,
                'nominal'     => $d->nominal,
                'created_at'  => $d->created_at,
            ]);

        if ($donatur->isEmpty()) {
            return $this->error('ERR-DON-01', 'Belum ada donatur', 404);
        }

        return $this->success(['daftar_donatur' => $donatur]);
    }

    /**
     * i. GET /api/v1/campaign  (Public) — list campaign dengan filter & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        $query = Campaign::query()
            ->with('kategori:id_kategori,nama_kategori')
            ->whereIn('status', [Campaign::STATUS_AKTIF, Campaign::STATUS_SELESAI]);

        if ($request->filled('id_kategori'))    $query->where('id_kategori', $request->query('id_kategori'));
        if ($request->filled('kode_wilayah'))   $query->where('kode_wilayah', $request->query('kode_wilayah'));
        if ($request->filled('target_audiens')) $query->where('target_audiens', $request->query('target_audiens'));
        if ($request->filled('status'))         $query->where('status', $request->query('status'));

        if (!$query->clone()->exists()) {
            return $this->error('ERR-CAMP-04', 'Belum ada campaign', 404);
        }

        $page = $query->orderByDesc('created_at')->paginate($perPage);

        $data = collect($page->items())->map(fn ($c) => [
            'id_campaign'       => $c->id_campaign,
            'judul'             => $c->judul,
            'foto_campaign_url' => $c->foto_campaign_url,
            'nama_kategori'     => $c->kategori?->nama_kategori,
            'kode_wilayah'      => $c->kode_wilayah,
            'dana_terkumpul'    => $c->dana_terkumpul,
            'target_dana'       => $c->target_dana,
            'tanggal_selesai'   => $c->tanggal_selesai,
            'status'            => $c->status,
        ]);

        return $this->paginated($page, $data);
    }

    /**
     * j. GET /api/v1/campaign/{id}/detail-publik  (Public)
     */
    public function detailPublik(int $id): JsonResponse
    {
        $campaign = Campaign::with(['komunitas:id_komunitas,nama_lembaga,foto_lembaga_url'])
            ->where('id_campaign', $id)
            ->first();

        // ERR-CAMP-05: tidak ditemukan
        if (!$campaign) {
            return $this->error('ERR-CAMP-05', 'Campaign tidak ditemukan', 404);
        }

        // ERR-MON-01: belum boleh diakses publik (mis. masih review/ditolak)
        if (in_array($campaign->status, [Campaign::STATUS_MENUNGGU_REVIEW, Campaign::STATUS_DITOLAK])) {
            return $this->error('ERR-MON-01', 'Campaign tidak dapat diakses publik', 403);
        }

        $updatePost = $campaign->updates()
            ->orderByDesc('is_pinned')->orderByDesc('created_at')
            ->with('foto')
            ->get();

        return $this->success([
            'judul'             => $campaign->judul,
            'foto_campaign_url' => $campaign->foto_campaign_url,
            'deskripsi'         => $campaign->deskripsi,
            'id_kategori'       => $campaign->id_kategori,
            'kode_wilayah'      => $campaign->kode_wilayah,
            'target_audiens'    => $campaign->target_audiens,
            'target_dana'       => $campaign->target_dana,
            'dana_terkumpul'    => $campaign->dana_terkumpul,
            'saldo_tersedia'    => $campaign->saldo_tersedia,
            'saldo_terkunci'    => $campaign->saldo_terkunci,
            'tanggal_mulai'     => $campaign->tanggal_mulai,
            'tanggal_selesai'   => $campaign->tanggal_selesai,
            'status'            => $campaign->status,
            'profil_komunitas'  => [
                'id_komunitas'     => $campaign->komunitas?->id_komunitas,
                'nama_lembaga'     => $campaign->komunitas?->nama_lembaga,
                'foto_lembaga_url' => $campaign->komunitas?->foto_lembaga_url,
            ],
            'timeline_dana' => [], // diisi dari view v_timeline_dana_campaign bila tersedia
            'update_post'   => $updatePost,
        ]);
    }
}
