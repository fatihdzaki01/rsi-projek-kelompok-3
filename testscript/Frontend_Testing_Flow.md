# Frontend Testing Flow — Manual UI Berbagive

**Base URL Frontend:** `http://localhost:5173`

Pastikan backend dan frontend jalan:
```bash
# Terminal 1
cd backend && php artisan serve

# Terminal 2
cd frontend && npm run dev
```

Gunakan browser (Chrome/Firefox) untuk menjalankan test script secara manual.

---

## Setup Data Test Seeder

Sebelum testing, pastikan data berikut tersedia di database (via seeder atau manual):

| Akun | Email | Password | Role |
|------|-------|----------|------|
| DONATUR | `donatur@mail.com` | `Password123` | DONATUR |
| KOMUNITAS | `komunitas@mail.com` | `Password123` | KOMUNITAS |
| SUPERADMIN | `admin@mail.com` | `Password123` | SUPERADMIN |
| User Baru | `test@mail.com` | `Password123` | *(buat via register)* |

Data pendukung minimal:
- 1 campaign dengan status `aktif` milik KOMUNITAS
- 1 campaign dengan milestone terverifikasi
- Beberapa donasi dengan status `berhasil` dan `pending`
- 1 komunitas dengan followers

---

## FASE A — Auth & Public Pages

| # | TS Code | Skenario Test | Langkah Manual | Ekspektasi |
|---|---------|---------------|----------------|------------|
| 1 | TS-FE-E01 | **Register User (DONATUR)** | 1. Buka `/register`<br>2. Isi: username `test`, email `test@mail.com`, password `Password123`<br>3. Klik submit | Form registrasi tampil dengan input username, email, password. Submit berhasil. Redirect ke `/login`. Notifikasi sukses: "Registrasi berhasil". |
| 2 | TS-FE-E02 | **Login DONATUR → redirect** | 1. Buka `/login`<br>2. Isi email `donatur@mail.com`, password `Password123`<br>3. Klik Login | Login berhasil. Redirect ke `/campaigns`. Navbar berubah: avatar user muncul, dropdown profil tersedia. Daftar campaign tampil. |
| 3 | TS-FE-E03 | **Login KOMUNITAS → redirect** | 1. Buka `/login`<br>2. Isi email `komunitas@mail.com`, password `Password123`<br>3. Klik Login | Login berhasil. Redirect ke `/communities/dashboard`. Dashboard tampil dengan stat cards: campaign aktif, donatur, dana terkumpul. |
| 4 | TS-FE-E04 | **Login SUPERADMIN → redirect** | 1. Buka `/login`<br>2. Isi email `admin@mail.com`, password `Password123`<br>3. Klik Login | Login berhasil. Redirect ke `/dashboard`. Dashboard superadmin tampil dengan statistik platform. Sidebar navigasi admin muncul. |

---

## FASE B — Campaign & Donation Flow

| # | TS Code | Skenario Test | Langkah Manual | Ekspektasi |
|---|---------|---------------|----------------|------------|
| 5 | TS-FE-E05 | **Daftar Campaign (Etalase Publik)** | 1. Buka `/campaigns` (tanpa login)<br>2. Klik pill kategori<br>3. Ketik di search bar | Card campaign tampil: foto, judul, nama lembaga, progress bar (%). Pill button kategori berfungsi (highlight saat diklik). Search bar tersedia dan memfilter hasil. |
| 6 | TS-FE-E06 | **Detail Campaign + Sidebar Donasi** | 1. Klik salah satu campaign di daftar<br>2. Scroll halaman detail | Hero image, judul, nama lembaga (link ke profil komunitas), progress bar (Rp terkumpul / target), jumlah donatur, target penerima, waktu tersisa, deskripsi cerita. Sidebar donasi di kanan: pilihan nominal, input kustom, toggle anonim, pesan/doa, metode pembayaran, tombol "Donasi Sekarang". |
| 7 | TS-FE-E08 | **Donasi — Submit dari Sidebar** | 1. Login sebagai DONATUR<br>2. Buka detail campaign aktif<br>3. Pilih nominal Rp 50.000<br>4. Centang anonim<br>5. Pilih metode QRIS<br>6. Klik "Donasi Sekarang" | Redirect ke halaman checkout `/payments/checkout/{id}`. Halaman checkout menampilkan: nomor transaksi, nominal, metode pembayaran, batas waktu, QR code. |
| 8 | TS-FE-E09 | **Riwayat Donasi User** | 1. Login sebagai DONATUR<br>2. Klik dropdown profil → "Riwayat Donasi"<br>3. Klik filter status | Tabel riwayat donasi: judul campaign, nominal, status (colored badge: hijau=berhasil, kuning=pending, merah=gagal), tanggal. Filter status berfungsi memfilter baris. Pagination jika > 1 halaman. |
| 9 | TS-FE-E10 | **Detail Donasi + Bukti** | 1. Klik salah satu donasi berstatus "berhasil"<br>2. Klik "Lihat Bukti"<br>3. Klik "Download PDF" | Detail: nomor transaksi, judul campaign, nominal, metode, status, nama/anonim, tanggal. Tombol "Lihat Bukti" menampilkan receipt view. Tombol "Download PDF" mendownload file receipt. |

---

## FASE C — Komunitas & Monitoring

