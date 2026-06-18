# Planning: BERBAGIVE - Gap Analysis & Implementation Plan

## Context

Proyek BERBAGIVE adalah platform donasi komunitas berbasis web (Laravel 12 backend + Vue 3 frontend). Analisa ini membandingkan kode yang sudah ada dengan FSD (`docs/FSD_A_3_2024.md`) dan TSD (`docs/TSD_2024_A_3.md`) untuk menentukan apa yang belum dibuat, apa yang belum dikonfigurasi, dan urutan pengerjaan.

---

## Temuan Analisa

### ✅ Yang Sudah Ada

**Backend:**
- 34 Controllers (termasuk Auth, Campaign, Donation, Superadmin, Community, dll)
- 16 Models
- 85+ routes di `routes/api.php`
- 5 Services (DonationService, CampaignService, dll)
- 13 Form Requests (validasi)
- 1 Export class (FinancialReportExport)
- Middleware role-based access (CheckRole)
- Helper ApiResponse

**Frontend:**
- 23 Views (auth 7, campaigns 4, community 2, dashboard 3, donations 3, payments 2, notification 1)
- 26 Components
- 3 Pinia stores (auth, counter, donation)
- 24 Routes di router

---

## Masalah Kritis

### 🔴 KRITIS - Backend: Tidak Ada Migration

**Tidak ada satu pun migration custom** untuk tabel aplikasi. Hanya ada 4 migration default Laravel. Semua tabel seperti `komunitas`, `campaign`, `donasi`, `wilayah`, dll — **belum dibuat via migration**.

TSD mendefinisikan 18+ tabel yang wajib ada:
`wilayah`, `kategori_campaign`, `jenis_lembaga`, `jenis_dokumen`, `komunitas`, `campaign`, `donasi`, `update_campaign`, `pencairan_dana`, `laporan_penggunaan_dana`, `potongan_platform`, `verifikasi_rekening`, `notifikasi`, `dokumen_komunitas`, `template_dokumen`, `follow_komunitas`, `audit_logs`, dan perluasan tabel `users`.

### 🔴 KRITIS - Frontend: Views Penting Belum Ada

| View | Status | Keterangan |
|------|--------|-----------|
| `auth/RegisterKomunitas.vue` | ❌ BELUM ADA | Form registrasi komunitas |
| `community/CommunityDashboard.vue` | ❌ BELUM ADA | Dashboard komunitas |
| `campaigns/CampaignSubmitPage.vue` | ❌ BELUM ADA | Form pengajuan kampanye |
| `dashboard/WithdrawalRequestView.vue` | ❌ BELUM ADA | Pengajuan pencairan dana |
| `views/admin/` (direktori) | ❌ BELUM ADA | Semua halaman admin |
| `views/profile/` (direktori) | ❌ BELUM ADA | Edit profil user & komunitas |
| `views/search/` (direktori) | ❌ BELUM ADA | Hasil pencarian |

### 🟠 BELUM DIKONFIGURASI

**Backend:**
- [ ] Database belum berjalan (tidak ada migration = tidak ada tabel)
- [ ] Redis belum dikonfigurasi (TSD mensyaratkan Redis untuk cache/session)
- [ ] Mail (Mailtrap) belum terkonfigurasi dengan benar di `.env`
- [ ] File upload storage belum dikonfigurasi (`storage:link`)
- [ ] Rate limiting login (5 gagal = lock 15 menit) — belum ada throttle middleware
- [ ] Database Functions (5), Views (10), Stored Procedures (3) dari TSD belum dibuat

**Frontend:**
- [ ] `.env` / `VITE_API_URL` belum dikonfigurasi
- [ ] Route guards (auth + role) belum ada di `router/index.js`
- [ ] Auth store tidak punya role checking / computed getters
- [ ] Axios tidak punya response interceptor (handle 401/403)
- [ ] Tidak ada store untuk community, campaign, notification

### 🟡 BELUM DIIMPLEMENTASI (Fitur)

