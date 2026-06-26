# Postman Testing Flow — Backend Esensial Berbagive

**Base URL:** `http://localhost:8000/api/v1`

Pastikan backend jalan:
```bash
cd backend && php artisan serve
```

---

## Setup Environment Variables Postman

| Variable | Value | Keterangan |
|----------|-------|------------|
| `base_url` | `http://localhost:8000/api/v1` | Base API |
| `token_donatur` | *(isi setelah login)* | Token role DONATUR |
| `token_komunitas` | *(isi setelah login)* | Token role KOMUNITAS |
| `token_admin` | *(isi setelah login)* | Token role SUPERADMIN |
| `id_campaign` | *(isi dari response)* | ID campaign aktif |
| `id_donasi` | *(isi dari response)* | ID donasi |
| `community_id` | *(isi dari response)* | ID komunitas |

---

## FASE A — Auth & Setup Data Dasar

| # | Test Script | Method | Endpoint | Body | Expected | Simpan |
|---|-------------|--------|----------|------|----------|--------|
| 1 | TS-BE-E01 | `POST` | `{{base_url}}/auth/register-user` | `{"username":"test","email":"test@mail.com","password":"Password123"}` | 201, role DONATUR | `id_user` |
| 2 | TS-BE-E27 | `POST` | `{{base_url}}/auth/register-user` | `{"email":"test@mail.com","username":"newuser","password":"Password123"}` | **409** — email sudah dipakai | — |
| 3 | TS-BE-E02 | `POST` | `{{base_url}}/auth/login` | `{"email":"test@mail.com","password":"Password123"}` | 200, dapat token | `{{token_donatur}}` |
| 4 | TS-BE-E28 | `POST` | `{{base_url}}/auth/login` | `{"email":"test@mail.com","password":"WrongPass123"}` | **401** | — |
| 5 | TS-BE-E03 | `POST` | `{{base_url}}/auth/logout` | Header: `Authorization: Bearer {{token_donatur}}` | 200 | — |

> **Setelah ini:** Register akun KOMUNITAS via `POST {{base_url}}/users/me/register-komunitas`, lalu login untuk dapat `{{token_komunitas}}`.
> Login SUPERADMIN (via seeder) untuk dapat `{{token_admin}}`.

---

## FASE B — Superadmin Setup

| # | Test Script | Method | Endpoint | Header | Expected |
|---|-------------|--------|----------|--------|----------|
| 6 | TS-BE-E20 | `GET` | `{{base_url}}/superadmin/dashboard` | `Bearer {{token_admin}}` | 200, stats dashboard |
| 7 | TS-BE-E32 | `GET` | `{{base_url}}/superadmin/dashboard` | `Bearer {{token_donatur}}` | **403** — akses ditolak |
| 8 | TS-BE-E21 | `POST` | `{{base_url}}/superadmin/community-registrations/{id}/approve` | `Bearer {{token_admin}}` | 200, akun aktif |

---

## FASE C — Campaign (KOMUNITAS)

| # | Test Script | Method | Endpoint | Body / Header | Expected |
|---|-------------|--------|----------|---------------|----------|
| 9 | TS-BE-E16 | `POST` | `{{base_url}}/communities/campaigns` | `{"judul":"Bantu Pendidikan","id_kategori":1,"deskripsi":"Deskripsi campaign","target_dana":10000000,"tanggal_mulai":"2026-07-01","tanggal_selesai":"2026-12-31","foto_campaign_url":"url","kode_wilayah":"3276040001","target_audiens":"Anak SD","tipe_distribusi":"individual","url_rab":"url"} + Bearer {{token_komunitas}}` | 201, status `menunggu_review` |
| 10 | TS-BE-E22 | `POST` | `{{base_url}}/superadmin/campaigns/{id}/approve` | `Bearer {{token_admin}}` | 200, campaign jadi `aktif` |
| 11 | TS-BE-E17 | `POST` | `{{base_url}}/communities/campaigns/{id}/updates` | `{"judul_update":"Update Minggu 1","konten":"Distribusi tahap awal berjalan lancar","foto_urls":["url"]} + Bearer {{token_komunitas}}` | 201 |
| 12 | TS-BE-E04 | `GET` | `{{base_url}}/campaigns/search?page=1&status=aktif` | Tanpa token | 200, array campaign + pagination |
| 13 | TS-BE-E05 | `GET` | `{{base_url}}/campaigns/{id}/public` | Tanpa token | 200, detail campaign |

---

## FASE D — Donasi (DONATUR)

