<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JenisDokumen;
use App\Models\JenisLembaga;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MasterDataController extends Controller
{
    public function jenisLembaga()
    {
        $data = Cache::remember('master:jenis-lembaga', 43200, function () {
            return JenisLembaga::all(['id_jenis', 'nama_jenis']);
        });

        return ApiResponse::success($data, 'Daftar jenis lembaga berhasil dimuat');
    }

    public function wilayah(Request $request)
    {
        $level = $request->get('level');
        $parent = $request->get('parent');
        $cacheKey = 'master:wilayah:' . md5($level . $parent);

        $data = Cache::remember($cacheKey, 43200, function () use ($level, $parent) {
            $query = Wilayah::query();

            if ($level) {
                $query->whereRaw(
                    'array_length(string_to_array(kode, \'.\'), 1) = ?',
                    [(int) $level]
                );
            }

            if ($parent) {
                $query->where('kode', 'like', $parent . '.%')
                      ->whereRaw(
                          'array_length(string_to_array(kode, \'.\'), 1) = ?',
                          [((int) substr_count($parent, '.') + 2)]
                      );
            }

            return $query->orderBy('nama')->get(['kode', 'nama']);
        });

        return ApiResponse::success($data, 'Daftar wilayah berhasil dimuat');
    }

    public function jenisDokumen(Request $request)
    {
        $jenisLembagaId = $request->get('jenis_lembaga_id');
        $cacheKey = 'master:jenis-dokumen:' . ($jenisLembagaId ?? 'all');

        $data = Cache::remember($cacheKey, 43200, function () use ($jenisLembagaId) {
            $query = JenisDokumen::query();

            if ($jenisLembagaId) {
                $query->where(function ($q) use ($jenisLembagaId) {
                    $q->where('wajib_untuk_jenis_lembaga', (string) $jenisLembagaId)
                      ->orWhereNull('wajib_untuk_jenis_lembaga');
                });
            }

            return $query->orderBy('id_jenis_dok')->get();
        });

        return ApiResponse::success($data, 'Daftar jenis dokumen berhasil dimuat');
    }
}
