# BAB III — IMPLEMENTASI

## 3.X Arsitektur API

### 3.X.1 Pengenalan RESTful Architecture

Arsitektur API Berbagive dirancang mengikuti prinsip **REST** (*Representational State Transfer*), yaitu gaya arsitektur perangkat lunak yang memanfaatkan protokol HTTP secara penuh untuk komunikasi antara *client* (frontend) dan *server* (backend). Penerapan REST pada Berbagive diwujudkan melalui sejumlah karakteristik sebagai berikut:

#### a. Resource-Oriented Design (Desain Berbasis Sumber Daya)

Seluruh endpoint API Berbagive menggunakan kata benda (*noun*) sebagai representasi sumber daya (*resource*), bukan kata kerja (*verb*). Contoh:

- `/users` — sumber daya pengguna (donatur)
- `/campaigns` — sumber daya campaign
- `/donations` — sumber daya donasi
- `/communities` — sumber daya komunitas
- `/notifications` — sumber daya notifikasi

Operasi terhadap sumber daya ditentukan oleh *HTTP method* yang digunakan, bukan oleh nama endpoint. Pola ini memisahkan identitas sumber daya dari aksi yang dilakukan, sesuai prinsip *uniform interface* dalam REST.

#### b. Stateless Communication (Komunikasi Tanpa Status)

Setiap *request* dari frontend ke backend bersifat mandiri (*self-contained*) dan tidak bergantung pada *request* sebelumnya. Seluruh informasi yang dibutuhkan server untuk memproses *request* — termasuk identitas pengguna — disertakan dalam setiap *request* melalui header `Authorization: Bearer <token>`. Berbagive menggunakan **Laravel Sanctum** sebagai mekanisme autentikasi berbasis token (*token-based authentication*). Token dibuat saat pengguna berhasil login melalui `POST /auth/login` dan wajib disertakan pada seluruh endpoint yang membutuhkan autentikasi. Tidak ada *server-side session* yang disimpan; setiap *request* diverifikasi secara independen oleh Sanctum *guard* terhadap tabel `personal_access_tokens`.

#### c. Standard HTTP Methods

Berbagive menggunakan metode HTTP standar dengan semantik yang konsisten:

| Method | Semantik | Contoh Penggunaan |
|--------|----------|-------------------|
| `GET` | Mengambil data (*read*) | `GET /campaigns/{id}/public` — melihat detail campaign |
| `POST` | Membuat data baru (*create*) | `POST /donations` — membuat donasi baru |
| `PATCH` | Memperbarui sebagian data (*partial update*) | `PATCH /users/me` — mengedit profil |
| `DELETE` | Menghapus data (*delete*) | `DELETE /communities/{id}/follow` — unfollow komunitas |

Metode `PUT` tidak digunakan; seluruh pembaruan data menggunakan `PATCH` untuk *partial update* agar lebih sesuai dengan sifat operasi pembaruan yang kerap hanya menyentuh sebagian *field*.

#### d. Standard HTTP Status Codes

Server mengembalikan kode status HTTP yang sesuai dengan hasil pemrosesan:

| Kode | Makna | Kondisi |
|------|-------|---------|
| `200 OK` | Permintaan berhasil | Data berhasil diambil/diperbarui |
| `201 Created` | Sumber daya berhasil dibuat | Registrasi akun, pembuatan donasi |
| `400 Bad Request` | Permintaan tidak valid | Parameter salah, format tidak sesuai |
| `401 Unauthorized` | Belum/tidak terautentikasi | Token tidak ada, kadaluwarsa, atau tidak valid |
| `403 Forbidden` | Tidak memiliki izin | Role tidak sesuai (misal donatur mengakses endpoint superadmin) |
| `404 Not Found` | Sumber daya tidak ditemukan | Campaign/komunitas/donasi tidak ada |
| `409 Conflict` | Konflik data | Email sudah terdaftar, sudah follow komunitas yang sama |
| `410 Gone` | Sumber daya sudah tidak tersedia | Link reset password kadaluwarsa |
| `422 Unprocessable Entity` | Validasi gagal | Data form tidak memenuhi aturan validasi |
| `429 Too Many Requests` | Batas permintaan terlampaui | Rate limiting — terlalu banyak request |
| `503 Service Unavailable` | Layanan tidak tersedia | Database, Redis, atau storage gagal (health check) |