**Backend:**
- [ ] Payment gateway integration (QRIS, GoPay, OVO, ShopeePay, transfer bank)
- [ ] PDF receipt generation (`donations/{id}/receipt`)
- [ ] Email verification + password reset emails (template)
- [ ] Auto-close campaign via scheduled command (`sp_close_expired_campaigns`)
- [ ] Platform fee auto-deduction (`sp_apply_platform_fee`)
- [ ] Notification auto-archive (`sp_archive_old_notifications`)
- [ ] Soft deletes pada tabel audit-related

**Frontend:**
- [ ] Payment flow (QRIS display, VA number, status polling)
- [ ] File upload (foto profil, dokumen komunitas, RAB kampanye)
- [ ] Dashboard komunitas dengan chart donasi
- [ ] Superadmin: halaman manajemen donor, komunitas, kategori, template dokumen
- [ ] Follow/unfollow komunitas
- [ ] Report kampanye/update

---

## Planning Implementasi (Urutan Prioritas)

### FASE 1: Fondasi Database (WAJIB PERTAMA)
> Tanpa ini, tidak ada yang bisa berjalan.

1. **Buat migration untuk semua tabel aplikasi** (`database/migrations/`)
   - `wilayah`, `jenis_lembaga`, `jenis_dokumen`, `kategori_campaign`
   - Perluasan `users` (tambah kolom role, komunitas_id, wilayah_id, dll)
   - `komunitas`, `dokumen_komunitas`, `template_dokumen`
   - `campaign`, `update_campaign`, `foto_update`
   - `donasi`, `pencairan_dana`, `laporan_penggunaan_dana`, `potongan_platform`
   - `verifikasi_rekening`, `follow_komunitas`
   - `notifikasi`, `audit_logs`

2. **Buat Database Seeders** untuk data master:
   - Wilayah (provinsi/kota awal)
   - Kategori campaign default
   - Jenis lembaga & dokumen
   - Superadmin default account

3. **Buat DB Functions, Views, Stored Procedures** (SQL raw via migration)
   - 5 functions: `fn_campaign_progress`, `fn_available_balance`, dll
   - 10 views: `v_campaign_public`, `v_campaign_detail`, dll
   - 3 stored procedures: `sp_close_expired_campaigns`, dll

### FASE 2: Konfigurasi Environment

**Backend `.env`:**
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=berbagive
DB_USERNAME=...
DB_PASSWORD=...

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_DRIVER=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
...

SANCTUM_STATEFUL_DOMAINS=localhost:5173

