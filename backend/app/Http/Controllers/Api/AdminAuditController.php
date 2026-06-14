<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * n. GET /api/v1/admin/audit-logs (Bonus, JWT: superadmin)
 *
 * Catatan: tabel audit_logs belum ada di DDL. Controller ini mengasumsikan
 * tabel `audit_logs` (lihat migration contoh di database/migrations).
 */
class AdminAuditController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);

        $query = DB::table('audit_logs')->orderByDesc('created_at');

        if ($request->filled('id_user'))     $query->where('user_id', $request->query('id_user'));
        if ($request->filled('action_type')) $query->where('action_type', $request->query('action_type'));
        if ($request->filled('start_date'))  $query->where('created_at', '>=', $request->query('start_date'));
        if ($request->filled('end_date'))    $query->where('created_at', '<=', $request->query('end_date'));

        $page = $query->paginate($perPage);

        return $this->paginated($page);
    }
}
