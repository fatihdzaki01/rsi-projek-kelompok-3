<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Traits\HasImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SuperadminController extends Controller
{
    use HasImageUpload;

    private function auditLog(Request $request, string $actionType, string $description): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => $request->user()->id_user,
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }

    private function bustCommunityProfileCache(int $idKomunitas): void
    {
        Cache::forget("community:profile:{$idKomunitas}");
    }

    public function profile(Request $request)
    {
        $user = $request->user();

        return ApiResponse::success([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'nama_lengkap' => $user->nama_lengkap,
            'foto_profil_url' => $user->foto_profil_url,
            'is_active' => $user->is_active,
            'is_verified' => $user->is_verified,
        ], 'Profil superadmin berhasil dimuat.');
    }

    public function updateProfile(Request $request)
    {
        if ($request->has('email')) {
            return ApiResponse::error('Email superadmin tidak dapat diubah dari halaman profil.', ['email' => ['Email tidak dapat diubah.']], 422);
        }

        $validated = $request->validate([
            'nama_lengkap' => ['nullable', 'string', 'max:150'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'foto_profil_url' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $fotoProfilUrl = $user->foto_profil_url;
        if ($request->hasFile('foto_profil')) {
            $fotoProfilUrl = $this->uploadImage($request->file('foto_profil'), 'profile-photos');
        }

        $user->update([
            'nama_lengkap' => $validated['nama_lengkap'] ?? $user->nama_lengkap,
            'foto_profil_url' => $fotoProfilUrl,
        ]);

        return ApiResponse::success([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'email' => $user->email,
            'nama_lengkap' => $user->nama_lengkap,
            'foto_profil_url' => $user->foto_profil_url,
        ], 'Profil superadmin berhasil diperbarui.');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['old_password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'old_password' => ['Password lama salah.'],
            ]);
        }

        if (Hash::check($validated['new_password'], $user->password_hash)) {
            return ApiResponse::error('Password baru tidak boleh sama dengan password lama.', 422, [
                'new_password' => ['Password baru tidak boleh sama dengan password lama.'],
            ]);
        }

        $user->password_hash = Hash::make($validated['new_password']);
        $user->save();

        $currentTokenId = $request->user()?->currentAccessToken()?->id;

        if ($currentTokenId) {
            $user->tokens()
                ->where('id', '!=', $currentTokenId)
                ->delete();
        }

        return ApiResponse::success(null, 'Password superadmin berhasil diperbarui.');
    }

    public function campaignReviewList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $status = $request->query('status', 'menunggu_review');

        $query = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->select(
                'c.id_campaign',
                'c.judul',
                'c.status',
                'c.target_dana',
                'c.dana_terkumpul',
                'c.tanggal_mulai',
                'c.tanggal_selesai',
                'c.created_at',
                'c.updated_at',
                'k.id_komunitas',
                'k.nama_lembaga',
                'kc.nama_kategori'
            );

        if ($status) {
            $query->where('c.status', $status);
        }

        $data = $query->orderByDesc('c.created_at')->paginate($perPage);

        return ApiResponse::success($data, 'Daftar campaign review berhasil dimuat.');
    }

    public function campaignDetail(int $id)
    {
        $campaign = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->leftJoin('wilayah as w', 'w.kode', '=', 'c.kode_wilayah')
            ->select(
                'c.*',
                'k.nama_lembaga',
                'k.nomor_kontak',
                'k.status as status_komunitas',
                'kc.nama_kategori',
                'w.nama as nama_wilayah'
            )
            ->where('c.id_campaign', $id)
            ->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        return ApiResponse::success($campaign, 'Detail campaign berhasil dimuat.');
    }

    public function approveCampaign(Request $request, int $id)
    {
        $campaign = DB::table('campaign')->where('id_campaign', $id)->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        if ($campaign->status !== 'menunggu_review') {
            return ApiResponse::error('Campaign sudah direview sebelumnya.', 409);
        }

        DB::table('campaign')
            ->where('id_campaign', $id)
            ->update([
                'status' => 'aktif',
                'alasan_penolakan' => null,
                'direview_oleh' => $request->user()->id_user,
                ]);

        // Notifikasi ke komunitas pemilik campaign
        Notifikasi::kirim([
            'id_penerima_komunitas' => $campaign->id_komunitas,
            'judul' => 'Campaign disetujui',
            'pesan' => 'Campaign "' . $campaign->judul . '" telah disetujui dan sekarang aktif.',
            'tipe' => 'campaign_disetujui',
            'related_campaign_id' => $id,
        ]);

        $this->auditLog($request, 'APPROVE', 'Menyetujui campaign "' . $campaign->judul . '" (ID: ' . $id . ')');
        $this->bustCommunityProfileCache($campaign->id_komunitas);

        return ApiResponse::success(null, 'Campaign berhasil disetujui.');
    }

    public function rejectCampaign(Request $request, int $id)
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:3'],
        ]);

        $campaign = DB::table('campaign')->where('id_campaign', $id)->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        if ($campaign->status !== 'menunggu_review') {
            return ApiResponse::error('Campaign sudah direview sebelumnya.', 409);
        }

        DB::table('campaign')
            ->where('id_campaign', $id)
            ->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $validated['alasan_penolakan'],
                'direview_oleh' => $request->user()->id_user,
                ]);

        // Notifikasi ke komunitas pemilik campaign
        Notifikasi::kirim([
            'id_penerima_komunitas' => $campaign->id_komunitas,
            'judul' => 'Campaign ditolak',
            'pesan' => 'Campaign "' . $campaign->judul . '" ditolak. Alasan: ' . $validated['alasan_penolakan'],
            'tipe' => 'campaign_ditolak',
            'related_campaign_id' => $id,
        ]);

        $this->auditLog($request, 'REJECT', 'Menolak campaign "' . $campaign->judul . '" (ID: ' . $id . ') — Alasan: ' . $validated['alasan_penolakan']);
        $this->bustCommunityProfileCache($campaign->id_komunitas);

        return ApiResponse::success(null, 'Campaign berhasil ditolak.');
    }

    public function campaignReviewHistory(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('users as u', 'u.id_user', '=', 'c.direview_oleh')
            ->select(
                'c.id_campaign',
                'c.judul',
                'c.status',
                'c.alasan_penolakan',
                'c.updated_at as tanggal_review',
                'k.nama_lembaga',
                'u.username as reviewer'
            )
            ->whereIn('c.status', ['aktif', 'ditolak', 'nonaktif', 'selesai', 'ditutup_permanen']);

        if ($request->filled('status')) {
            $query->where('c.status', $request->query('status'));
        }

        $data = $query->orderByDesc('c.updated_at')->paginate($perPage);

        return ApiResponse::success($data, 'Riwayat review campaign berhasil dimuat.');
    }

    public function disbursementList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $status = $request->query('status', 'menunggu_review');

        $query = DB::table('pencairan_dana as pd')
            ->join('campaign as c', 'c.id_campaign', '=', 'pd.id_campaign')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'pd.id_komunitas')
            ->select(
                'pd.id_pencairan',
                'pd.id_campaign',
                'pd.id_komunitas',
                'pd.urutan_ke',
                'pd.nominal_diajukan',
                'pd.nominal_disetujui',
                'pd.status',
                'pd.alasan_penolakan',
                'pd.tanggal_pengajuan',
                'pd.tanggal_keputusan',
                'pd.url_proposal',
                'pd.nama_bank_tujuan',
                'pd.nomor_rekening_tujuan',
                'c.judul as judul_campaign',
                'k.nama_lembaga'
            );

        if ($status) {
            $query->where('pd.status', $status);
        }

        $data = $query->orderByDesc('pd.tanggal_pengajuan')->paginate($perPage);

        return ApiResponse::success($data, 'Daftar pengajuan pencairan berhasil dimuat.');
    }

    public function approveDisbursement(Request $request, int $id)
    {
        try {
            DB::statement('CALL sp_review_withdrawal(?, ?, ?, ?)', [
                $id,
                'approve',
                $request->user()->id_user,
                null,
            ]);

            $this->auditLog($request, 'APPROVE_DISBURSEMENT', 'Menyetujui pencairan dana (ID: ' . $id . ')');

            return ApiResponse::success(null, 'Pengajuan pencairan berhasil disetujui.');
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function rejectDisbursement(Request $request, int $id)
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:3'],
        ]);

        try {
            DB::statement('CALL sp_review_withdrawal(?, ?, ?, ?)', [
                $id,
                'reject',
                $request->user()->id_user,
                $validated['alasan_penolakan'],
            ]);

            $this->auditLog($request, 'REJECT_DISBURSEMENT', 'Menolak pencairan dana (ID: ' . $id . ') — Alasan: ' . $validated['alasan_penolakan']);

            return ApiResponse::success(null, 'Pengajuan pencairan berhasil ditolak.');
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    public function disbursementHistory(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = DB::table('pencairan_dana as pd')
            ->join('campaign as c', 'c.id_campaign', '=', 'pd.id_campaign')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'pd.id_komunitas')
            ->leftJoin('users as u', 'u.id_user', '=', 'pd.direview_oleh')
            ->select(
                'pd.id_pencairan',
                'pd.urutan_ke',
                'pd.nominal_diajukan',
                'pd.nominal_disetujui',
                'pd.status',
                'pd.alasan_penolakan',
                'pd.tanggal_pengajuan',
                'pd.tanggal_keputusan',
                'c.id_campaign',
                'c.judul as judul_campaign',
                'k.id_komunitas',
                'k.nama_lembaga',
                'u.username as reviewer'
            );

        if ($request->filled('status')) {
            $query->where('pd.status', $request->query('status'));
        }

        $data = $query->orderByDesc('pd.tanggal_pengajuan')->paginate($perPage);

        return ApiResponse::success($data, 'Riwayat pencairan dana berhasil dimuat.');
    }

    public function dashboard(Request $request)
    {
        return Cache::remember('superadmin:dashboard', 30, function () use ($request) {
            $summary = DB::table('v_platform_summary_mv')->first();

            $campaignStatusBreakdown = DB::table('campaign')
                ->select('status', DB::raw('COUNT(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $recentDonations = DB::table('v_donation_transactions')
                ->where('status_pembayaran', 'berhasil')
                ->orderByDesc('tanggal_transaksi')
                ->limit(10)
                ->get();

            $recentCampaigns = DB::table('campaign as c')
                ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
                ->select(
                    'c.id_campaign',
                    'c.judul',
                    'c.status',
                    'c.dana_terkumpul',
                    'c.target_dana',
                    'c.created_at',
                    'k.nama_lembaga'
                )
                ->orderByDesc('c.created_at')
                ->limit(10)
                ->get();

            return ApiResponse::success([
                'summary' => $summary,
                'campaign_status_breakdown' => $campaignStatusBreakdown,
                'recent_donations' => $recentDonations,
                'recent_campaigns' => $recentCampaigns,
            ], 'Dashboard superadmin berhasil dimuat.');
        });
    }

    public function dashboardStatistics(Request $request)
    {
        $days = min((int) $request->query('days', 30), 365);

        return Cache::remember("superadmin:stats:days={$days}", 60, function () use ($days) {
            $dailyStats = DB::table('v_platform_analytics')
                ->orderByDesc('tanggal')
                ->limit($days)
                ->get();

            $topCampaigns = DB::table('campaign as c')
                ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
                ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
                ->select(
                    'c.id_campaign',
                    'c.judul',
                    'c.dana_terkumpul',
                    'c.target_dana',
                    'c.status',
                    'k.nama_lembaga',
                    'kc.nama_kategori'
                )
                ->where('c.status', 'aktif')
                ->orderByDesc('c.dana_terkumpul')
                ->limit(5)
                ->get();

            $topCommunities = DB::table('komunitas as k')
                ->leftJoin('campaign as c', 'c.id_komunitas', '=', 'k.id_komunitas')
                ->select(
                    'k.id_komunitas',
                    'k.nama_lembaga',
                    DB::raw('COALESCE(SUM(c.dana_terkumpul), 0) as total_dana'),
                    DB::raw('COUNT(c.id_campaign) as total_campaign')
                )
                ->whereNull('k.deleted_at')
                ->groupBy('k.id_komunitas', 'k.nama_lembaga')
                ->orderByDesc('total_dana')
                ->limit(5)
                ->get();

            return ApiResponse::success([
                'daily_stats' => $dailyStats,
                'top_campaigns' => $topCampaigns,
                'top_communities' => $topCommunities,
            ], 'Statistik dashboard berhasil dimuat.');
        });
    }

    public function dashboardActivities(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 20), 100);

        $activities = DB::table('v_recent_platform_activities')
            ->orderByDesc('waktu_aktivitas')
            ->limit($perPage)
            ->get();

        return ApiResponse::success($activities, 'Aktivitas dashboard berhasil dimuat.');
    }

    public function platformAnalytics(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-t'));

        return Cache::remember("superadmin:analytics:{$startDate}:{$endDate}", 120, function () use ($startDate, $endDate) {
            $platformData = DB::table('v_platform_analytics')
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->orderBy('tanggal')
                ->get();

            $financialReport = DB::table('v_financial_report')
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate . ' 23:59:59'])
                ->orderBy('tanggal_transaksi')
                ->limit(500)
                ->get();

            $financialSummary = DB::table('v_financial_report')
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate . ' 23:59:59'])
                ->select(
                    DB::raw("COALESCE(SUM(nominal_donasi), 0) as total_donasi"),
                    DB::raw("COALESCE(SUM(nominal_potongan_platform), 0) as total_potongan"),
                    DB::raw("COALESCE(SUM(nominal_pencairan), 0) as total_pencairan"),
                    DB::raw("COUNT(id_donasi) as jumlah_donasi"),
                    DB::raw("COUNT(id_pencairan) FILTER (WHERE id_pencairan IS NOT NULL) as jumlah_pencairan")
                )
                ->first();

            $saldoAkhir = DB::selectOne('SELECT fn_hitung_saldo_platform(?, ?) as saldo_akhir', [$startDate, $endDate]);

            $totalCampaign = DB::table('campaign')->count();
        $successfulCampaigns = DB::table('campaign')
            ->where('status', 'selesai')
            ->whereColumn('dana_terkumpul', '>=', 'target_dana')
            ->count();

        $kategoriDistribution = DB::table('campaign as c')
            ->join('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->select('kc.nama_kategori', DB::raw('COUNT(c.id_campaign) as total'))
            ->groupBy('kc.nama_kategori')
            ->orderByDesc('total')
            ->get();

        return ApiResponse::success([
            'platform_daily' => $platformData,
            'financial_report' => $financialReport,
            'financial_summary' => $financialSummary,
            'saldo_akhir' => $saldoAkhir->saldo_akhir ?? 0,
            'campaign_success_rate' => $totalCampaign > 0 ? round(($successfulCampaigns / $totalCampaign) * 100, 2) : 0,
            'category_distribution' => $kategoriDistribution,
        ], 'Analitik platform berhasil dimuat.');
        });
    }

    // ========== KELOLA DONATUR ==========

    public function donorList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $search = $request->query('search');

        $query = DB::table('users')
            ->select(
                'id_user',
                'username',
                'nama_lengkap',
                'email',
                'foto_profil_url',
                'nomor_telepon',
                'is_active',
                'is_verified',
                'created_at',
                DB::raw('(SELECT COALESCE(COUNT(*), 0) FROM donasi WHERE id_user = users.id_user AND status_pembayaran = \'berhasil\') as total_transaksi_donasi'),
                DB::raw('(SELECT COALESCE(SUM(nominal), 0) FROM donasi WHERE id_user = users.id_user AND status_pembayaran = \'berhasil\') as total_nominal_donasi')
            )
            ->where('role', 'DONATUR')
            ->whereNull('deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('username', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $data = $query->orderByDesc('created_at')->paginate($perPage);

        return ApiResponse::success($data, 'Daftar donatur berhasil dimuat.');
    }

    public function donorDetail(int $id)
    {
        $donor = DB::table('users')
            ->select(
                'id_user',
                'username',
                'nama_lengkap',
                'email',
                'foto_profil_url',
                'nomor_telepon',
                'jenis_kelamin',
                'tanggal_lahir',
                'kode_wilayah',
                'is_active',
                'is_verified',
                'created_at',
                'updated_at'
            )
            ->where('id_user', $id)
            ->where('role', 'DONATUR')
            ->whereNull('deleted_at')
            ->first();

        if (!$donor) {
            return ApiResponse::error('Donatur tidak ditemukan.', 404);
        }

        $donationHistory = DB::table('v_user_donation_history')
            ->where('id_user', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return ApiResponse::success([
            'donor' => $donor,
            'donation_history' => $donationHistory,
        ], 'Detail donatur berhasil dimuat.');
    }

    public function donorToggleStatus(Request $request, int $id)
    {
        try {
            DB::statement('CALL sp_update_donor_status(?, ?, ?)', [
                $id,
                $request->input('is_active', true),
                $request->user()->id_user,
            ]);

            return ApiResponse::success(null, 'Status donatur berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    // ========== KELOLA KOMUNITAS ==========

    public function communityList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $search = $request->query('search');

        $query = DB::table('komunitas as k')
            ->leftJoin('users as u', 'u.id_user', '=', 'k.id_user')
            ->leftJoin('jenis_lembaga as jl', 'jl.id_jenis', '=', 'k.id_jenis_lembaga')
            ->select(
                'k.id_komunitas',
                'k.nama_lembaga',
                'jl.nama_jenis as jenis_lembaga',
                'u.email',
                'k.nomor_kontak',
                'k.status',
                'k.created_at',
                DB::raw('(SELECT COUNT(*) FROM campaign WHERE id_komunitas = k.id_komunitas) as total_campaign'),
                DB::raw('(SELECT COUNT(*) FROM campaign WHERE id_komunitas = k.id_komunitas AND status = \'aktif\') as total_campaign_aktif'),
                DB::raw('(SELECT COALESCE(SUM(d.nominal), 0) FROM donasi d JOIN campaign c ON c.id_campaign = d.id_campaign WHERE c.id_komunitas = k.id_komunitas AND d.status_pembayaran = \'berhasil\') as total_donasi_diterima')
            )
            ->whereNull('k.deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('k.nama_lembaga', 'ilike', "%{$search}%")
                  ->orWhere('u.email', 'ilike', "%{$search}%")
                  ->orWhere('k.nomor_kontak', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('k.status', $request->query('status'));
        }

        $data = $query->orderByDesc('k.created_at')->paginate($perPage);

        return ApiResponse::success($data, 'Daftar komunitas berhasil dimuat.');
    }

    public function communityDetail(int $id)
    {
        $community = DB::table('komunitas as k')
            ->leftJoin('users as u', 'u.id_user', '=', 'k.id_user')
            ->leftJoin('jenis_lembaga as jl', 'jl.id_jenis', '=', 'k.id_jenis_lembaga')
            ->leftJoin('wilayah as w', 'w.kode', '=', 'k.kode_wilayah')
            ->select('k.*', 'u.email', 'u.username', 'jl.nama_jenis as jenis_lembaga', 'w.nama as nama_wilayah')
            ->where('k.id_komunitas', $id)
            ->first();

        if (!$community) {
            return ApiResponse::error('Komunitas tidak ditemukan.', 404);
        }

        $campaigns = DB::table('campaign')
            ->where('id_komunitas', $id)
            ->orderByDesc('created_at')
            ->get();

        $documents = DB::table('dokumen_komunitas as dk')
            ->leftJoin('jenis_dokumen as jd', 'jd.id_jenis_dok', '=', 'dk.id_jenis_dok')
            ->select('dk.*', 'jd.nama_dokumen')
            ->where('dk.id_komunitas', $id)
            ->get();

        return ApiResponse::success([
            'community' => $community,
            'campaigns' => $campaigns,
            'documents' => $documents,
        ], 'Detail komunitas berhasil dimuat.');
    }

    public function communityUpdateStatus(Request $request, int $id)
    {
        try {
            DB::statement('CALL sp_update_community_status(?, ?, ?)', [
                $id,
                $request->input('is_active', true),
                $request->user()->id_user,
            ]);

            return ApiResponse::success(null, 'Status komunitas berhasil diperbarui.');
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }

    // ========== TEMPLATE DOKUMEN ==========

    public function documentTemplateList(Request $request)
    {
        $data = DB::table('jenis_dokumen')
            ->orderBy('nama_dokumen')
            ->get();

        return ApiResponse::success($data, 'Daftar template dokumen berhasil dimuat.');
    }

    public function documentTemplateStore(Request $request)
    {
        $validated = $request->validate([
            'nama_dokumen' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'wajib_untuk_jenis_lembaga' => ['nullable', 'string', 'max:50'],
            'is_opsional' => ['boolean'],
        ]);

        $maxId = DB::table('jenis_dokumen')->max('id_jenis_dok') ?? 0;

        $data = DB::table('jenis_dokumen')->insert([
            'id_jenis_dok' => $maxId + 1,
            'nama_dokumen' => $validated['nama_dokumen'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'wajib_untuk_jenis_lembaga' => $validated['wajib_untuk_jenis_lembaga'] ?? null,
            'is_opsional' => $validated['is_opsional'] ?? false,
        ]);

        return ApiResponse::success($data, 'Template dokumen berhasil ditambahkan.');
    }

    // ========== KATEGORI CAMPAIGN ==========

    public function categoryList(Request $request)
    {
        $data = DB::table('kategori_campaign')
            ->orderBy('nama_kategori')
            ->get();

        return ApiResponse::success($data, 'Daftar kategori campaign berhasil dimuat.');
    }

    public function categoryStore(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $maxId = DB::table('kategori_campaign')->max('id_kategori') ?? 0;

        $id = DB::table('kategori_campaign')->insertGetId([
            'id_kategori' => $maxId + 1,
            'nama_kategori' => $validated['nama_kategori'],
            'deskripsi' => $validated['deskripsi'] ?? null,
            'is_active' => true,
        ], 'id_kategori');

        return ApiResponse::success(['id_kategori' => $maxId + 1], 'Kategori campaign berhasil ditambahkan.');
    }

    public function categoryUpdate(Request $request, int $id)
    {
        $category = DB::table('kategori_campaign')->where('id_kategori', $id)->first();

        if (!$category) {
            return ApiResponse::error('Kategori tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        DB::table('kategori_campaign')
            ->where('id_kategori', $id)
            ->update($validated);

        return ApiResponse::success(null, 'Kategori campaign berhasil diperbarui.');
    }

    public function categoryToggleStatus(Request $request, int $id)
    {
        $category = DB::table('kategori_campaign')->where('id_kategori', $id)->first();

        if (!$category) {
            return ApiResponse::error('Kategori tidak ditemukan.', 404);
        }

        DB::table('kategori_campaign')
            ->where('id_kategori', $id)
            ->update(['is_active' => !$category->is_active]);

        return ApiResponse::success(null, 'Status kategori campaign berhasil diperbarui.');
    }

    public function categoryDelete(int $id)
    {
        return response()->json([
            'status' => 'error',
            'data' => null,
            'message' => 'Tidak dapat menghapus kategori. Hanya dapat menonaktifkan melalui endpoint status.',
            'errors' => ['method' => ['DELETE tidak diizinkan.']],
        ], 405);
    }

    // ========== EXPORT LAPORAN KEUANGAN ==========

    public function financialExport(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $format = $request->input('format', 'json');

        $startDate = "{$year}-{$month}-01";
        $endDate = date('Y-m-t', strtotime($startDate));

        $reportData = DB::table('v_financial_report')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate . ' 23:59:59'])
            ->orderBy('tanggal_transaksi')
            ->get();

        $saldoAkhir = DB::selectOne('SELECT fn_hitung_saldo_platform(?, ?) as saldo_akhir', [$startDate, $endDate]);

        if ($format === 'csv') {
            $csv = "tanggal_transaksi,nama_donatur,judul_campaign,nama_komunitas,nominal_donasi,nominal_potongan_platform,status_donasi,nominal_pencairan,status_pencairan\n";
            foreach ($reportData as $row) {
                $row = (array) $row;
                $csv .= implode(',', array_map(function ($val) {
                    return '"' . str_replace('"', '""', $val ?? '') . '"';
                }, $row)) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=financial_report_{$month}_{$year}.csv",
            ]);
        }

        return ApiResponse::success([
            'periode' => ['month' => $month, 'year' => $year],
            'saldo_akhir' => $saldoAkhir->saldo_akhir ?? 0,
            'transactions' => $reportData,
        ], 'Laporan keuangan berhasil dimuat.');
    }

    // ========== REVIEW PENDAFTARAN KOMUNITAS ==========

    public function registrationList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $data = DB::table('komunitas as k')
            ->join('users as u', 'u.id_user', '=', 'k.id_user')
            ->leftJoin('jenis_lembaga as jl', 'jl.id_jenis', '=', 'k.id_jenis_lembaga')
            ->leftJoin('wilayah as w', 'w.kode', '=', 'k.kode_wilayah')
            ->select(
                'k.id_komunitas',
                'k.nama_lembaga',
                'k.deskripsi',
                'k.nomor_kontak',
                'k.status',
                'k.created_at',
                'u.email',
                'u.nama_lengkap as nama_pengurus',
                'jl.nama_jenis as jenis_lembaga',
                'w.nama as nama_wilayah'
            )
            ->where('k.status', 'menunggu')
            ->orderByDesc('k.created_at')
            ->paginate($perPage);

        return ApiResponse::success($data, 'Daftar pendaftaran komunitas berhasil dimuat.');
    }

    public function registrationDetail(int $id)
    {
        $registration = DB::table('komunitas as k')
            ->join('users as u', 'u.id_user', '=', 'k.id_user')
            ->leftJoin('jenis_lembaga as jl', 'jl.id_jenis', '=', 'k.id_jenis_lembaga')
            ->leftJoin('wilayah as w', 'w.kode', '=', 'k.kode_wilayah')
            ->select('k.*', 'u.email', 'u.nama_lengkap', 'u.username', 'jl.nama_jenis as jenis_lembaga', 'w.nama as nama_wilayah')
            ->where('k.id_komunitas', $id)
            ->first();

        if (!$registration) {
            return ApiResponse::error('Pendaftaran tidak ditemukan.', 404);
        }

        $documents = DB::table('dokumen_komunitas as dk')
            ->leftJoin('jenis_dokumen as jd', 'jd.id_jenis_dok', '=', 'dk.id_jenis_dok')
            ->select('dk.*', 'jd.nama_dokumen')
            ->where('dk.id_komunitas', $id)
            ->get();

        return ApiResponse::success([
            'registration' => $registration,
            'documents' => $documents,
        ], 'Detail pendaftaran komunitas berhasil dimuat.');
    }

    public function registrationApprove(Request $request, int $id)
    {
        $komunitas = DB::table('komunitas')->where('id_komunitas', $id)->first();

        if (!$komunitas) {
            return ApiResponse::error('Pendaftaran tidak ditemukan.', 404);
        }

        if ($komunitas->status !== 'menunggu') {
            return ApiResponse::error('Pendaftaran sudah direview sebelumnya.', 409);
        }

        DB::table('komunitas')
            ->where('id_komunitas', $id)
            ->update([
                'status' => 'aktif',
                'alasan_penolakan' => null,
                'direview_oleh' => $request->user()->id_user,
                ]);

        DB::table('users')
            ->where('id_user', $komunitas->id_user)
            ->update(['is_verified' => true]);

        return ApiResponse::success(null, 'Pendaftaran komunitas berhasil disetujui.');
    }

    public function registrationReject(Request $request, int $id)
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:3'],
        ]);

        $komunitas = DB::table('komunitas')->where('id_komunitas', $id)->first();

        if (!$komunitas) {
            return ApiResponse::error('Pendaftaran tidak ditemukan.', 404);
        }

        if ($komunitas->status !== 'menunggu') {
            return ApiResponse::error('Pendaftaran sudah direview sebelumnya.', 409);
        }

        DB::table('komunitas')
            ->where('id_komunitas', $id)
            ->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $validated['alasan_penolakan'],
                'direview_oleh' => $request->user()->id_user,
                ]);

        return ApiResponse::success(null, 'Pendaftaran komunitas berhasil ditolak.');
    }

    public function registrationHistory(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = DB::table('komunitas as k')
            ->join('users as u', 'u.id_user', '=', 'k.id_user')
            ->leftJoin('jenis_lembaga as jl', 'jl.id_jenis', '=', 'k.id_jenis_lembaga')
            ->leftJoin('users as reviewer', 'reviewer.id_user', '=', 'k.direview_oleh')
            ->select(
                'k.id_komunitas',
                'k.nama_lembaga',
                'k.status',
                'k.alasan_penolakan',
                'k.updated_at as tanggal_review',
                'u.email',
                'jl.nama_jenis as jenis_lembaga',
                'reviewer.username as reviewer'
            )
            ->whereIn('k.status', ['aktif', 'ditolak']);

        if ($request->filled('status')) {
            $query->where('k.status', $request->query('status'));
        }

        $data = $query->orderByDesc('k.updated_at')->paginate($perPage);

        return ApiResponse::success($data, 'Riwayat review pendaftaran berhasil dimuat.');
    }

    // ========== REVIEW PERUBAHAN REKENING ==========

    public function bankAccountChangeList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $status = $request->query('status', 'menunggu');

        $query = DB::table('verifikasi_rekening as vr')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'vr.id_komunitas')
            ->select(
                'vr.*',
                'k.nama_lembaga',
                'k.nama_bank as nama_bank_lama',
                'k.nomor_rekening as nomor_rekening_lama'
            );

        if ($status) {
            $query->where('vr.status', $status);
        }

        $data = $query->orderByDesc('vr.created_at')->paginate($perPage);

        return ApiResponse::success($data, 'Daftar perubahan rekening berhasil dimuat.');
    }

    public function bankAccountChangeDetail(int $id)
    {
        $change = DB::table('verifikasi_rekening as vr')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'vr.id_komunitas')
            ->select('vr.*', 'k.nama_lembaga', 'k.nama_bank as nama_bank_lama', 'k.nomor_rekening as nomor_rekening_lama')
            ->where('vr.id_verif', $id)
            ->first();

        if (!$change) {
            return ApiResponse::error('Perubahan rekening tidak ditemukan.', 404);
        }

        return ApiResponse::success($change, 'Detail perubahan rekening berhasil dimuat.');
    }

    public function bankAccountChangeApprove(Request $request, int $id)
    {
        $change = DB::table('verifikasi_rekening')->where('id_verif', $id)->first();

        if (!$change) {
            return ApiResponse::error('Perubahan rekening tidak ditemukan.', 404);
        }

        if ($change->status !== 'menunggu') {
            return ApiResponse::error('Perubahan rekening sudah direview sebelumnya.', 409);
        }

        DB::transaction(function () use ($change, $request, $id) {
            DB::table('verifikasi_rekening')
                ->where('id_verif', $id)
                ->update([
                    'status' => 'disetujui',
                    'direview_oleh' => $request->user()->id_user,
                    'tanggal_keputusan' => now(),
                ]);

            DB::table('komunitas')
                ->where('id_komunitas', $change->id_komunitas)
                ->update([
                    'nama_bank' => $change->nama_bank_baru,
                    'nomor_rekening' => $change->nomor_rekening_baru,
                    'foto_buku_rekening_url' => $change->foto_buku_rekening_url,
                ]);
        });

        return ApiResponse::success(null, 'Perubahan rekening berhasil disetujui.');
    }

    public function bankAccountChangeReject(Request $request, int $id)
    {
        $validated = $request->validate([
            'alasan_penolakan' => ['required', 'string', 'min:3'],
        ]);

        $change = DB::table('verifikasi_rekening')->where('id_verif', $id)->first();

        if (!$change) {
            return ApiResponse::error('Perubahan rekening tidak ditemukan.', 404);
        }

        if ($change->status !== 'menunggu') {
            return ApiResponse::error('Perubahan rekening sudah direview sebelumnya.', 409);
        }

        DB::table('verifikasi_rekening')
            ->where('id_verif', $id)
            ->update([
                'status' => 'ditolak',
                'alasan_penolakan' => $validated['alasan_penolakan'],
                'direview_oleh' => $request->user()->id_user,
                'tanggal_keputusan' => now(),
            ]);

        return ApiResponse::success(null, 'Perubahan rekening berhasil ditolak.');
    }

    public function bankAccountChangeHistory(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = DB::table('verifikasi_rekening as vr')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'vr.id_komunitas')
            ->leftJoin('users as u', 'u.id_user', '=', 'vr.direview_oleh')
            ->select('vr.*', 'k.nama_lembaga', 'u.username as reviewer')
            ->whereIn('vr.status', ['disetujui', 'ditolak']);

        if ($request->filled('status')) {
            $query->where('vr.status', $request->query('status'));
        }

        $data = $query->orderByDesc('vr.tanggal_keputusan')->paginate($perPage);

        return ApiResponse::success($data, 'Riwayat perubahan rekening berhasil dimuat.');
    }

    // ========== DETAIL PENCAIRAN DANA ==========

    public function disbursementDetail(int $id)
    {
        $disbursement = DB::table('pencairan_dana as pd')
            ->join('campaign as c', 'c.id_campaign', '=', 'pd.id_campaign')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'pd.id_komunitas')
            ->leftJoin('laporan_penggunaan_dana as lpd', 'lpd.id_pencairan', '=', 'pd.id_pencairan')
            ->select(
                'pd.*',
                'c.judul as judul_campaign',
                'c.dana_terkumpul',
                'c.saldo_tersedia',
                'k.nama_lembaga',
                'lpd.id_laporan',
                'lpd.deskripsi_penggunaan',
                'lpd.total_realisasi',
                'lpd.file_dokumentasi_url',
                'lpd.status_verifikasi as status_laporan'
            )
            ->where('pd.id_pencairan', $id)
            ->first();

        if (!$disbursement) {
            return ApiResponse::error('Pencairan dana tidak ditemukan.', 404);
        }

        return ApiResponse::success($disbursement, 'Detail pencairan dana berhasil dimuat.');
    }

    // ========== LAPORAN CAMPAIGN ==========

    public function campaignReportList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->select(
                'c.id_campaign',
                'c.judul',
                'c.status',
                'c.alasan_penolakan',
                'c.target_dana',
                'c.dana_terkumpul',
                'c.created_at',
                'c.updated_at',
                'k.nama_lembaga',
                'kc.nama_kategori'
            )
            ->whereIn('c.status', ['menunggu_review', 'nonaktif', 'ditolak', 'ditutup_permanen']);

        if ($request->filled('status')) {
            $query->where('c.status', $request->query('status'));
        }

        $data = $query->orderByDesc('c.updated_at')->paginate($perPage);

        return ApiResponse::success($data, 'Daftar laporan campaign berhasil dimuat.');
    }

    public function campaignReportDetail(int $id)
    {
        $campaign = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->leftJoin('wilayah as w', 'w.kode', '=', 'c.kode_wilayah')
            ->leftJoin('users as u', 'u.id_user', '=', 'c.direview_oleh')
            ->select('c.*', 'k.nama_lembaga', 'k.nomor_kontak', 'kc.nama_kategori', 'w.nama as nama_wilayah', 'u.username as reviewer')
            ->where('c.id_campaign', $id)
            ->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        return ApiResponse::success($campaign, 'Detail laporan campaign berhasil dimuat.');
    }

    public function campaignReportIgnore(Request $request, int $id)
    {
        $campaign = DB::table('campaign')->where('id_campaign', $id)->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        if ($campaign->status !== 'menunggu_review') {
            return ApiResponse::error('Hanya campaign dengan status menunggu review yang bisa diabaikan.', 409);
        }

        DB::table('campaign')
            ->where('id_campaign', $id)
            ->update([
                'status' => 'aktif',
                'alasan_penolakan' => null,
                'direview_oleh' => $request->user()->id_user,
                ]);

        $this->auditLog($request, 'IGNORE_REPORT', 'Mengabaikan laporan campaign "' . $campaign->judul . '" (ID: ' . $id . ')');
        $this->bustCommunityProfileCache($campaign->id_komunitas);

        return ApiResponse::success(null, 'Laporan campaign diabaikan, campaign diaktifkan.');
    }

    public function campaignDisable(Request $request, int $id)
    {
        $campaign = DB::table('campaign')->where('id_campaign', $id)->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        if ($campaign->status === 'nonaktif') {
            return ApiResponse::error('Campaign sudah nonaktif.', 409);
        }

        DB::table('campaign')
            ->where('id_campaign', $id)
            ->update([
                'status' => 'nonaktif',
                'direview_oleh' => $request->user()->id_user,
                ]);

        // Notifikasi peringatan ke komunitas
        Notifikasi::kirim([
            'id_penerima_komunitas' => $campaign->id_komunitas,
            'judul' => 'Peringatan: Campaign dinonaktifkan',
            'pesan' => 'Campaign "' . $campaign->judul . '" telah dinonaktifkan oleh superadmin. Silakan periksa halaman campaign Anda.',
            'tipe' => 'peringatan',
            'related_campaign_id' => $id,
        ]);

        $this->auditLog($request, 'DISABLE', 'Menonaktifkan campaign "' . $campaign->judul . '" (ID: ' . $id . ')');
        $this->bustCommunityProfileCache($campaign->id_komunitas);

        return ApiResponse::success(null, 'Campaign berhasil dinonaktifkan.');
    }

    // ========== KLARIFIKASI CAMPAIGN ==========

    public function clarificationList(Request $request)
    {
        $perPage = min((int) $request->query('per_page', 15), 100);

        $query = DB::table('campaign as c')
            ->join('komunitas as k', 'k.id_komunitas', '=', 'c.id_komunitas')
            ->leftJoin('kategori_campaign as kc', 'kc.id_kategori', '=', 'c.id_kategori')
            ->select(
                'c.id_campaign',
                'c.judul',
                'c.status',
                'c.alasan_penolakan',
                'c.target_dana',
                'c.dana_terkumpul',
                'c.created_at',
                'c.updated_at',
                'k.nama_lembaga',
                'kc.nama_kategori'
            )
            ->whereIn('c.status', ['nonaktif', 'ditutup_permanen']);

        if ($request->filled('status')) {
            $query->where('c.status', $request->query('status'));
        }

        $data = $query->orderByDesc('c.updated_at')->paginate($perPage);

        return ApiResponse::success($data, 'Daftar klarifikasi campaign berhasil dimuat.');
    }

    public function clarificationDetail(int $id)
    {
        return $this->campaignReportDetail($id);
    }

    public function clarificationReactivate(Request $request, int $id)
    {
        $campaign = DB::table('campaign')->where('id_campaign', $id)->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        if ($campaign->status !== 'nonaktif') {
            return ApiResponse::error('Hanya campaign nonaktif yang bisa diaktifkan kembali.', 409);
        }

        DB::table('campaign')
            ->where('id_campaign', $id)
            ->update([
                'status' => 'aktif',
                'alasan_penolakan' => null,
                'direview_oleh' => $request->user()->id_user,
                ]);

        $this->auditLog($request, 'REACTIVATE', 'Mengaktifkan kembali campaign "' . $campaign->judul . '" (ID: ' . $id . ')');
        $this->bustCommunityProfileCache($campaign->id_komunitas);

        return ApiResponse::success(null, 'Campaign berhasil diaktifkan kembali.');
    }

    public function clarificationClosePermanently(Request $request, int $id)
    {
        $campaign = DB::table('campaign')->where('id_campaign', $id)->first();

        if (!$campaign) {
            return ApiResponse::error('Campaign tidak ditemukan.', 404);
        }

        if ($campaign->status === 'ditutup_permanen') {
            return ApiResponse::error('Campaign sudah ditutup permanen.', 409);
        }

        DB::table('campaign')
            ->where('id_campaign', $id)
            ->update([
                'status' => 'ditutup_permanen',
                'direview_oleh' => $request->user()->id_user,
                ]);

        // Notifikasi peringatan ke komunitas
        Notifikasi::kirim([
            'id_penerima_komunitas' => $campaign->id_komunitas,
            'judul' => 'Peringatan: Campaign ditutup permanen',
            'pesan' => 'Campaign "' . $campaign->judul . '" telah ditutup permanen oleh superadmin.',
            'tipe' => 'peringatan',
            'related_campaign_id' => $id,
        ]);

        $this->auditLog($request, 'CLOSE_PERMANENT', 'Menutup permanen campaign "' . $campaign->judul . '" (ID: ' . $id . ')');
        $this->bustCommunityProfileCache($campaign->id_komunitas);

        return ApiResponse::success(null, 'Campaign berhasil ditutup permanen.');
    }

    // ========== HAPUS POST UPDATE ==========

    public function deleteUpdate(int $updateId)
    {
        $update = DB::table('update_campaign')->where('id_update', $updateId)->first();

        if (!$update) {
            return ApiResponse::error('Update tidak ditemukan.', 404);
        }

        DB::table('foto_update')->where('id_update', $updateId)->delete();
        DB::table('update_campaign')->where('id_update', $updateId)->delete();

        return ApiResponse::success(null, 'Post update berhasil dihapus.');
    }
}