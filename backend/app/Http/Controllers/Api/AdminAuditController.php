<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Helpers\ApiResponse;

class AdminAuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        $query = DB::table('audit_logs as al')
            ->leftJoin('users as u', 'u.id_user', '=', 'al.user_id')
            ->select('al.*', 'u.nama_lengkap as user_name')
            ->orderByDesc('al.created_at');

        if ($request->filled('id_user'))     $query->where('al.user_id', $request->query('id_user'));
        if ($request->filled('action_type')) $query->where('al.action_type', $request->query('action_type'));
        if ($request->filled('start_date'))  $query->where('al.created_at', '>=', $request->query('start_date'));
        if ($request->filled('end_date'))    $query->where('al.created_at', '<=', $request->query('end_date'));

        $page = $query->paginate($perPage);

        return ApiResponse::success($page, 'Audit logs berhasil dimuat.');
    }
}
