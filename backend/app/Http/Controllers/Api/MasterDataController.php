<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\JenisDokumen;
use App\Models\JenisLembaga;
use App\Models\Wilayah;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function jenisLembaga()
    {
        return ApiResponse::success(
            JenisLembaga::all(['id_jenis', 'nama_jenis']),
            'Daftar jenis lembaga berhasil dimuat'
        );
    }

    public function wilayah(Request $request)
    {
        $query = Wilayah::query();
        $level = $request->get('level');
        $parent = $request->get('parent');

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

        return ApiResponse::success(
            $query->orderBy('nama')->get(['kode', 'nama']),
            'Daftar wilayah berhasil dimuat'
        );
    }

    public function jenisDokumen(Request $request)
    {
        $query = JenisDokumen::query();
        $jenisLembagaId = $request->get('jenis_lembaga_id');

        if ($jenisLembagaId) {
            $query->where(function ($q) use ($jenisLembagaId) {
                $q->where('wajib_untuk_jenis_lembaga', (string) $jenisLembagaId)
                  ->orWhereNull('wajib_untuk_jenis_lembaga');
            });
        }

        return ApiResponse::success(
            $query->orderBy('id_jenis_dok')->get(),
            'Daftar jenis dokumen berhasil dimuat'
        );
    }
}
