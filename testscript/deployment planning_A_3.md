   
**LAPORAN**

**DEPLOYMENT PLANNING**

**KE SERVER PRODUCTION**

Rekayasa Sistem Informasi – Aplikasi Web Client-Server

| Nama Proyek | Berbagive |
| :---- | :---- |
| **Versi Rilis** | v1.0.0 |
| **Tanggal Rencana Deployment** | \[17 / 06 / 2026\] |
| **Server Target (Web)** | [https://hospital-extras-explicit-active.trycloudflare.com](https://hospital-extras-explicit-active.trycloudflare.com)  |
| **Server Target (DB)** | [https://hospital-extras-explicit-active.trycloudflare.com/login](https://hospital-extras-explicit-active.trycloudflare.com/login)  |
| **Mata Kuliah** | Rekayasa Sistem Informasi |
| **Dosen Pengampu** | Bambang Widoyono, S.T., M.T.I. |
| **Status Dokumen** | Final |

| NIM | Nama | PIC konten |
| :---- | :---- | :---- |
| L0224001 | Angelina Harlitasari | BAB 1, 6 |
| L0224007 | Muhammad Rasyid H | BAB 4 |
| L0224023 | Najma Syakira | BAB 3 |
| L0224042 | Fatih Dzaki Nabhani | BAB 2, 5 |

 

# **BAB 1 – PENDAHULUAN**

## **1.1 Latar Belakang**

Dokumen ini merupakan panduan standar deployment untuk aplikasi berbasis web client-server yang akan dipindahkan dari lingkungan pengembangan (development/staging) ke server production. Deployment merupakan tahap kritis yang menentukan keberhasilan suatu sistem dapat diakses oleh pengguna akhir secara stabil, aman, dan berkinerja optimal.

Perencanaan yang terstruktur, mencakup persiapan environment, pemetaan folder sumber dan tujuan, langkah konfigurasi, pengujian dasar, serta rencana mitigasi Risiko sangat diperlukan agar proses deployment berlangsung terkendali dan risiko gangguan layanan dapat diminimalkan. 

Aplikasi yang akan dilakukan deployment adalah Berbagive, sebuah sistem informasi penyaluran dan pengelolaan bantuan sosial berbasis komunitas yang dikembangkan dalam bentuk aplikasi web. Berbagive didesain untuk memfasilitasi proses penggalang dana dengan masyarakat umum sebagai donatur. Sistem ini tidak hanya menyediakan fitur donasi, tetapi juga mendukung proses pengajuan campaign, verifikasi komunitas, pengajuan pencairan dana, monitoring penggunaan dana, notifikasi, dashboard komunitas, dashboard superadmin, serta pengelolaan data oleh superadmin.

Berdasarkan ruang lingkup pada FSD, Berbagive dikembangkan untuk menjawab kebutuhan transparansi dan akuntabilitas dalam pengelolaan dana sosial. Sistem ini mempunyai mekanisme verifikasi legalitas komunitas, pengajuan pencairan dana bertahap yang disertai laporan penggunaan dana, serta fitur monitoring campaign yang menampilkan riwayat dana masuk dan dana keluar. Oleh karena itu, proses deployment Berbagive perlu direncanakan secara sistematis agar aplikasi dapat berjalan secara stabil pada server production, mendukung akses pengguna sesuai peran, menjaga keamanan data, serta memastikan fitur utama aplikasi dapat digunakan dengan baik setelah go-live. 

**\[jangan di ubah, tambahkan saja penjelasan aplikasi dari FSD\]**

## **1.2 Tujuan**

* Mendokumentasikan persyaratan environment hardware per jenis server yang diperlukan.

* Memetakan isi folder source (development) dan folder target (production) secara eksplisit.

* Menyediakan panduan langkah deployment lengkap termasuk aksi copy/transfer komponen antar environment.

* Menetapkan prosedur smoke test untuk verifikasi fungsionalitas pasca-deployment.

* Memastikan seluruh komponen utama aplikasi Berbagive, meliputi frontend, backend API, database, konfigurasi environment, storage file, dan layanan pendukung lainnya telah siap digunakan pada lingkungan production. 

* Menjadi pedoman bagi tim pengembang dalam melakukan deployment aplikasi Berbagive secara terstruktur, terdokumentasi, dan dapat diverifikasi melalui checklist serta indikator keberhasilan pada setiap tahap. 

## **1.3 Ruang Lingkup**

Dokumen ini mencakup proses deployment aplikasi Berbagive, yaitu sistem informasi donasi berbasis komunitas yang dikembangkan untuk memfasilitasi kegiatan penggalangan dan penyaluran dana secara transparan antara komunitas penggalang dana dengan para donatur. Deployment dilakukan untuk memindahkan aplikasi dari lingkungan development/staging ke lingkungan production agar sistem dapat diakses oleh pengguna akhir melalui browser secara stabil, aman, dan sesuai dengan kebutuhan operasional.

Ruang lingkup deployment mencakup seluruh komponen utama aplikasi Berbagive, yaitu frontend, backend, database, konfigurasi environment, serta file dan komponen pendukung yang dibutuhkan agar aplikasi dapat berjalan pada server production. Sistem Berbagive dikembangkan sebagai aplikasi berbasis web dengan Vue pada sisi frontend dan Laravel sebagai backend. Oleh karena itu, proses deployment perlu memperhatikan kesiapan source code, dependency aplikasi, konfigurasi database, pengaturan file environment, serta pengamanan akses menggunakan protokol HTTPS.

Secara arsitektural, deployment Berbagive menggunakan pendekatan containerized dengan Docker. Komponen dipisahkan dalam container terpisah: Nginx sebagai web server/reverse proxy (presentation layer), PHP-FPM Laravel sebagai backend API (application layer), dan PostgreSQL sebagai database (data layer). Seluruh container berjalan pada satu mesin dengan sistem operasi Ubuntu 22.04 LTS. Akses publik ke aplikasi menggunakan tailscale Tunnel yang menyediakan HTTPS otomatis tanpa perlu konfigurasi sertifikat SSL manual. File upload pengguna (foto profil, dokumen legalitas, bukti campaign) disimpan menggunakan MinIO (S3-compatible object storage) dalam container terpisah.

Berdasarkan ruang lingkup sistem, aplikasi Berbagive mencakup fitur utama seperti autentikasi, manajemen profil, donasi, campaign, pencairan dana, monitoring, notifikasi, dashboard komunitas, dashboard superadmin, serta pengelolaan dan verifikasi komunitas. Sistem juga mendukung proses bisnis utama berupa pendaftaran komunitas, pengajuan dan review campaign, donasi online, pencairan dana, monitoring aliran dana, dan moderasi platform.

Dokumen deployment ini berfokus pada tahapan teknis untuk menyiapkan aplikasi pada lingkungan production, meliputi persiapan server, transfer source code, konfigurasi environment dan database, instalasi dependency, pengujian dasar (smoke test), monitoring awal, serta backup dan recovery. Dokumen ini menjadi pedoman agar proses deployment Berbagive dapat dilakukan secara terstruktur dan terverifikasi.

Dokumen ini tidak membahas pengembangan fitur baru, aplikasi mobile native, maupun integrasi sistem eksternal di luar kebutuhan deployment. Integrasi payment gateway hanya dibahas pada aspek konfigurasi environment yang diperlukan agar fitur donasi dapat berfungsi sesuai rancangan sistem.

# **BAB 2 – ENVIRONMENT SPECIFICATIONS READINESS**

Seluruh komponen berikut harus diverifikasi SEBELUM deployment dimulai. Centang kolom Status apabila sudah terpenuhi. Kosongkan atau beri catatan apabila belum.

## **2.1 Hardware Requirements — Web / Application Server**

Server yang menjalankan seluruh layanan dalam container Docker: Nginx (reverse proxy \+ static SPA), PHP-FPM Laravel (backend API), PostgreSQL (database), Redis (cache), MinIO (object storage), Queue Worker, Scheduler, dan Tailscale (tunnel HTTPS).

| No. | Komponen | Spesifikasi Direkomendasikan | Spesifikasi Minimum | Status |
| :---: | ----- | ----- | ----- | ----- |
| 1 | Processor (CPU) | 8 core @ 2.9 GHz | 2 core @ 2.0 GHz | \[ \] Ready |
| 2 | Memory (RAM) | 8 GB DDR4 | 8 GB DDR4 | \[ \] Ready |
| 3 | Storage (Disk) | 256 GB SSD | 40 GB SSD NVMe | \[ \] Ready |
| 4 | Operating System | Ubuntu Server 22.04 LTS  | Ubuntu Server 22.04 LTS  | \[ \] Ready |
| 5 | IP Address | IP lokal lab / Cloudflare Tunnel (akses publik melalui trycloudflare.com) | IP Publik Statis \+ reverse DNS | \[ \] Ready |
| 6 | Bandwidth (Internet) | Jaringan lokal lab  | 100 Mbps dedicated | \[ \] Ready |
| 7 | Server Uptime SLA | 99% / bulan | 99.9% / bulan | \[ \] Ready |
| 8 | Hostname |  |  | \[ \] Ready |

**Bagian status Cukup \[\]Ready saja.**

## **2.2 Hardware Requirements — Database Server**

"Menggunakan konfigurasi single-server deployment. Lihat spesifikasi pada tabel Web/Application Server."

## **2.3 Software Requirements**

Software berikut harus terinstal dan berjalan normal di server yang sesuai sebelum deployment dilaksanakan. Kolom 'Server' menunjukkan di mana software tersebut diinstal.

| No | Nama Software | Versi | Server | Peruntukan / Role | Wajib | Status | Verifikasi |
| ----- | ----- | ----- | ----- | ----- | ----- | ----- | ----- |
| 1 | Nginx | 1.24.x+ | Web Server | Reverse proxy ke PHP-FPM \+ server Vue build | Wajib  | Ready  |  |
| 2 | PHP | 8.4.x+ | Web Server | Runtime laravel 12 | Wajib  | Ready  |  |
| 3 | PHP-FPM | 8.4.x+ | Web Server | FastCGI Process manager untuk PHP | Wajib  | Ready  |  |
| 4 | Composer | 2.x+ | Web Server | Package Manager PHP / Laravel | Wajib  | Ready  |  |
| 5 | [Node.js](http://Node.js) \+ npm | 20 LTS | Web Server | Build asset vue 3 (VIte) | Wajib  | Ready  |  |
| 6 | Redis | 7.0.x | Web Server | Cache \+ session Laravel | opsional | Ready  |  |
| 7 | Git | 2.40.x | Web Server | Pull source code dari repository, CI/CD | Wajib  | Ready  |  |
| 8 | UFW |  | Web Server | Firewall OS | opsional  | Ready  |  |
| 9 | cron |  | Web Server | Scheduler Laravel | Opsional (pakai scheduler container) | Ready  |  |
| 10 | PostgreSQL | 16.x.+ | DB Server | Database Utama | Wajib  | Ready  |  |
| 11 | pg\_dump | 16.x.+ | DB Server | Database Backup | Wajib  | Ready  |  |
| 12 | Certbot | \>= 2.x | Web Server | SSL/TLS (jika domain publik) | opsional | Ready  |  |
| 13 | Docker | 24.x.+ | Web/DB | Kontainerisasi | Wajib  | Ready  |  |
| 14 | MInio | latest | Web/db | Object storage S3-compatible | Wajib  | Ready  |  |
| 15 | tailscale | latest | Web Server | Tunnel HTTPS publik | Wajib  | Ready  |  |
| 16 | Docker Compose | v2.x | Web/db | Orchestration multi-container | Wajib  | Ready  |  |
| 17 | Laravel Queue Worker  |  | Web Server | Proses background job (email, notifikasi) | Wajib  | Ready  |  |
| 18  | Laravel Scheduler |  | Web Server | Task terjadwal (cron di container) | Wajib  | Ready  |  |

## **2.4 Network & Firewall Requirements**

Konfigurasi port dan firewall yang harus dibuka di masing-masing server. Port yang TIDAK tercantum harus dalam kondisi TERTUTUP (blocked/DROP).

Verifikasi jaringan: pastikan Web Server TIDAK bisa diakses langsung ke port DB (3306) dari internet. DB Server hanya menerima koneksi dari IP Web Server melalui jaringan internal/private.

### **2.4.1 Web / Application Server  \- Aturan Firewall (SESUAIKAN)**

| No. | Port | Protokol | Nama Service | Jenis Akses | Keterangan & Third Party |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | 80 | TCP | HTTP/NGinx | Public | SPA statis \+ reverse proxy ke PHP-FPM. Pakai cloudflared, HTTPS auto |
| 2 | 22 | TCP | SSH | Restricted (admin only) | Akses remote server |
| 3 | \- | \- | PostgreSQL (5432), Redis (6379), MinIO (9000/9001), PHP-FPM (9000) | Internal Docker | Tidak ada port yang di-expose ke host. Hanya bisa diakses antar-container via Docker bridge network berbagive-net |

### **2.4.2 Database Server \-  Aturan Firewall** 

| No. | Port | Protokol | Nama Service | Jenis Akses | Bandwidth Min. | Keterangan |
| :---: | ----- | ----- | ----- | ----- | ----- | ----- |
| 1 | 22 | TCP | SSH | Restricted | 1 Mbps | Akses remote admin |
| 2 | 5432 | TCP | PostgreSQL | Internal Docker  | N/A | Single server, hanya diakses container app via Docker network |

## **2.5 Isi Folder Source — Development / Staging Environment**

Tabel berikut mendokumentasikan seluruh komponen yang ada di environment development/staging dan akan di-transfer ke server production. Pengelola harus memverifikasi bahwa seluruh item di bawah ini sudah tersedia dan siap sebelum proses deployment dimulai.

Catatan pengisian: PATH FOLDER di bawah adalah contoh. Sesuaikan dengan struktur proyek aktual. Tandai kolom Status jika item sudah diverifikasi tersedia dan siap di-deploy.

| No. | Kategori | Nama Item / File | Deskripsi & Peruntukan | Path Folder (Source Dev) | Status |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | Kode (Source) | backend/app/, backend/routes/, backend/config/ | Source Laravel 12: controller, model, service, middleware, route API | backend/ | \[ \] Ready |
| 2 | Kode (Source) | frontend/src/, frontend/dist/ | Source Vue 3 \+ hasil build Vite | frontend/ | \[ \] Ready |
| 3 | Kode (Source) | .env.docker, frontend/.env.production | Template environment variables, dasar pembuatan .env production | ./.env.docker, frontend/.env.production | \[ \] Ready |
| 4 | Kode (Source) | docker-compose.yml, Dockerfile, .dockerignore | Orchestrasi container, build image, ignore list | ./.dockerignore, ./Dockerfile, ./docker-compose.yml | \[ \] Ready |
| 5 | Kode (Source) | backend/composer.json / composer.lock, frontend/package.json / package-lock.json | Dependensi PHP (Composer) & JS (npm untuk build Vite), instalasi deterministik | backend/composer.json, frontend/package.json | \[ \] Ready |
| 6 | Database (DB) | backend/database/migrations/ | Migrasi Laravel — struktur tabel PostgreSQL | backend/database/migrations/ | \[ \] Ready |
| 7 | Database (DB) | backend/database/seeders/ | Data awal: role, kategori campaign, akun superadmin | backend/database/seeders/ | \[ \] Ready / \[ \] N/A |
| 8 | Software/Config | nginx/default.conf | Konfigurasi Nginx: reverse proxy ke PHP-FPM \+ serve Vue build \+ proxy storage ke MinIO | nginx/default.conf | \[ \] Ready |
| 9 | Software/Config | ddl/\*.sql (6 file), insert.sql | DDL mentah PostgreSQL (skema inti \+ constraint \+ view \+ SP \+ function \+ index) \+ data CSV | ddl/, insert.sql | \[ \] Ready |
| 10 | Software/Config | deploy.sh, finalize.sh | Skrip otomatis deployment \+ finalisasi post-DDL | ./deploy.sh, ./finalize.sh | \[ \] Ready |
| 11 | File Lain | backend/storage/app/public/ | File upload pengguna di environment development (runtime) | backend/storage/app/public/ | \[ \] Ready / \[ \] N/A |
| 12 | File Lain | backend/routes/console.php | Definisi scheduled tasks (close campaign, archive notif, backup DB) | backend/routes/console.php | \[ \] Ready |

## **2.6 Isi Folder Target — Production Server**

Tabel berikut menunjukkan lokasi akhir (path target) dari setiap komponen setelah proses deployment di server production. Pengelola menggunakan tabel ini sebagai panduan penempatan file dan verifikasi hasil deployment.

Catatan: Kolom 'Server Name' diisi dengan nama/hostname server production yang relevan sesuai dengan yang telah didefinisikan di bagian 2.1 dan 2.2. Contoh: 'app-server' atau 'db-server'.

| . | Kategori | Nama Item | Peruntukan di Production | Path Folder (Target Production) | Server Name |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | Kode (Source) | backend/ | Source Laravel 12 di-copy ke image saat docker compose build — berada di /var/www dalam container berbagive-app | Layer image Docker (COPY di Dockerfile) → /var/www (di container app) | Docker host |
| 2 | Kode (Source) | frontend/dist/ | File frontend statis hasil build Vite — dilayani langsung oleh Nginx via bind mount | ./frontend/dist:/var/www/frontend-dist (bind mount ke container nginx) | Docker host |
| 3 | Kode (Source) | .env | File environment variables production (dibuat dari .env.docker, TIDAK di-commit ke Git) | ./.env (root proyek, dibaca oleh docker-compose) | Docker host |
| 4 | Kode (Source) | docker-compose.yml | Definisi 7 container: app, nginx, db, redis, minio, queue, scheduler | ./docker-compose.yml | Docker host |
| 5 | Kode (Source) | frontend/node\_modules/ | Dependensi Node.js hasil npm ci saat build (di host, bukan di server) | ./frontend/node\_modules/ (host, temporary) | Docker host |
| 6 | Database (DB) | berbagive (PostgreSQL) | Database production. Data tersimpan di volume Docker pgdata | Volume Docker: pgdata:/var/lib/postgresql/data | Docker host |
| 7 | Database (DB) | Backup DB rutin | File backup pg\_dump harian via cron host | /backup/db/\[YYYY-MM-DD\]/ | Docker host |
| 8 | Software/Config | nginx/default.conf | Konfigurasi virtual host Nginx untuk reverse proxy \+ serve frontend | Bind mount: ./nginx/default.conf:/etc/nginx/conf.d/default.conf:ro | Docker host |
| 9 | Software/Config | MinIO bucket | Object storage S3-compatible untuk file upload (foto profil, dokumen, bukti campaign) | Volume Docker: minio-data:/data — bucket: berbagive (public) | Docker host |
| 10 | Software/Config | Tailscale tunnel | Akses HTTPS publik — diatur di host, bukan di container | Host: sudo tailscale funnel \--bg http://localhost:\[HTTP\_PORT\] | Docker host |
| 11 | File Lain | backend/storage/app/public/ | Symlink storage untuk akses file via Nginx (development fallback) | Bind mount: ./backend/storage/app/public:/var/www/storage/app/public:ro (ke container nginx) | Docker host |
| 12 | File Lain | Scheduler tasks | Task terjadwal berjalan di container berbagive-scheduler (loop schedule:run tiap 60 detik) — definisi task di backend/routes/console.php | Container berbagive-scheduler — tidak perlu cron host | Docker host |
| 13 | File Lain | Backup App Files | Backup file aplikasi dan .env sebelum deployment | /backup/app/\[versi\]/ | Docker host |

# **BAB 3 – LANGKAH DEPLOYMENT (STEP BY STEP)**

Laksanakan langkah-langkah berikut secara berurutan. JANGAN melanjutkan ke langkah berikutnya apabila indikator keberhasilan langkah sebelumnya belum terpenuhi. Waktu deployment yang disarankan: dini hari pukul 01.00–04.00 WIB saat trafik pengguna paling rendah.

Konvensi placeholder: \[nama-app\] \= nama direktori aplikasi, \[domain.com\] \= domain production, \[db-server-IP\] \= IP internal database server, \[nama\_db\_prod\] \= nama database, \[app\_user\] \= user database production.

| No. | Langkah | Tujuan & Deskripsi | Perintah / Aksi Utama (termasuk copy Source → Target) | Indikator Keberhasilan |
| :---: | ----- | ----- | ----- | ----- |
| 1 | Pre-Deployment Checklist & Go/No-Go | Memastikan prasyarat BAB 2 terpenuhi, backup dibuat, tim siap & ada persetujuan.  | 1\. Centang tabel BAB 2 (HW, SW, Network, Folder Source). 2\. Backup DB lama (jika ada): \`docker exec postgres pg\_dump \-U \[app\_user\] \[nama\_db\_prod\] \\ | Semua checklist centang Ready. Backup DB tersedia dan berukuran \> 0 byte. Halaman maintenance aktif dan dapat diakses dari browser eksternal. Persetujuan Go tercatat. |
| 2 | Persiapan & Instalasi docker | Memastikan host punya Docker Engine \+ Compose plugin dan paket dasar (Git, UFW, cron).  | sudo apt update && sudo apt upgrade \-y sudo apt install \-y git ufw cron Install Docker Engine \+ Compose plugin (get.docker.com). Verifikasi: docker \--version && docker compose version && git \--version | Output versi sesuai Tabel 2.3 (Docker 24.x+, Git 2.40+). docker compose version muncul tanpa error.  |
| 3 | Konfigurasi Firewall UFW | Membuka hanya port publik (80 / 443\) \+ SSH; tutup sisanya. DB/Redis tidak diekspos.  | sudo ufw allow OpenSSH sudo ufw allow 80/tcp sudo ufw allow 443/tcp (jika pakai SSL) sudo ufw enable && sudo ufw status verbose  | Status active. Hanya 22, 80, 443 ALLOW. Port 5432/6379/8000 tidak tampil dari luar.  |
| 4 | COPY: Ambil Source Code ke Server  | Menyalin source (Folder Source 2.5) ke direktori production di host (Folder Target 2.6) via Git.  | sudo mkdir \-p \[deploy-dir\] && cd \[deploy-dir\] sudo git clone \[URL\_REPO\] . sudo git checkout \[tag-versi\] Verifikasi: git rev-parse \--short HEAD & ls \-la  | Direktori terisi. Ada docker-compose.prod.yml, Dockerfile, artisan, composer.json. Tag sesuai rilis.  |
| 5 | Buat & konfigurasi .env Production | Menyalin source code aplikasi dari environment development ke direktori production di Web Server. Ini adalah langkah COPY dari Folder Source (Tabel 2.5) ke Folder Target (Tabel 2.6). | cp .env.example .env && nano .env Set: APP\_ENV=production, APP\_DEBUG=false, APP\_URL=http(s)://\[domain-atau-IP\], DB\_CONNECTION=pgsql, DB\_HOST=postgres, DB\_PORT=5432, DB\_DATABASE=\[nama\_db\_prod\], DB\_USERNAME=\[app\_user\], DB\_PASSWORD=\[password\_kuat\], REDIS\_HOST=redis, CACHE\_DRIVER=redis, SESSION\_DRIVER=redis, QUEUE\_CONNECTION=redis chmod 600 .env | .env ada, APP\_ENV=production, APP\_DEBUG=false. DB mengarah ke service postgres. Tidak ada nilai dev/localhost tersisa. Permission 600\. |
| 6 | Build/Pull Docker Image | Menyalin file konfigurasi Nginx dari Folder Source ke lokasi konfigurasi Nginx di Web Server (Tabel 2.6 baris 8–9). | Jika build di server: docker compose \-f docker-compose.prod.yml build \--no-cache Jika dari registry: docker compose \-f docker-compose.prod.yml pull | Build/pull selesai tanpa error. Image muncul di docker images. |
| 7 | Jalankan contrainer production | Menyalakan seluruh service: ngix, app, postgres, redis | docker compose \-f docker-compose.prod.yml up \-d docker compose \-f docker-compose.prod.yml ps | Contrainer ngiz, app, postgre, redis status Up/Healty |
| 8 | Inisialisasi Laravel (Key, Migarate, Seed) | Menyiapkan app key, struktur DB (migration), dan data awal (seed) di PostgreSQL.  | docker exec \-it app php artisan key:generate \--force docker exec \-it app php artisan migrate \--force (opsional) docker exec \-it app php artisan db:seed \--force Verifikasi: docker exec \-it postgres psql \-U \[app\_user\] \-d \[nama\_db\_prod\] \-c "\\dt" | Migrate sukses tanpa error. \\dt menampilkan daftar tabel sesuai skema. Data seed (role/superadmin) ada.  |
| 9 | Build Frontend (Vue \+ Vite) & cache | Memastikan Vue ter-build dan config laravel tercache | Jika build belum di Dockerfile: docker exec \-it app npm ci && docker exec \-it app npm run build docker exec \-it app php artisan storage:link docker exec \-it app php artisan config:cache && route:cache | Folder public/build/ berisi asset. storage:link aktif. Tidak ada error build/cache.  |
| 10 | Konfigurasi Akses Publik / SSL | Memastikan aplikasi dapat diakses; aktifkan HTTPS bila pakai domain publik.  | Lab lokal (HTTP): cukup pastikan Nginx container expose port 80 → akses http://\[IP-server\]. Domain publik (HTTPS): terminate SSL (Certbot di host / cert di container), buka 443, set redirect 80→443. | http(s)://\[domain-atau-IP\] dapat diakses. Jika SSL: gembok aktif, redirect 80→443 jalan. |
| 11 | Registrasi Cron Backup Otomatis | Menjadwalkan backup DB & uploads harian via cron host | sudo crontab \-e, contoh: \`0 2    • docker exec postgres pg\_dump \-U \[app\_user\] \[nama\_db\_prod\] \\ | gzip \> /backup/db/$(date \+%Y%m%d).sql.gz 30 2    • docker run \--rm \-v berbagive\_storage:/data \-v /backup:/backup alpine tar czf /backup/uploads\_$(date \+%Y%m%d).tar.gz \-C /data . sudo crontab \-l\` 12 |
| 12 | Smoke test | Verifikasi fungsi inti sebelum maintenance dimatikan | Jalankan item BAB 4: akses URL, login, health check, list campaign, donasi, upload file, dsb (dari jaringan eksternal). | Seluruh item prioritass **Pass**. tidak ada eror 500 |
| 13 | Post-Deployment Monitoring & Go-Live | Pantau stabilitas 60 menit pertama, lalu buka akses. | docker compose \-f docker-compose.prod.yml logs \-f docker stats , df \-h Setelah 60 menit aman: docker exec \-it app php artisan up Kirim notifikasi "deployment berhasil  | Tidak ada error fatal di log 60 menit. CPU \< 70%, RAM \< 80%, disk sisa \> 30%. Maintenance dimatikan, tim dapat notifikasi. |

# **BAB 4 – SMOKE TEST (BASIC FUNCTIONALITY TEST)**

Smoke test dilakukan SEGERA setelah langkah deployment selesai untuk memverifikasi bahwa fungsi-fungsi utama aplikasi berjalan normal di production. Bukan pengujian menyeluruh — hanya verifikasi cepat sebelum maintenance page dinonaktifkan.

Seluruh pengujian dilakukan dari jaringan EKSTERNAL (bukan dari server) menggunakan browser atau tools curl / Postman. Tandai hasil di kolom Hasil.

| No. | Fungsi yang Diuji | Parameter Pengujian | Langkah / Cara Uji | Indikator Keberhasilan |
| :---: | ----- | ----- | ----- | ----- |
| 1 | Akses URL Production | URL: http://\[IP-server\] atau https://\[tailscale-funnel-url\] | Buka URL di browser dari jaringan eksternal | HTTP 200\. Halaman utama (SPA) tampil \< 3 detik. Tidak ada koneksi refused. |
| 2 | Halaman Login / Autentikasi | Via SPA di /login atau API: POST /api/v1/auth/login | Buka halaman login, masukkan email & password valid | Login berhasil, response mengandung token Sanctum \+ data user. Redirect sesuai role (DONATUR → /campaigns, KOMUNITAS → /communities/dashboard, SUPERADMIN → /dashboard). |
| 3 | Health Check API | GET \[URL\]/api/v1/health | curl \-X GET \[URL\]/api/v1/health | HTTP 200\. {"status":"ok","checks":{"database":{"status":"ok"},"redis":{"status":"ok"},"storage":{"status":"ok"}}}. Response time \< 500ms. |
| 4 | Koneksi Database (List Campaign) | GET /api/v1/campaigns/search (tanpa auth) | Buka halaman campaign atau curl /api/v1/campaigns/search | Data campaign tampil dari PostgreSQL. Tidak ada error 500 / "DB connection failed". |
| 5 | Buat Campaign | Login sebagai KOMUNITAS | POST /api/v1/communities/campaigns dengan data valid | Campaign tersimpan, status menunggu\_review, muncul di riwayat komunitas. |
| 6 | Review Campaign (Superadmin) | Login sebagai SUPERADMIN | POST /api/v1/superadmin/campaigns/{id}/approve | Status campaign berubah jadi aktif, muncul di daftar publik. |
| 7 | Alur Donasi | Login DONATUR, nominal Rp10.000 | POST /api/v1/donations dengan id\_campaign, nominal, is\_anonim | Donasi tercatat status pending. Riwayat donasi muncul di GET /me/donations. |
| 8 | Update Status Pembayaran | Patch payment status | PATCH /api/v1/donations/{id}/payment-status ke berhasil | Dana terkumpul campaign bertambah sesuai nominal. |
| 9 | Upload File (MinIO) | File uji \< 5 MB | Upload dokumen legalitas/bukti via fitur komunitas | Upload sukses, file bisa diakses via URL /storage/.... |
| 10 | Notifikasi & Email | Trigger lupa password | POST /api/v1/auth/forgot-password dengan email terdaftar | Response sukses. Cek Mailtrap dashboard (bukan inbox real) — email terkirim. Cek docker compose logs queue — queue worker memproses job. |
| 11 | Dashboard Komunitas | Login KOMUNITAS | GET /api/v1/communities/dashboard | Statistik campaign (total, aktif, dana terkumpul) tampil. |
| 12 | Dashboard Superadmin | Login SUPERADMIN | GET /api/v1/superadmin/dashboard | Statistik platform (total users, donasi, campaign) tampil. |
| 13 | Responsivitas Mobile | View 375px (Chrome DevTools) | Buka home, login, dashboard di mode mobile | Layout menyesuaikan, tanpa horizontal scroll, semua tombol bisa di-tap. |
| 14 | Security Header HTTP | curl \-I \[URL\] | Periksa response header | Ada X-Content-Type-Options: nosniff, X-Frame-Options: SAMEORIGIN. |
| 15 | Performa Loading H | Chrome DevTools Network | Reload home, amati waktu l | Total \< 5 detik (SPA \+ API call), tidak ada resource gagal (404/500). |

# **BAB 5 – BACKUP-RECOVERY PLAN**

Prosedur yang dijalankan apabila server production mengalami kegagalan sistem (crash), kerusakan data, atau kondisi darurat. Dirancang untuk memulihkan layanan sesuai target RTO dan RPO berikut:

Recovery Time Objective (RTO): Maksimal 4 jam sejak kegagalan terdeteksi. Recovery Point Objective (RPO): Data dapat dikembalikan ke kondisi backup terakhir (maksimal 24 jam sebelum kegagalan).

### **5.1 Jadwal & Strategi Backup Rutin**

| No. | Tipe Backup | Metode | Frekuensi & Jadwal | Lokasi Penyimpanan | Retensi |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | Database (PostgreSQL) | docker compose exec berbagive-db pg\_dump \-U berbagive berbagive | gzip \> /backup/db/$(date \+%Y%m%d\_%H%M).sql.gz | Per jam (via scheduler container: php artisan backup:database — lihat routes/console.php baris 41\) \+ Harian 02:00 (cron host fallback) | Lokal: /backup/db/ | 7 hari |
| 2 | Volume MinIO (Upload Pengguna) | docker run \--rm \-v berbagive\_minio-data:/data \-v /backup:/backup alpine tar czf /backup/minio/$(date \+%Y%m%d).tar.gz \-C /data . | Harian 02:30 WIB (cron host) | Lokal: /backup/minio/ | 7 hari |
| 3 | Source Code \+ .env \+ docker-compose | tar czf /backup/app/berbagive-v1.0.0.tar.gz \--exclude=frontend/node\_modules \--exclude=.git . | Setiap deployment (sebelum update) \+ mingguan | Lokal: /backup/app/ | 3 versi terakhir |
| 4 | Docker Image | Tidak perlu di-backup terpisah — image bisa di-rebuild dari source \+ Git | — | — | Factory rebuild via docker compose build |

### **5.2 Prosedur Recovery (Jika Server Crash)** 

Prosedur yang dijalankan apabila diperlukan pemeliharaan terencana (planned maintenance). Misalnya upgrade server, update major OS, perbaikan bug kritis, atau penggantian infrastruktur. Tujuannya meminimalkan dampak ke pengguna dan memastikan sistem kembali stabil dalam waktu terkontrol.

| No. | Langkah Recovery | Tujuan | Perintah / Aksi Utama | Indikator Keberhasilan |
| :---: | ----- | ----- | ----- | ----- |
| 1 | Identifikasi & Analisis Kegagalan | Menentukan penyebab crash | • docker compose ps & docker compose logs • Cek resource host: df \-h, htop • Catat waktu kegagalan • Hubungi tim | Penyebab teridentifikasi (host down / container crash / data corrupt / disk full). Tim siap. |
| 2 | Restore Host / Rebuild Container | Menyiapkan kembali environment | • Pastikan Docker host sehat • cd \[deploy-dir\] && git pull • docker compose build \--pull • docker compose up \-d | Semua container docker compose ps status Up (healthy) |
| 3 | Restore Database dari Backup | Memulihkan data PostgreSQL dari backup harian | ls \-lh /backup/db/ — pilih backup sebelum insiden **Restore:** gunzip \-c /backup/db/\[YYYYMMDD\].sql.gz | docker compose exec \-T berbagive-db psql \-U berbagive \-d berbagive **Verifikasi:** docker compose exec \-T berbagive-db psql \-U berbagive \-d berbagive \-c "SELECT COUNT(\*) FROM campaign;" | Data kembali. Query verifikasi mengembalikan jumlah record yang wajar. |
| 4 | Restore Volume MinIO (Upload Pengguna) | Memulihkan file upload | docker run \--rm \-v berbagive\_minio-data:/data \-v /backup:/backup alpine tar xzf /backup/minio/\[YYYYMMDD\].tar.gz \-C /data | File upload tersedia kembali. Dokumen legalitas, bukti pencairan, foto campaign bisa diakses via /storage/.... |
| 5 | Finalisasi Laravel | Menyiapkan cache, symlink, optimize | docker compose exec berbagive-app php artisan key:generate \--force docker compose exec berbagive-app php artisan storage:link docker compose exec berbagive-app php artisan optimize | Tidak ada error artisan. Symlink storage aktif. |
| 6 | Restart Semua Service | Menghidupkan ulang seluruh container | docker compose down docker compose up \-d **Verifikasi:** docker compose ps | Semua container status Up. |
| 7 | Smoke Test Post-Recovery | Verifikasi fungsi dasar sebelum dibuka | Jalankan item prioritas BAB 4 (akses URL, health check, login, list campaign, donasi) \+ verifikasi data transaksi tidak hilang \+ pantau log 15 menit | Item smoke test prioritas lulus. Data kritikal valid. Tidak ada error baru. |
| 8 | Notifikasi & Dokumentasi Insiden | Memberi tahu stakeholder | Matikan maintenance: akses normal kembali • Notifikasi ke PM, dosen, pengguna • Isi Incident Report (waktu down, penyebab, tindakan, RTO aktual) | Semua stakeholder dapat notifikasi. Incident Report selesai \< 24 jam. |

# **BAB 6 –PENUTUP**

Dokumen ini memuat seluruh komponen standar deployment planning mulai dari pemetaan environment per server, mapping folder source ke target, langkah-langkah deployment beserta perintah aktual, smoke test, hingga backup-recovery. Dengan mengikuti prosedur ini secara konsisten, risiko downtime dan kehilangan data dapat diminimalkan secara signifikan.

## **Checklist Final Sebelum Go-Live**

| No. | Item Verifikasi Final | Status | Catatan |
| :---: | ----- | ----- | ----- |
| 1 | Semua stakeholder dapat notifikasi. Incident Report selesai \< 24 jam. | \[ \] Done |  |
| 2 | File .env production sudah dikonfigurasi (APP\_ENV=production, APP\_DEBUG=false, APP\_KEY ter-generate) | \[ \] Done |  |
| 3 | Semua container (app, postgres, redis, nginx) berjalan normal (docker compose ps → healthy) | \[ \] Done |  |
| 4 | Migrasi database sudah dijalankan (php artisan migrate \--force) tanpa error | \[ \] Done |  |
| 5 | config:cache, route:cache, view:cache, dan storage:link sudah dijalankan | \[ \] Done |  |
| 6 | Koneksi PostgreSQL & Redis dari container app berhasil | \[ \] Done |  |
| 7 | Reverse Nginx reverse proxy aktif \+ Tailscale tunnel HTTPS | \[ \] Done |  |
| 8 | Smoke test (BAB 4\) lulus semua skenario  | \[ \] Done |  |
| 9 | Backup pertama (PostgreSQL \+ volume upload) berhasil dibuat & terverifikasi  | \[ \] Done |  |
| 10 | Prosedur recovery (BAB 5\) sudah didokumentasikan & dipahami tim  | \[ \] Done |  |
| 11 | Monitoring / log container dapat diakses (docker compose logs)  | \[ \] Done |  |
| 12 | Akun superadmin awal sudah dibuat & dapat login  | \[ \] Done |  |