#### e. Konsistensi Format Response

Seluruh endpoint — baik sukses maupun gagal — mengembalikan *response* dalam format JSON yang seragam melalui *helper class* `App\Helpers\ApiResponse`. Format ini menjamin prediktabilitas bagi frontend dalam mengonsumsi respons API.

Response sukses:

```json
{
  "status": "success",
  "data": { ... },
  "message": "Operasi berhasil",
  "errors": null
}
```

Response error:

```json
{
  "status": "error",
  "data": null,
  "message": "Email sudah digunakan",
  "errors": { "code": "ERR-AUTH-03" }
}
```

Empat *key* — `status`, `data`, `message`, `errors` — selalu hadir di setiap respons, meskipun bernilai `null`. Frontend (Vue 3 + Axios) mengonsumsi data melalui `response.data.data` dan pesan melalui `response.data.message`.

#### f. Arsitektur Tiga Lapis (Three-Layer Architecture)

API Berbagive menerapkan arsitektur tiga lapis yang memisahkan tanggung jawab sebagai berikut:

1. **Presentation Layer** — Frontend Vue 3 dengan Axios sebagai HTTP client. Terletak di `frontend/`. Mengonsumsi API melalui *single axios instance* (`src/api/axios.js`) yang dilengkapi *request interceptor* (penyisipan token) dan *response interceptor* (redirect otomatis pada 401/403).

2. **Business Logic Layer** — Backend Laravel yang terdiri dari 21 API Controller di `backend/app/Http/Controllers/Api/`, 5 Service class, 15 *stored procedure* PostgreSQL, dan 3 middleware (`auth:sanctum`, `RoleMiddleware`, `throttle`). Logika bisnis transaksional yang kompleks — seperti pembuatan donasi, konfirmasi pembayaran, pencairan dana, dan pemotongan biaya platform — dienkapsulasi dalam *stored procedure* untuk menjamin integritas data.

3. **Data Layer** — PostgreSQL 16 dengan 19 tabel utama, 15 *view* (termasuk 1 *materialized view* `v_platform_summary_mv` untuk performa *dashboard* superadmin), 2 *function* (`fn_search_campaigns`, `fn_get_donation_chart`), serta sejumlah *constraint*, *trigger*, dan *index* (termasuk *full-text search* GIN index dengan *Indonesian dictionary*). Seluruh DDL, DML, *stored procedure*, *view*, dan *index* didefinisikan dalam *script* SQL terpisah di direktori `ddl/`.

#### g. Versioning

API Berbagive menggunakan *URL path versioning* dengan prefix `/api/v1/`. Konfigurasi *prefix* ini dilakukan di `bootstrap/app.php` Laravel melalui parameter `apiPrefix: 'api/v1'`. Frontend mengakses API melalui *relative URL* yang dikonfigurasi di *environment variable* `VITE_API_URL=/api/v1`, sehingga pada mode *development* (Vite proxy) maupun *production* (same-origin) tidak memerlukan perubahan konfigurasi.

#### h. Rate Limiting (Pembatasan Laju Permintaan)

Untuk mencegah penyalahgunaan dan melindungi ketersediaan layanan, Berbagive menerapkan *rate limiting* yang dikonfigurasi per kelompok endpoint:

| Kelompok Endpoint | Batas | Periode |
|-------------------|-------|---------|
| `/auth/login` | 60 permintaan | 1 menit |
| `/auth/*` (selain login) | 30 permintaan | 1 menit |
| `/auth/resend-verification` | 5 permintaan | 24 jam (1440 menit) |
| `/users/me/register-komunitas` | 5 permintaan | 1 jam (60 menit) |
| Endpoint terautentikasi umum | 120 permintaan | 1 menit |

Ketika batas terlampaui, server mengembalikan respons `429 Too Many Requests` dengan pesan yang disesuaikan dengan konteks (misal: "Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit").

#### i. Error Handling Terpusat

Seluruh *exception* yang tidak tertangani dikonversi menjadi respons JSON yang konsisten melalui *exception handler* yang dikonfigurasi di `bootstrap/app.php`. Dua *exception* yang ditangani secara khusus:

- `ThrottleRequestsException` (429) — Mengembalikan pesan spesifik untuk endpoint login, *resend verification*, dan endpoint umum.
- `AuthenticationException` (401) — Mengembalikan respons `{ status: "error", message: "Unauthenticated" }` untuk seluruh endpoint API.

#### j. Caching

Berbagive memanfaatkan Redis sebagai *cache driver* (`CACHE_STORE=redis`). Endpoint *detail campaign publik* (`GET /campaigns/{id}/public`) menerapkan *response caching* dengan TTL 30 detik melalui `Cache::remember()`, mengurangi beban *query* database untuk halaman yang sering diakses.

---

### 3.X.2 Hirarki Endpoint

API Berbagive diorganisasikan dalam struktur hirarkis berbasis modul fungsional. Seluruh endpoint berada di bawah *base URL* `/api/v1` dan dikelompokkan ke dalam 10 (sepuluh) modul utama. Setiap modul memiliki aturan autentikasi, otorisasi *role*, dan *rate limiting* yang spesifik.

Berikut adalah hirarki endpoint secara lengkap:

---

#### Modul 1: Otentikasi (`/auth`)

- **Middleware group:** `throttle:30,1` (30 permintaan per menit)
- **Autentikasi:** Publik, kecuali logout (`auth:sanctum`)

| Method | Endpoint | Auth | Throttle | Deskripsi |
|--------|----------|------|----------|-----------|
| `POST` | `/auth/register-user` | Publik | 30/mnt | Registrasi akun donatur baru |
| `POST` | `/auth/login` | Publik | 60/mnt | Login, mengembalikan token Bearer |
| `POST` | `/auth/forgot-password` | Publik | 30/mnt | Mengirim link reset password ke email |
| `POST` | `/auth/reset-password` | Publik | 30/mnt | Menyetel password baru (dengan token reset) |
| `POST` | `/auth/resend-verification` | Publik | 5/hari | Kirim ulang email verifikasi |
| `POST` | `/auth/logout` | `auth:sanctum` | 30/mnt | Menghapus token aktif, mengakhiri sesi |

---

#### Modul 2: Profil Donatur (`/users`)

- **Middleware group:** `throttle:120,1` + `auth:sanctum`
- **Role:** DONATUR (default; `register-komunitas` khusus donatur yang akan beralih role)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/users/me` | Melihat profil donatur sendiri |
| `PATCH` | `/users/me` | Mengedit profil donatur (nama, telepon, dst.) |
| `POST` | `/users/me` | Alternatif mengedit profil (untuk upload file) |
| `PATCH` | `/users/me/password` | Mengganti password |
| `GET` | `/users/me/donations` | Melihat riwayat donasi pribadi |
| `GET` | `/users/me/following` | Melihat daftar komunitas yang diikuti |
| `POST` | `/users/me/register-komunitas` | Mendaftar sebagai komunitas (donatur → komunitas) |

---

#### Modul 3: Konten Publik

- **Autentikasi:** Publik (tanpa login), throttle `120:1` untuk pencarian

**Campaign Publik:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/campaigns/search` | Pencarian & filter campaign |
| `GET` | `/campaigns/{id}/public` | Detail lengkap campaign + timeline dana + update post |
| `GET` | `/campaigns/{id}/donors` | Daftar donatur campaign (dengan paginasi) |
| `GET` | `/campaigns/{id}/monitoring` | Monitoring publik (progres, donatur, dsb.) |