| # | TS Code | Skenario Test | Langkah Manual | Ekspektasi |
|---|---------|---------------|----------------|------------|
| 10 | TS-FE-E07 | **Monitoring Publik Campaign** | 1. Klik "Lihat Monitoring" di detail campaign<br>2. Scroll halaman | Header campaign (judul, progress %). 3 stat cards: donatur, penerima, waktu tersisa. Daftar donatur terbaru: avatar + nama + tanggal. Pagination jika > 15 donatur. Breadcrumb dan link kembali tersedia. |
| 11 | TS-FE-E11 | **Profil Publik Komunitas + Follow** | 1. Buka `/communities/{id}`<br>2. Klik stat "Followers"<br>3. Klik stat "Campaign Aktif"<br>4. Klik tombol Follow (login DONATUR) | Foto lembaga, nama, deskripsi, badge verifikasi. 3 stat clickable: Campaign Aktif, Campaign Selesai, Followers. Klik Campaign Aktif → modal daftar campaign. Klik Followers → modal daftar followers. Tombol Follow/Following berfungsi toggle. |
| 12 | TS-FE-E12 | **Post Update Campaign (Komunitas)** | 1. Login KOMUNITAS<br>2. Buka dashboard → klik "Buat Update"<br>3. Isi campaign, judul, konten, URL foto<br>4. Submit | Form: dropdown pilih campaign, judul, konten, URL foto (max 10). Submit → notifikasi sukses. Update muncul di halaman detail campaign. |
| 13 | TS-FE-E13 | **Dashboard Komunitas** | 1. Login KOMUNITAS<br>2. Buka `/communities/dashboard` | Stat cards: campaign aktif, donatur, dana terkumpul, saldo tersisa. Tabel daftar campaign. Grafik donasi. Info pencairan pending. Tombol "Ajukan Campaign" dan "Ajukan Pencairan". |
| 14 | TS-FE-E15 | **Monitoring — Milestone Tampil** | 1. Buka monitoring campaign yang punya milestone terverifikasi<br>2. Scroll ke section timeline | Section timeline milestone: judul ("Distribusi Tahap X — N Penerima"), deskripsi, jumlah penerima, foto dokumentasi, badge "Terverifikasi" (hijau), tanggal verifikasi. |

---

## FASE D — Superadmin & Negative Tests

| # | TS Code | Skenario Test | Langkah Manual | Ekspektasi |
|---|---------|---------------|----------------|------------|
| 15 | TS-FE-E14 | **Dashboard Superadmin** | 1. Login SUPERADMIN<br>2. Buka `/dashboard`<br>3. Klik menu sidebar | Statistik platform: total user, komunitas, campaign, donasi, pencairan. Sidebar navigasi admin lengkap (12 menu). Tabel donasi terbaru (5 baris). Tabel review terbaru (5 baris). |
| 16 | TS-FE-E16 | **(Negatif) Login — Password salah** | 1. Buka `/login`<br>2. Isi email `donatur@mail.com`, password `wrong`<br>3. Klik Login | Tetap di `/login`. Notifikasi error: "Email atau password salah". Form tetap terisi. Tidak redirect. |
| 17 | TS-FE-E17 | **(Negatif) Donasi — Nominal < Rp 5.000** | 1. Login DONATUR<br>2. Buka detail campaign aktif<br>3. Isi nominal Rp 3.000<br>4. Klik "Donasi Sekarang" | Tetap di halaman campaign. Notifikasi error: "Minimal donasi Rp 5.000". Tidak redirect ke checkout. |
| 18 | TS-FE-E18 | **(Negatif) Halaman Butuh Auth — Redirect Login** | 1. Logout / hapus token<br>2. Buka `/profile` langsung | Diarahkan ke `/login?redirect=/profile`. Setelah login berhasil, redirect balik ke `/profile`. |
| 19 | TS-FE-E19 | **(Negatif) Superadmin — DONATUR ditolak** | 1. Login sebagai DONATUR<br>2. Buka `/dashboard` | Diarahkan ke `/forbidden`. Halaman 403: "Akses ditolak" / icon error. |
| 20 | TS-FE-E20 | **(Negatif) Campaign tidak ditemukan** | 1. Buka `/campaigns/99999` (ID tidak ada) | Tampil halaman error: icon error, pesan "Campaign tidak tersedia". Tombol kembali ke daftar campaign. |

---

## Ringkasan FASE & Alur

```
FASE A (Auth)     ─► Register → Login DONATUR → Login KOMUNITAS → Login SUPERADMIN
                         │
FASE B (Donasi)    ◄────┘ Campaign List → Detail → Donasi Sidebar → Checkout → History Detail
                         │
FASE C (Komunitas) ◄────┘ Monitoring Publik → Profil Komunitas → Follow → Update Campaign
                         │
FASE D (Superadmin)◄────┘ Dashboard SA → Negative Tests (auth, validasi, 403, 404)
```

---

## Status Tracking

| FASE | TS Code | Status | Tester | Tanggal | Screenshot |
|------|---------|--------|--------|---------|------------|
| A | TS-FE-E01 | □ | | | |
| A | TS-FE-E02 | □ | | | |
| A | TS-FE-E03 | □ | | | |
| A | TS-FE-E04 | □ | | | |
| B | TS-FE-E05 | □ | | | |
| B | TS-FE-E06 | □ | | | |
| B | TS-FE-E08 | □ | | | |
| B | TS-FE-E09 | □ | | | |
| B | TS-FE-E10 | □ | | | |
| C | TS-FE-E07 | □ | | | |
| C | TS-FE-E11 | □ | | | |
| C | TS-FE-E12 | □ | | | |
| C | TS-FE-E13 | □ | | | |
| C | TS-FE-E15 | □ | | | |
| D | TS-FE-E14 | □ | | | |
| D | TS-FE-E16 | □ | | | |
| D | TS-FE-E17 | □ | | | |
| D | TS-FE-E18 | □ | | | |
| D | TS-FE-E19 | □ | | | |
| D | TS-FE-E20 | □ | | | |