FILESYSTEM_DISK=local
APP_URL=http://localhost:8000
```

**Frontend `.env`:**
```
VITE_API_URL=http://localhost:8000/api/v1
```

Jalankan: `php artisan storage:link`, `php artisan migrate --seed`

### FASE 3: Frontend Infrastructure

**File: `frontend/src/router/index.js`**
- Tambah navigation guards (cek token + role)
- Redirect ke `/login` jika belum auth
- Redirect 403 jika role tidak sesuai

**File: `frontend/src/stores/auth.js`**
- Tambah computed: `isLoggedIn`, `userRole`, `isAdmin`, `isCommunity`, `isDonor`
- Tambah action: `fetchMe()` untuk load user data
- Simpan role di state

**File: `frontend/src/api/axios.js`**
- Tambah response interceptor: handle 401 (redirect login), 403 (redirect 403 page)

**Stores baru yang perlu dibuat:**
- `frontend/src/stores/notification.js`
- `frontend/src/stores/campaign.js`

### FASE 4: Views yang Belum Ada (Frontend)

Prioritas berdasarkan user journey:

**4a. Registrasi & Auth:**
- `views/auth/RegisterKomunitas.vue` — form multi-step (data lembaga + dokumen)

**4b. Profil:**
- `views/profile/UserProfilePage.vue` — edit profil donor
- `views/profile/CommunityProfileEdit.vue` — edit profil komunitas
- `views/profile/BankAccountPage.vue` — manajemen rekening komunitas

**4c. Komunitas:**
- `views/community/CommunityDashboard.vue` — statistik + chart donasi

**4d. Kampanye (Community):**
- `views/campaigns/CampaignSubmitPage.vue` — form pengajuan kampanye + upload RAB
- `views/campaigns/CampaignUpdatePage.vue` — posting update kampanye

**4e. Pencairan:**
- `views/dashboard/WithdrawalRequestView.vue` — form pengajuan pencairan dana

**4f. Pencarian:**
- `views/search/CampaignSearchPage.vue` — hasil pencarian kampanye
- `views/search/CommunitySearchPage.vue` — hasil pencarian komunitas

**4g. Admin (Superadmin):**
- `views/admin/DonorManagementView.vue`
- `views/admin/CommunityManagementView.vue`
- `views/admin/CommunityRegistrationView.vue`
- `views/admin/CampaignReviewView.vue`
- `views/admin/BankVerificationView.vue`
- `views/admin/CategoryManagementView.vue`
- `views/admin/DocumentTemplateView.vue`
- `views/admin/FinancialReportView.vue`
- `views/admin/AuditLogView.vue`

### FASE 5: Fitur Lanjutan

**Payment Gateway:**
- Integrasi Midtrans / payment provider
- Endpoint `PATCH /donations/{id}/payment-status` harus terkoneksi ke callback
- Frontend: `PaymentCheckoutPage.vue` perlu real payment flow

**Scheduled Tasks (Backend):**
```php
// app/Console/Kernel.php atau routes/console.php
Schedule::call(fn() => DB::statement('CALL sp_close_expired_campaigns()'))->daily();
Schedule::call(fn() => DB::statement('CALL sp_archive_old_notifications()'))->daily();
```

**PDF Receipt:**
- Install `barryvdh/laravel-dompdf`
- Implement di `DonationController::receipt()`

**Rate Limiting:**
- Tambah throttle `5,15` pada route `/auth/login`
- Implementasi lockout response message

---

## File Kritis yang Perlu Dimodifikasi

| File | Perubahan |
|------|-----------|
| `backend/routes/api.php` | Tambah throttle middleware login, cek endpoint konsistensi |
| `backend/database/migrations/` | Buat 18+ migration baru |
| `backend/database/seeders/DatabaseSeeder.php` | Tambah seeder data master |
| `frontend/src/router/index.js` | Tambah route guards |
| `frontend/src/stores/auth.js` | Tambah role & helper getters |
| `frontend/src/api/axios.js` | Tambah response interceptor |
| `backend/.env` | Konfigurasi DB, Redis, Mail, Sanctum |
| `frontend/.env` | Set VITE_API_URL |

---

## Verifikasi End-to-End

Setelah implementasi, test flow berikut:

1. **Auth Flow**: Register donor → verify email → login → lihat profil
2. **Donor Flow**: Browse kampanye → donasi → bayar → cek riwayat → download receipt
3. **Community Flow**: Register komunitas → approval superadmin → ajukan kampanye → posting update → request pencairan
4. **Superadmin Flow**: Login → approve registrasi → review kampanye → approve pencairan → export laporan
5. **Notifikasi**: Setiap aksi approval/rejection memunculkan notifikasi yang benar
6. **Search**: Pencarian kampanye dengan filter kategori & lokasi

---

## Estimasi Prioritas

| Fase | Prioritas | Kompleksitas |
|------|-----------|-------------|
| Fase 1: Database Migrations | 🔴 KRITIS | Tinggi |
| Fase 2: Environment Config | 🔴 KRITIS | Rendah |
| Fase 3: Frontend Infrastructure | 🟠 TINGGI | Sedang |
| Fase 4a-4d: Views Core | 🟠 TINGGI | Tinggi |
| Fase 4e-4g: Views Admin | 🟡 SEDANG | Tinggi |
| Fase 5: Fitur Lanjutan | 🟢 RENDAH | Sangat Tinggi |
