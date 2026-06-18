<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function campaigns(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword'        => ['nullable', 'string', 'max:100'],
            'kategori'       => ['nullable', 'string', 'max:100'],
            'provinsi'       => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'status'         => ['nullable', 'in:aktif,selesai'],
            'page'           => ['nullable', 'integer', 'min:1'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'data'    => null,
                'message' => 'Parameter pencarian tidak valid',
                'errors'  => [
                    'code'    => 'ERR-SEARCH-03',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $perPage = $request->input('per_page', 10);
        $keyword = $request->input('keyword');

        $query = DB::table('campaign')
            ->leftJoin('komunitas', 'campaign.id_komunitas', '=', 'komunitas.id_komunitas')
            ->leftJoin('kategori_campaign', 'campaign.id_kategori', '=', 'kategori_campaign.id_kategori')
            ->leftJoin('wilayah', 'campaign.kode_wilayah', '=', 'wilayah.kode')
            ->select(
                'campaign.id_campaign',
                'campaign.judul',
                'campaign.deskripsi',
                'campaign.foto_campaign_url',
                'campaign.target_dana',
                'campaign.dana_terkumpul',
                'campaign.tanggal_selesai',
                'campaign.status',
                'komunitas.nama_lembaga',
                'kategori_campaign.nama_kategori',
                'wilayah.nama as nama_wilayah'
            )
            ->whereIn('campaign.status', ['aktif', 'selesai']);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereRaw("campaign.search_vector @@ plainto_tsquery('indonesian', ?)", [$keyword])
                  ->orWhereRaw("komunitas.search_vector @@ plainto_tsquery('indonesian', ?)", [$keyword]);
            })
            ->orderByRaw("
                GREATEST(
                    COALESCE(ts_rank(campaign.search_vector, plainto_tsquery('indonesian', ?)), 0),
                    COALESCE(ts_rank(komunitas.search_vector, plainto_tsquery('indonesian', ?)), 0)
                ) DESC
            ", [$keyword, $keyword]);
        } else {
            $query->orderByDesc('campaign.created_at');
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_campaign.nama_kategori', 'ILIKE', "%{$request->kategori}%");
        }

        if ($request->filled('provinsi')) {
            $query->where('wilayah.nama', 'ILIKE', "%{$request->provinsi}%");
        }

        if ($request->filled('kabupaten_kota')) {
            $query->where('wilayah.nama', 'ILIKE', "%{$request->kabupaten_kota}%");
        }

        if ($request->filled('status')) {
            $query->where('campaign.status', $request->status);
        }

        $campaigns = $query->paginate($perPage);

        $totalDonatur = DB::table('donasi')
            ->where('status_pembayaran', 'berhasil')
            ->distinct('id_user')
            ->count('id_user');

        $totalDonasi = DB::table('donasi')
            ->where('status_pembayaran', 'berhasil')
            ->sum('nominal');

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'items'      => $campaigns->items(),
                'pagination' => [
                    'current_page' => $campaigns->currentPage(),
                    'per_page'     => $campaigns->perPage(),
                    'total'        => $campaigns->total(),
                    'last_page'    => $campaigns->lastPage(),
                ],
                'total_donatur' => $totalDonatur,
                'total_donasi'  => (int) $totalDonasi,
            ],
            'message' => $campaigns->isEmpty() ? 'Tidak ada campaign yang sesuai dengan pencarian' : 'Hasil pencarian campaign berhasil ditampilkan',
            'errors'  => null,
        ], 200);
    }

    public function communities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword'        => ['nullable', 'string', 'max:100'],
            'provinsi'       => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'sort'           => ['nullable', 'in:terbaru'],
            'page'           => ['nullable', 'integer', 'min:1'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'data'    => null,
                'message' => 'Parameter pencarian tidak valid',
                'errors'  => [
                    'code'    => 'ERR-SEARCH-03',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $perPage = $request->input('per_page', 10);
        $keyword = $request->input('keyword');

        $query = DB::table('komunitas')
            ->leftJoin('wilayah', 'komunitas.kode_wilayah', '=', 'wilayah.kode')
            ->select(
                'komunitas.id_komunitas',
                'komunitas.nama_lembaga',
                'komunitas.deskripsi',
                'komunitas.foto_lembaga_url',
                'komunitas.nomor_kontak',
                'komunitas.link_medsos',
                'komunitas.status',
                'wilayah.nama as nama_wilayah'
            )
            ->selectRaw('(SELECT COUNT(*) FROM follow_komunitas WHERE id_komunitas = komunitas.id_komunitas AND is_active = TRUE) as total_follower')
            ->selectRaw('(SELECT COALESCE(SUM(dana_terkumpul), 0) FROM campaign WHERE id_komunitas = komunitas.id_komunitas AND status IN (\'aktif\', \'selesai\')) as total_dana_diterima')
            ->selectRaw('(SELECT COUNT(*) FROM campaign WHERE id_komunitas = komunitas.id_komunitas AND status = \'aktif\') as total_campaign_aktif')
            ->selectRaw('(SELECT COUNT(*) FROM campaign WHERE id_komunitas = komunitas.id_komunitas AND status = \'selesai\') as total_campaign_selesai')
            ->where('komunitas.status', 'aktif');

        if ($keyword) {
            $query->whereRaw("komunitas.search_vector @@ plainto_tsquery('indonesian', ?)", [$keyword])
                  ->orderByRaw("ts_rank(komunitas.search_vector, plainto_tsquery('indonesian', ?)) DESC", [$keyword]);
        } else {
            $query->orderByDesc('komunitas.created_at');
        }

        if ($request->filled('provinsi')) {
            $query->where('wilayah.nama', 'ILIKE', "%{$request->provinsi}%");
        }

        if ($request->filled('kabupaten_kota')) {
            $query->where('wilayah.nama', 'ILIKE', "%{$request->kabupaten_kota}%");
        }

        $communities = $query->paginate($perPage);

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'items'      => $communities->items(),
                'pagination' => [
                    'current_page' => $communities->currentPage(),
                    'per_page'     => $communities->perPage(),
                    'total'        => $communities->total(),
                    'last_page'    => $communities->lastPage(),
                ],
            ],
            'message' => $communities->isEmpty() ? 'Tidak ada komunitas yang sesuai dengan pencarian' : 'Hasil pencarian komunitas berhasil ditampilkan',
            'errors'  => null,
        ], 200);
    }
}