**Komunitas Publik:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/communities/{id}/profile` | Profil publik komunitas (tanpa data sensitif) |
| `GET` | `/communities/search` | Pencarian komunitas |

**Data Master (Referensi):**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/campaign-categories` | Daftar kategori campaign yang aktif |
| `GET` | `/jenis-lembaga` | Daftar jenis lembaga (yayasan, organisasi, komunitas informal) |
| `GET` | `/wilayah` | Data wilayah berjenjang (provinsi → kab/kota → kecamatan → kelurahan) |
| `GET` | `/jenis-dokumen` | Daftar jenis dokumen yang diwajibkan per jenis lembaga |

---

#### Modul 4: Donasi (`/donations`)

- **Middleware group:** `throttle:120,1` + `auth:sanctum`
- **Role:** DONATUR (semua pengguna terautentikasi dapat berdonasi)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/donations` | Membuat donasi baru (isi nominal, pilih metode bayar, anonim/nama tampil) |
| `GET` | `/donations/{id}` | Melihat detail donasi (milik sendiri) |
| `GET` | `/donations/{id}/receipt` | Melihat data bukti donasi |
| `GET` | `/donations/{id}/receipt-pdf` | Mengunduh bukti donasi dalam format PDF |
| `PATCH` | `/donations/{id}/payment-status` | Memperbarui status pembayaran (simulasi — *pending* → `berhasil`/`gagal`) |

---

#### Modul 5: Interaksi Komunitas (Follow)

- **Middleware group:** `throttle:120,1` + `auth:sanctum`
- **Role:** DONATUR (semua pengguna terautentikasi)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/communities/{communityId}/follow` | Mengikuti komunitas |
| `DELETE` | `/communities/{communityId}/follow` | Berhenti mengikuti komunitas |
| `GET` | `/communities/{communityId}/followers` | Melihat daftar pengikut komunitas (maks. 25) |

---

#### Modul 6: Komunitas (KOMUNITAS)

- **Middleware group:** `auth:sanctum` + `role:KOMUNITAS`
- **Role:** Hanya pengguna dengan role KOMUNITAS yang telah diverifikasi

**Profil Komunitas:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/communities/profile` | Melihat profil komunitas sendiri (termasuk data sensitif) |
| `PATCH` | `/communities/profile` | Mengedit profil komunitas (deskripsi, kontak, foto) |
| `POST` | `/communities/profile` | Alternatif edit profil (untuk upload file) |

**Pengelolaan Campaign:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/communities/campaigns` | Riwayat seluruh campaign milik komunitas |
| `POST` | `/communities/campaigns` | Mengajukan campaign baru (dengan RAB, foto, lokasi) |
| `POST` | `/communities/campaigns/{id}/updates` | Membuat post update campaign |
| `POST` | `/communities/campaigns/{id}/clarifications` | Mengirim klarifikasi (jika campaign dinonaktifkan) |

**Pengelolaan Rekening:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/communities/bank-account/history` | Riwayat perubahan rekening |
| `POST` | `/communities/bank-account/change` | Mengajukan perubahan rekening (perlu verifikasi superadmin) |

**Pencairan Dana:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/communities/withdrawals` | Riwayat pengajuan pencairan dana |
| `POST` | `/communities/withdrawals` | Mengajukan pencairan dana baru |

**Dashboard:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/communities/dashboard` | Ringkasan dashboard komunitas (statistik, grafik, dsb.) |

---

#### Modul 7: Superadmin (`/superadmin`)

- **Middleware group:** `auth:sanctum` + `role:SUPERADMIN`
- **Role:** Hanya pengguna dengan role SUPERADMIN

**Profil Superadmin:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/profile` | Melihat profil superadmin |
| `PATCH` / `POST` | `/superadmin/profile` | Mengedit profil superadmin |
| `PATCH` | `/superadmin/profile/password` | Mengganti password superadmin |

