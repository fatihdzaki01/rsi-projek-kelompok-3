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
            'keyword' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:aktif,selesai'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Parameter pencarian tidak valid',
                'errors' => [
                    'code' => 'ERR-SEARCH-03',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $perPage = $request->input('per_page', 10);

        $query = DB::table('campaign')
            ->leftJoin('komunitas', 'campaign.id_komunitas', '=', 'komunitas.id_komunitas')
            ->leftJoin('kategori_campaign', 'campaign.id_kategori', '=', 'kategori_campaign.id_kategori')
            ->leftJoin('wilayah', 'campaign.kode_wilayah', '=', 'wilayah.kode_wilayah')
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

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('campaign.judul', 'ILIKE', "%{$keyword}%")
                  ->orWhere('campaign.deskripsi', 'ILIKE', "%{$keyword}%")
                  ->orWhere('komunitas.nama_lembaga', 'ILIKE', "%{$keyword}%");
            });
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

        $campaigns = $query
            ->orderByDesc('campaign.created_at')
            ->paginate($perPage);

        if ($campaigns->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'items' => [],
                    'pagination' => [
                        'current_page' => $campaigns->currentPage(),
                        'per_page' => $campaigns->perPage(),
                        'total' => $campaigns->total(),
                        'last_page' => $campaigns->lastPage(),
                    ]
                ],
                'message' => 'Tidak ada campaign yang sesuai dengan pencarian',
                'errors' => null
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $campaigns->items(),
                'pagination' => [
                    'current_page' => $campaigns->currentPage(),
                    'per_page' => $campaigns->perPage(),
                    'total' => $campaigns->total(),
                    'last_page' => $campaigns->lastPage(),
                ]
            ],
            'message' => 'Hasil pencarian campaign berhasil ditampilkan',
            'errors' => null
        ], 200);
    }

    public function communities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'keyword' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'kabupaten_kota' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:terbaru'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Parameter pencarian tidak valid',
                'errors' => [
                    'code' => 'ERR-SEARCH-03',
                    'details' => $validator->errors()
                ]
            ], 422);
        }

        $perPage = $request->input('per_page', 10);

        $query = DB::table('komunitas')
            ->leftJoin('wilayah', 'komunitas.kode_wilayah', '=', 'wilayah.kode_wilayah')
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
            ->where('komunitas.status', 'aktif');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('komunitas.nama_lembaga', 'ILIKE', "%{$keyword}%")
                  ->orWhere('komunitas.deskripsi', 'ILIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('provinsi')) {
            $query->where('wilayah.nama', 'ILIKE', "%{$request->provinsi}%");
        }

        if ($request->filled('kabupaten_kota')) {
            $query->where('wilayah.nama', 'ILIKE', "%{$request->kabupaten_kota}%");
        }

        $query->orderByDesc('komunitas.created_at');

        $communities = $query->paginate($perPage);

        if ($communities->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'items' => [],
                    'pagination' => [
                        'current_page' => $communities->currentPage(),
                        'per_page' => $communities->perPage(),
                        'total' => $communities->total(),
                        'last_page' => $communities->lastPage(),
                    ]
                ],
                'message' => 'Tidak ada komunitas yang sesuai dengan pencarian',
                'errors' => null
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $communities->items(),
                'pagination' => [
                    'current_page' => $communities->currentPage(),
                    'per_page' => $communities->perPage(),
                    'total' => $communities->total(),
                    'last_page' => $communities->lastPage(),
                ]
            ],
            'message' => 'Hasil pencarian komunitas berhasil ditampilkan',
            'errors' => null
        ], 200);
    }
}