| # | Test Script | Method | Endpoint | Body / Header | Expected |
|---|-------------|--------|----------|---------------|----------|
| 14 | TS-BE-E07 | `POST` | `{{base_url}}/donations` | `{"id_campaign":{id},"nominal":50000,"metode_pembayaran":"qris","is_anonim":false,"nama_tampil":"Fatih"} + Bearer {{token_donatur}}` | 201, `status_pembayaran:pending` |
| 15 | TS-BE-E08 | `POST` | `{{base_url}}/donations` | `{"id_campaign":{id},"nominal":100000,"metode_pembayaran":"gopay","is_anonim":true} + Bearer {{token_donatur}}` | 201, `is_anonim:true` |
| 16 | TS-BE-E29 | `POST` | `{{base_url}}/donations` | `{"id_campaign":1,"nominal":50000}` — **tanpa Auth** | **401** |
| 17 | TS-BE-E30 | `POST` | `{{base_url}}/donations` | `{"id_campaign":{id},"nominal":3000} + Bearer {{token_donatur}}` | **422** — nominal min Rp 5.000 |
| 18 | TS-BE-E09 | `PATCH` | `{{base_url}}/donations/{id}/payment-status` | `{"status_pembayaran":"berhasil"} + Bearer {{token_donatur}}` | 200, dana terkumpul bertambah |
| 19 | TS-BE-E10 | `GET` | `{{base_url}}/users/me/donations?page=1&per_page=15` | `Bearer {{token_donatur}}` | 200, array donasi |
| 20 | TS-BE-E11 | `GET` | `{{base_url}}/donations/{id}` | `Bearer {{token_donatur}}` | 200, detail donasi |
| 21 | TS-BE-E12 | `GET` | `{{base_url}}/donations/{id}/receipt` | `Bearer {{token_donatur}}` | 200, data receipt |
| 22 | TS-BE-E13 | `GET` | `{{base_url}}/donations/{id}/receipt-pdf` | `Bearer {{token_donatur}}` | 200, binary PDF |

---

## FASE E — Monitoring & Komunitas

| # | Test Script | Method | Endpoint | Header | Expected |
|---|-------------|--------|----------|--------|----------|
| 23 | TS-BE-E06 | `GET` | `{{base_url}}/campaigns/{id}/monitoring?page=1&per_page=15` | Tanpa token | 200, progress + donatur terbaru |
| 24 | TS-BE-E14 | `GET` | `{{base_url}}/communities/{id}/profile` | Tanpa token | 200, profil publik komunitas |
| 25 | TS-BE-E15 | `POST` | `{{base_url}}/communities/{communityId}/follow` | `Bearer {{token_donatur}}` | 200, follow berhasil |
| 26 | TS-BE-E18 | `GET` | `{{base_url}}/communities/dashboard` | `Bearer {{token_komunitas}}` | 200, dashboard stats |

---

## FASE F — Pencairan & Laporan

| # | Test Script | Method | Endpoint | Body / Header | Expected |
|---|-------------|--------|----------|---------------|----------|
| 27 | TS-BE-E19 | `POST` | `{{base_url}}/communities/withdrawals` | `{"id_campaign":{id},"nominal":5000000,"keterangan":"Pencairan tahap 1","url_proposal":"url","nama_bank_tujuan":"BCA","nomor_rekening_tujuan":"1234567890"} + Bearer {{token_komunitas}}` | 201, status `menunggu_review` |
| 28 | TS-BE-E23 | `POST` | `{{base_url}}/superadmin/disbursements/{id}/approve` | `Bearer {{token_admin}}` | 200, pencairan disetujui |
| 29 | TS-BE-E24 | `POST` | `{{base_url}}/communities/withdrawals/{id}/laporan` | `{"jumlah_penerima":50,"deskripsi_penggunaan":"Penyaluran paket sembako untuk warga terdampak bencana di Desa Makmur","file_dokumentasi_url":["url1","url2"]} + Bearer {{token_komunitas}}` | 201, status `menunggu_verifikasi` |
| 30 | TS-BE-E25 | `POST` | `{{base_url}}/superadmin/laporan/{id}/verify` | `Bearer {{token_admin}}` | 200, milestone publik auto-created |
| 31 | TS-BE-E26 | `GET` | `{{base_url}}/campaigns/{id}/milestones?page=1&per_page=15` | Tanpa token | 200, array milestone |

---

## FASE G — Negatif Test (sisa)

| # | Test Script | Method | Endpoint | Header / Body | Expected |
|---|-------------|--------|----------|---------------|----------|
| 32 | TS-BE-E31 | `POST` | `{{base_url}}/donations` | `{"id_campaign":{id_campaign_nonaktif},"nominal":50000} + Bearer {{token_donatur}}` | **403** — campaign tidak menerima donasi |

---

## Tips Dynamic Variable di Postman (Tests Script)

Setelah request sukses, isi environment variable otomatis:

```js
// Ambil id_campaign dari response
var jsonData = pm.response.json();
pm.environment.set("id_campaign", jsonData.data.id_campaign);

// Ambil id_donasi
pm.environment.set("id_donasi", jsonData.data.id_donasi);

// Ambil token dari login
pm.environment.set("token_donatur", jsonData.data.token);
```

---

## Struktur Folder Collection (Saran)

```
Berbagive - Backend Esensial
├── Auth (E01, E02, E03, E27, E28)
├── Superadmin (E20, E21, E23, E25, E32)
├── Campaign (E04, E05, E16, E17, E22)
├── Donation (E07-E13, E29-E31)
├── Monitoring & Komunitas (E06, E14, E15, E18)
└── Pencairan & Laporan (E19, E24, E26)
```