**Dashboard & Analitik:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/dashboard` | Ringkasan dashboard superadmin |
| `GET` | `/superadmin/dashboard/statistics` | Statistik agregat platform |
| `GET` | `/superadmin/dashboard/activities` | Aktivitas terbaru platform |
| `GET` | `/superadmin/analytics/platform` | Grafik statistik platform (donasi, donatur baru, komunitas baru) |

**Kelola Donatur:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/donors` | Daftar seluruh donatur terdaftar |
| `GET` | `/superadmin/donors/{id}` | Detail donatur + riwayat donasi |
| `PATCH` | `/superadmin/donors/{id}/status` | Aktif/nonaktifkan akun donatur |

**Kelola Komunitas:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/communities` | Daftar seluruh komunitas |
| `GET` | `/superadmin/communities/{id}` | Detail komunitas + berkas + riwayat |
| `PATCH` | `/superadmin/communities/{id}/status` | Aktif/nonaktifkan akun komunitas |

**Review Pendaftaran Komunitas:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/community-registrations` | Daftar pendaftaran menunggu review |
| `GET` | `/superadmin/community-registrations/history` | Riwayat seluruh pendaftaran |
| `GET` | `/superadmin/community-registrations/{id}` | Detail pendaftaran (akun + profil + berkas) |
| `POST` | `/superadmin/community-registrations/{id}/approve` | Menyetujui pendaftaran |
| `POST` | `/superadmin/community-registrations/{id}/reject` | Menolak pendaftaran |

**Review Campaign:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/campaigns/review` | Daftar campaign menunggu review |
| `GET` | `/superadmin/campaigns/{id}` | Detail campaign untuk direview |
| `POST` | `/superadmin/campaigns/{id}/approve` | Menyetujui campaign → status Aktif |
| `POST` | `/superadmin/campaigns/{id}/reject` | Menolak campaign → status Ditolak |
| `POST` | `/superadmin/campaigns/{id}/disable` | Menonaktifkan campaign (dari laporan) |
| `GET` | `/superadmin/campaign-review-history` | Riwayat seluruh keputusan review campaign |

**Review Pencairan Dana:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/disbursements` | Daftar pengajuan pencairan menunggu review |
| `GET` | `/superadmin/disbursements/history` | Riwayat seluruh pencairan |
| `GET` | `/superadmin/disbursements/{id}` | Detail pengajuan pencairan |
| `POST` | `/superadmin/disbursements/{id}/approve` | Menyetujui pencairan → transfer dana |
| `POST` | `/superadmin/disbursements/{id}/reject` | Menolak pencairan → lepas kunci saldo |

**Verifikasi Perubahan Rekening:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/bank-account-changes` | Daftar perubahan rekening menunggu verifikasi |
| `GET` | `/superadmin/bank-account-changes/history` | Riwayat seluruh perubahan rekening |
| `GET` | `/superadmin/bank-account-changes/{id}` | Detail perubahan rekening (lama vs baru) |
| `POST` | `/superadmin/bank-account-changes/{id}/approve` | Menyetujui perubahan rekening |
| `POST` | `/superadmin/bank-account-changes/{id}/reject` | Menolak perubahan rekening |

**Tindak Lanjut Laporan Campaign:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/campaign-reports` | Daftar laporan campaign yang masuk |
| `GET` | `/superadmin/campaign-reports/{id}` | Detail laporan campaign |
| `POST` | `/superadmin/campaign-reports/{id}/ignore` | Mengabaikan laporan |
| `GET` | `/superadmin/campaign-clarifications` | Daftar klarifikasi dari komunitas |
| `GET` | `/superadmin/campaign-clarifications/{id}` | Detail klarifikasi |
| `POST` | `/superadmin/campaign-clarifications/{id}/reactivate` | Aktifkan kembali campaign |
| `POST` | `/superadmin/campaign-clarifications/{id}/close-permanently` | Tutup permanen campaign |

**Kelola Kategori Campaign:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/campaign-categories` | Daftar kategori (termasuk nonaktif) |
| `POST` | `/superadmin/campaign-categories` | Menambah kategori baru |
| `PATCH` | `/superadmin/campaign-categories/{id}` | Mengedit nama kategori |
| `PATCH` | `/superadmin/campaign-categories/{id}/status` | Aktif/nonaktifkan kategori |
| `DELETE` | `/superadmin/campaign-categories/{id}` | Menghapus kategori |

**Kelola Template Dokumen:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/document-templates` | Daftar template dokumen |
| `POST` | `/superadmin/document-templates` | Mengunggah template baru |

**Export Laporan Keuangan:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `POST` | `/superadmin/reports/financial/export` | Export laporan keuangan (Excel/PDF) |

**Audit Log:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/superadmin/audit-logs` | Melihat log audit aktivitas superadmin |

**Monitoring Internal & Moderasi Post:**

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/campaigns/{id}/internal` | Monitoring internal campaign (keuangan, RAB, donasi detail) |
| `DELETE` | `/campaigns/updates/{updateId}` | Menghapus post update campaign |

---

#### Modul 8: Notifikasi (`/notifications`)

- **Middleware group:** `auth:sanctum` (semua pengguna terautentikasi)

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/notifications` | Daftar notifikasi pengguna (terbaru ke terlama) |
| `PATCH` | `/notifications/read-all` | Menandai seluruh notifikasi sebagai sudah dibaca |
| `PATCH` | `/notifications/{notificationId}/read` | Menandai satu notifikasi sebagai sudah dibaca |

---

#### Modul 9: Pelaporan & Post Update

- **Middleware:** Bervariasi

| Method | Endpoint | Auth | Deskripsi |
|--------|----------|------|-----------|
| `POST` | `/campaigns/{id}/reports` | `auth:sanctum` | Melaporkan campaign |
| `POST` | `/campaigns/updates/{updateId}/reports` | Publik (tanpa login) | Melaporkan post update |

---

#### Modul 10: Internal & Health Check

| Method | Endpoint | Auth | Deskripsi |
|--------|----------|------|-----------|
| `POST` | `/internal/notifications/user-events` | Publik | Endpoint internal sistem untuk mengirim notifikasi (dipanggil *backend-to-backend*) |
| `GET` | `/health` | Publik | Health check — memeriksa koneksi database, Redis, dan storage |

---

#### Ringkasan Struktur Middleware per Modul

| Modul | Middleware | Role |
|-------|-----------|------|
| Auth | `throttle:30,1` (login: `60,1`) | Publik |
| Users | `throttle:120,1` + `auth:sanctum` | DONATUR |
| Konten Publik | `throttle:120,1` | Publik |
| Donasi | `throttle:120,1` + `auth:sanctum` | Semua role |
| Follow | `throttle:120,1` + `auth:sanctum` | Semua role |
| Komunitas | `auth:sanctum` + `role:KOMUNITAS` | KOMUNITAS |
| Superadmin | `auth:sanctum` + `role:SUPERADMIN` | SUPERADMIN |
| Notifikasi | `auth:sanctum` | Semua role |
| Health | Tidak ada | Publik |

**Total endpoint API Berbagive: ±80 endpoint** yang tersebar dalam 10 modul fungsional, dengan 3 level otorisasi (DONATUR, KOMUNITAS, SUPERADMIN) dan 5 tingkat *rate limiting* yang berbeda. Struktur hirarkis ini dirancang agar setiap modul memiliki batasan akses yang jelas, mendukung prinsip *least privilege*, serta memudahkan pengembangan dan pemeliharaan di masa mendatang.
