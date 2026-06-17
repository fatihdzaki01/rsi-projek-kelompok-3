   
**LAPORAN**

**DEPLOYMENT PLANNING**

**KE SERVER PRODUCTION**

Rekayasa Sistem Informasi – Aplikasi Web Client-Server

| Nama Proyek | Berbagive |
| :---- | :---- |
| **Versi Rilis** | v1.0.0 |
| **Tanggal Rencana Deployment** | \[15 / 06 / 2026\] |
| **Server Target (Web)** | \[IP / Hostname Server Web Production\] |
| **Server Target (DB)** | \[IP / Hostname Server Database Production\] |
| **Mata Kuliah** | Rekayasa Sistem Informasi |
| **Dosen Pengampu** | Bambang Widoyono, S.T., M.T.I. |
| **Status Dokumen** | Draft |

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

Aplikasi yang akan dilakukan deployment yaitu Berbagive, Berbagive sendiri adalah sistem informasi penyaluran dan pengelolaan bantuan sosial berbasis komunitas yang dikembangkan dalam bentuk aplikasi web. Berbagive didesain untuk memfasilitasi proses penggalang dana dengan masyarakat umum sebagai donatur. Sistem ini tidak hanya menyediakan fitur donasi, tetapi juga mendukung proses pengajuan campaign, verifikasi komunitas, pengajuan pencairan dana, monitoring penggunaan dana, notifikasi, dashboard komunitas, dashboard superadmin, serta pengelolaan data oleh superadmin.

Berdasarkan ruang lingkup pada FSD, Berbagive dikembangkan untuk menjawab kebutuhan transparansi dan akuntabilitas dalam pengelolaan dana sosial. Sistem ini mempunyai mekanisme verifikasi legalitas komunitas, pengajuan pencairan dana bertahap yang disertai laporan penggunaan dana, serta fitur monitoring campaign yang menampilkan riwayat dana masuk dan dana keluar. Oleh karena itu, proses deployment Berbagive perlu direncanakan secara sistematis agar aplikasi dapat berjalan secara stabil pada server production, mendukung akses pengguna sesuai peran, menjaga keamanan data, serta memastikan fitur utama aplikasi dapat digunakan dengan baik setelah go-live. 

**\[jangan di ubah, tambahkan saja penjelasan aplikasi dari FSD\]**

## **1.2 Tujuan**

* Mendokumentasikan persyaratan environment hardware per jenis server yang diperlukan.

* Memetakan isi folder source (development) dan folder target (production) secara eksplisit.

* Menyediakan panduan langkah deployment lengkap termasuk aksi copy/transfer komponen antar environment.

* Menetapkan prosedur smoke test untuk verifikasi fungsionalitas pasca-deployment.

* Menyediakan Backup-Recovery Plan (server crash) dan Fallback Plan (maintenance terencana).

* Menyediakan Fallback plan untuk mendukung proses pemulihan jika terjadi kendala selama maintenance atau deployment terencana.

* Memastikan seluruh komponen utama aplikasi Berbagive, meliputi frontend, backend API, database, konfigurasi environment, storage file, dan layanan pendukung lainnya telah siap digunakan pada lingkungan production. 

* Menjadi pedoman bagi tim pengembang dalam melakukan deployment aplikasi Berbagive secara terstruktur, terdokumentasi, dan dapat diverifikasi melalui checklist serta indikator keberhasilan pada setiap tahap. 

* **\[Penjelasan isi\]**

## **1.3 Ruang Lingkup**

Dokumen ini mencakup proses deployment aplikasi Berbagive, yaitu sistem informasi donasi berbasis komunitas yang dikembangkan untuk memfasilitasi kegiatan penggalangan dan penyaluran dana secara transparan antara komunitas penggalang dana dengan para donatur. Deployment dilakukan untuk memindahkan aplikasi dari lingkungan development/staging ke lingkungan production agar sistem dapat diakses oleh pengguna akhir melalui browser secara stabil, aman, dan sesuai dengan kebutuhan operasional.

Ruang lingkup deployment mencakup seluruh komponen utama aplikasi Berbagive, yaitu frontend, backend, database, konfigurasi environment, serta file dan komponen pendukung yang dibutuhkan agar aplikasi dapat berjalan pada server production. Sistem Berbagive dikembangkan sebagai aplikasi berbasis web dengan Vue pada sisi frontend dan Laravel sebagai backend. Oleh karena itu, proses deployment perlu memperhatikan kesiapan source code, dependency aplikasi, konfigurasi database, pengaturan file environment, serta pengamanan akses menggunakan protokol HTTPS.

Secara arsitektural, deployment Berbagive dirancang menggunakan arsitektur web client-server dengan pemisahan komponen frontend, backend API, dan database. Bentuk arsitektur yang diadopsi mengarah pada three-tier architecture, yaitu presentation layer pada frontend berbasis Vue, application layer pada backend berbasis Laravel, dan data layer pada database. Deployment production direncanakan menggunakan dua server utama, yaitu Web/Application Server untuk menjalankan frontend dan backend aplikasi, serta Database Server untuk menyimpan dan mengelola data utama sistem. Sistem operasi server mengikuti ketentuan pada template deployment, yaitu **Ubuntu Server 22.04 LTS**, atau dapat disesuaikan kembali dengan environment production yang digunakan oleh tim.

Berdasarkan ruang lingkup sistem, aplikasi Berbagive mencakup fitur utama seperti autentikasi, manajemen profil, donasi, campaign, pencairan dana, monitoring, notifikasi, dashboard komunitas, dashboard superadmin, serta pengelolaan dan verifikasi komunitas. Sistem juga mendukung proses bisnis utama berupa pendaftaran komunitas, pengajuan dan review campaign, donasi online, pencairan dana, monitoring aliran dana, dan moderasi platform.

Dokumen deployment ini berfokus pada tahapan teknis untuk menyiapkan aplikasi pada lingkungan production, meliputi persiapan server, transfer source code, konfigurasi environment dan database, instalasi dependency, pengujian dasar (smoke test), monitoring awal, serta backup dan recovery. Dokumen ini menjadi pedoman agar proses deployment Berbagive dapat dilakukan secara terstruktur dan terverifikasi.

Dokumen ini tidak membahas pengembangan fitur baru, aplikasi mobile native, maupun integrasi sistem eksternal di luar kebutuhan deployment. Integrasi payment gateway hanya dibahas pada aspek konfigurasi environment yang diperlukan agar fitur donasi dapat berfungsi sesuai rancangan sistem.

**\[Penjelasan ruanglingkup singkat, jumlah server, bentuk architektur yg diadopsi, OS\]**

# **BAB 2 – ENVIRONMENT SPECIFICATIONS READINESS**

Seluruh komponen berikut harus diverifikasi SEBELUM deployment dimulai. Centang kolom Status apabila sudah terpenuhi. Kosongkan atau beri catatan apabila belum.

## **2.1 Hardware Requirements — Web / Application Server**

Server yang menjalankan Nginx (reverse proxy \+ static files), Node.js backend, Redis, PM2, dan Certbot.

| No. | Komponen | Spesifikasi Direkomendasikan | Spesifikasi Minimum | Status |
| :---: | ----- | ----- | ----- | ----- |
| 1 | Processor (CPU) | 8 core @ 2.9 GHz | 2 core @ 2.0 GHz | \[ \] Ready |
| 2 | Memory (RAM) | 8 GB DDR4 | 4 GB DDR4 | \[ \] Ready |
| 3 | Storage (Disk) | 256 GB SSD | 20 GB SSD NVMe | \[ \] Ready |
| 4 | Operating System | Ubuntu Server 22.04 LTS / Windows 11 64-bit | Ubuntu Server 22.04 LTS / windows 10 64-bit | \[ \] Ready |
| 5 | IP Address | IP lokal lab | IP Publik Statis \+ reverse DNS | \[ \] Ready |
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
| 2 | PHP | 8.2.x+ | Web Server | Runtime laravel 12 | Wajib  | Ready  |  |
| 3 | PHP-FPM | 8.2.x+ | Web Server | FastCGI Process manager untuk PHP | Wajib  | Ready  |  |
| 4 | Composer | 2.x+ | Web Server | Package Manager PHP / Laravel | Wajib  | Ready  |  |
| 5 | [Node.js](http://Node.js) \+ npm | 20 LTS/ 10.x+ | Web Server | Build asset vue 3 (VIte) | Wajib  | Ready  |  |
| 6 | Redis | 7.0.x | Web Server | Cache \+ session Laravel | opsional | Ready  |  |
| 7 | Git | 2.40.x | Web Server | Pull source code dari repository, CI/CD | Wajib  | Ready  |  |
| 8 | UFW |  | Web Server | Firewall OS | Wajib  | Ready  |  |
| 9 | cron |  | Web Server | Scheduler Laravel | Wajib  | Ready  |  |
| 10 | PostgreSQL | 15.x.+ | DB Server | Database Utama | Wajib  | Ready  |  |
| 11 | pg\_dump | 15.x.+ | DB Server | Database Backup | Wajib  | Ready  |  |
| 12 | Certbot | \>= 2.x | Web Server | SSL/TLS (jika domain publik) | opsional | Ready  |  |
| 13 | Docker | 24.x.+ | Web/DB | Kontainerisasi | Wajib  | Ready  |  |

Keterangan: (\*) Wajib apabila backend menggunakan framework Node.js/Express. Sesuaikan dengan stack teknologi yang digunakan pada proyek.

**Bagian Wajib ditentukan sendiri.**

## **2.4 Network & Firewall Requirements**

Konfigurasi port dan firewall yang harus dibuka di masing-masing server. Port yang TIDAK tercantum harus dalam kondisi TERTUTUP (blocked/DROP).

Verifikasi jaringan: pastikan Web Server TIDAK bisa diakses langsung ke port DB (3306) dari internet. DB Server hanya menerima koneksi dari IP Web Server melalui jaringan internal/private.

### **2.4.1 Web / Application Server  \- Aturan Firewall (SESUAIKAN)**

| No. | Port | Protokol | Nama Service | Jenis Akses | Keterangan & Third Party |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | 80 | TCP | HTTP/NGinx | Public | Serve Vue 3 static files & reverse proxy ke Laravel. Lab lokal tidak pakai SSL, gunakan HTTP |
| 2 | 8000 | TCP | Laravel App | Localhost | Port development Laravel. Hanya Nginx yang boleh akses via proxy\_pass |
| 3 | 6379 | TCP | Redis | Localhost | Cache & session Laravel. Bind ke 127.0.0.1 |
| 4 | 5434 | TCP | PostgreSQL | Localhost | Database utama. Hanya Laravel yang boleh akses, tidak dibuka ke publik |

### **2.4.2 Database Server \-  Aturan Firewall (SESUAIKAN)**

| No. | Port | Protokol | Nama Service | Jenis Akses | Bandwidth Min. | Keterangan |
| :---: | ----- | ----- | ----- | ----- | ----- | ----- |
| 1 | 22 | TCP | SSH | Restricted — whitelist IP admin saja | 1 Mbps | Akses remote ke PC lab. Gunakan SSH key jika memungkinkan |
| 2 | 5434 | TCP | PostgreSQL | Localhost  | N/A | Menggantikan MySQL port 3306\. Single server, jadi hanya diakses dari localhost — tidak perlu dibuka ke jaringan |

## **2.5 Isi Folder Source — Development / Staging Environment**

Tabel berikut mendokumentasikan seluruh komponen yang ada di environment development/staging dan akan di-transfer ke server production. Pengelola harus memverifikasi bahwa seluruh item di bawah ini sudah tersedia dan siap sebelum proses deployment dimulai.

Catatan pengisian: PATH FOLDER di bawah adalah contoh. Sesuaikan dengan struktur proyek aktual. Tandai kolom Status jika item sudah diverifikasi tersedia dan siap di-deploy.

| No. | Kategori | Nama Item / File | Deskripsi & Peruntukan | Path Folder (Source Dev) | Status |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | Kode (Source) | app/ (backend) | Source code backend Laravel 12: controller, model, service, route API & web  | \[path/ke/repo\]/app/, \[path/ke/repo\]/routes/ | \[ \] Ready |
| 2 | Kode (Source) | resources/  • public/build/ (frontend) | Source Vue 3  • hasil build Vite. Jalankan npm run build dulu | \[path/ke/repo\]/resources/, \[path/ke/repo\]/public/build/ | \[ \] Ready |
| 3 | Kode (Source) | .env.example | Template environment variables, dasar pembuatan .env production | \[path/ke/repo\]/.env.example | \[ \] Ready |
| 4 | Kode (Source) | ecosystem.config.js | docker-compose.yml  • Dockerfile | \[path/ke/repo\]/docker-compose.yml, \[path/ke/repo\]/Dockerfile | \[ \] Ready |
| 5 | Kode (Source) | composer.json/composer.lock  • package.json/package-lock.json | Dependensi PHP (Composer) & JS (npm untuk build Vite), instalasi deterministik  | \[path/ke/repo\]/composer.json, \[path/ke/repo\]/package.json | \[ \] Ready |
| 6 | Database (DB) | database/migrations/ | Migrasi Laravel \= struktur tabel PostgreSQL (pengganti schema\_migration.sql) | \[path/ke/repo\]/database/migrations/ | \[ \] Ready |
| 7 | Database (DB) | database/seeders/ | Data awal: role, kategori campaign, akun superadmin (pengganti seed\_data.sql) | \[path/ke/repo\]/database/seeders/ | \[ \] Ready / \[ \] N/A |
| 8 | Software/Config | docker/nginx/\*.conf | Konfigurasi Nginx: reverse proxy ke PHP-FPM \+ serve Vue build  | \[path/ke/repo\]/docker/nginx/ | \[ \] Ready |
| 9 | Software / Config | certbot-params.txt (opsional) | \[path/ke/repo\]/config/ | \[path/ke/repo\]/docker/  | \[ \] Ready / \[ \] N/A |
| 10 | File Lain | storage/app/public/  | File upload pengguna (bukti donasi, dokumen verifikasi komunitas)  | \[path/ke/repo\]/storage/app/public/  | \[ \] Ready / \[ \] N/A |
| 11 | File Lain | N/A | Tidak dipakai — Berbagive memakai Nginx, bukan Apache  | \- | \[ \] Ready / \[ \] N/A |
| 12 | File Lain | crontab-backup.txt | Daftar cron job backup otomatis (pg\_dump  • arsip volume) | \[path/ke/repo\]/docker/ | \[ \] Ready |

## **2.6 Isi Folder Target — Production Server**

Tabel berikut menunjukkan lokasi akhir (path target) dari setiap komponen setelah proses deployment di server production. Pengelola menggunakan tabel ini sebagai panduan penempatan file dan verifikasi hasil deployment.

Catatan: Kolom 'Server Name' diisi dengan nama/hostname server production yang relevan sesuai dengan yang telah didefinisikan di bagian 2.1 dan 2.2. Contoh: 'app-server' atau 'db-server'.

| . | Kategori | Nama Item | Peruntukan di Production | Path Folder (Target Production) | Server Name |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | Kode (Source) | app/ (backend) | Direktori utama source code backend yang dijalankan oleh PM2. | /var/www/\[nama-app\]/ | \[app-server\] |
| 2 | Kode (Source) | public/ atau dist/ | File frontend statis yang dilayani langsung oleh Nginx. | /var/www/\[nama-app\]/public/ | \[app-server\] |
| 3 | Kode (Source) | .env | File environment variables production (dibuat dari .env.example, TIDAK di-commit ke Git). | /var/www/\[nama-app\]/.env | \[app-server\] |
| 4 | Kode (Source) | ecosystem.config.js | Konfigurasi PM2 yang digunakan untuk perintah pm2 start. | /var/www/\[nama-app\]/ecosystem.config.js | \[app-server\] |
| 5 | Kode (Source) | node\_modules/ | Dependensi Node.js hasil npm install di server (bukan di-copy dari source). | /var/www/\[nama-app\]/node\_modules/ | \[app-server\] |
| 6 | Database (DB) | nama\_db\_prod (MySQL) | Database production. Schema dibuat dari schema\_migration.sql, data dari seed jika perlu. | DB: \[nama\_db\_prod\] di MySQL | \[db-server\] |
| 7 | Database (DB) | Backup DB rutin | File backup mysqldump harian yang disimpan di server dan/atau remote storage. | /backup/db/\[YYYY-MM-DD\]/ | \[db-server\] |
| 8 | Software / Config | app.conf (Nginx) | File konfigurasi virtual host Nginx untuk aplikasi ini. | /etc/nginx/sites-available/\[nama-app\] | \[app-server\] |
| 9 | Software / Config | Symlink Nginx | Symlink aktif yang menghubungkan sites-available ke sites-enabled. | /etc/nginx/sites-enabled/\[nama-app\] | \[app-server\] |
| 10 | Software / Config | Sertifikat SSL | Sertifikat SSL dan private key yang dikelola oleh Certbot. | /etc/letsencrypt/live/\[domain.com\]/ | \[app-server\] |
| 11 | File Lain | uploads/ atau storage/ | Direktori penyimpanan file yang di-upload pengguna. Permission: www-data. | /var/www/\[nama-app\]/storage/uploads/ | \[app-server\] |
| 12 | File Lain | Cron job backup | Entry cron yang diregistrasikan menggunakan crontab \-e untuk backup otomatis. | crontab (root atau deploy user) | \[db-server / app-server\] |
| 13 | File Lain | Backup App Files | Backup file aplikasi sebelum setiap deployment. | /backup/app/\[versi\]/ | \[app-server\] |

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
| 1 | Akses URL Production | URL: https://\[domain.com\] | Buka https://\[domain.com\] di Chrome/Firefox dari jaringan eksternal. | HTTP status 200\. Halaman utama tampil normal \< 3 detik. Gembok SSL hijau aktif di address bar. Tidak ada pesan error SSL/TLS. |
| 2 | Redirect HTTP → HTTPS | URL: http://\[domain.com\] (tanpa S) | Buka http://\[domain.com\] di browser. Amati apakah otomatis diarahkan ke HTTPS. | Browser redirect ke https://\[domain.com\] dengan status 301 Moved Permanently. URL di address bar berubah menjadi https://. |
| 3 | Halaman Login / Autentikasi | Endpoint: /login Akun uji: \[test\_user\] / \[test\_password\] | Akses /login. Masukkan kredensial valid. Klik login. | Login berhasil, diarahkan ke dashboard. Session/token JWT tersimpan. Tidak ada pesan 'Internal Server Error'. |
| 4 | Health Check API | GET https://\[domain.com\]/api/health | Jalankan: curl \-X GET https://\[domain.com\]/api/health | HTTP 200\. Response: {"status":"ok","database":"connected"}. Response time \< 500ms. |
| 5 | Koneksi Database (List Campaign) | GET /api/campaigns atau halaman daftar campaign  | Buka halaman yang memuat daftar campaign dari DB.  | Data campaign tampil benar dari PostgreSQL. Tidak ada error "DB connection failed" / 500\.  |
| 6 | Buat & Tampil Campaign  | Login sebagai komunitas terverifikasi  | Buat campaign baru → cek tampil di daftar publik.  | Campaign tersimpan & muncul di halaman publik dengan data lengkap (judul, target, deskripsi).  |
| 7 | Alur Donasi | Akun donatur, nominal uji (con : Rp10.000) | Login donatur → pilih campaign → input donasi → submit.  | Donasi tercatat, saldo/total terkumpul campaign bertambah, riwayat donasi muncul.  |
| 8 | Verifikasi Komunitas (Superadmin)  | Login akun superadmin  | Buka dashboard superadmin → verifikasi pengajuan komunitas.  | Status komunitas berubah jadi "Terverifikasi". Notifikasi terkirim ke komunitas.  |
| 9 | Pengajuan Pencairan Dana  | Login komunitas, campaign dengan dana terkumpul  | Ajukan pencairan dana \+ unggah laporan penggunaan.  | Pengajuan tercatat, masuk antrian approval superadmin, status berubah.  |
| 10 | Upload / Download File  | File uji: bukti\_test.pdf (\< 5 MB) | Unggah dokumen (legalitas/laporan) → download kembali.  | Upload sukses, file tersimpan di storage/, hasil download identik (tidak corrupt). |
| 11 | Notifikasis & Email | Fitur lupa password/notifikasi. Email: \[test@domain.com\] | Trigger email → cek inbox | Email diterima \< 5 menit, tidak masuk spam, link dapat diklik. (Cek juga queue worker jalan.)  |
| 12 | Dashboard & Monitoring Dana | Login komunitas komunitas & superadmin | Buka daasboard, cek grafik/riwayat dana masuk & keluar | Data dashboard akurat sesuai transaksi. Riwayat dana masuk/keluar tampil benar.  |
| 13 | Responsivitas Mobile | View 375px (Chrome DevTools) | Buka home, login dashboard di mode mobile | Layout menyesuaikan, tanpa horizontal scroll, semua tombol bisa di-tap.  |
| 14 | Seacurity Header HTTP | curl \-I https://\[domain.com\]  | Periksa response header | Ada X-Content-Type-Options: nosniff, X-Frame-Options, Strict-Transport-Security. Versi Nginx/PHP tidak terekspos.  |
| 15 | Performa Loading Halaman Utama | Chrome DevTools Network / PageSpeed  | Reload home, amati waktu loading | Total \< 3 detik, tidak ada resource gagal (404/500), ukuran halaman \< 3 MB.  |

# **BAB 5 – BACKUP-RECOVERY PLAN**

Prosedur yang dijalankan apabila server production mengalami kegagalan sistem (crash), kerusakan data, atau kondisi darurat. Dirancang untuk memulihkan layanan sesuai target RTO dan RPO berikut:

Recovery Time Objective (RTO): Maksimal 4 jam sejak kegagalan terdeteksi. Recovery Point Objective (RPO): Data dapat dikembalikan ke kondisi backup terakhir (maksimal 24 jam sebelum kegagalan).

### **5.1 Jadwal & Strategi Backup Rutin**

| No. | Tipe Backup | Metode | Frekuensi & Jadwal | Lokasi Penyimpanan | Retensi |
| :---: | ----- | ----- | ----- | ----- | ----- |
| 1 | Database (PostgreSQL) | \`docker exec postgres pg\_dump \-U \[app\_user\] \[nama\_db\_prod\] \\  | gzip \> .sql.gz\`  | Harian otomatis, 02:00 WIB (cron)  | Lokal /backup/db/\[YYYY-MM-DD\]/  • Remote \[remote\] 7 Hari terakhir |
| 2 | VOlumen Upload Pengguna | docker run \--rm \-v berbagive\_storage:/data \-v /backup:/backup alpine tar czf /backup/uploads\_\[tgl\].tar.gz \-C /data . (bukti, dokumen legalitas, gambar campaign) | Harian 02.30 | Lokal: /backup/app/uploads/ \+ Remote storage | 7 hari terakhir |
| 3 | Source Code \+ Compose \+ .env | tar.gz \[deploy-dir\] (exclude vendor/, node\_modules/) \+ .env (encrypted gpg) | Mingguan & setiap ada perubahan konfigurasi | Setiap sebelum deployment & tiap perubahan config  | 3 Versi |
| 4 | Docker Image | \`docker save \[image\]:\[tag\] \\ | gzip \> image\_\[versi\].tar.gz\`  | Setiap rilis versi baru | Lokal \+ Remote / registry 2 versi terakhir |

### **5.2 Prosedur Recovery (Jika Server Crash)** 

Prosedur yang dijalankan apabila diperlukan pemeliharaan terencana (planned maintenance). Misalnya upgrade server, update major OS, perbaikan bug kritis, atau penggantian infrastruktur. Tujuannya meminimalkan dampak ke pengguna dan memastikan sistem kembali stabil dalam waktu terkontrol.

| No. | Langkah Recovery | Tujuan | Perintah / Aksi Utama | Indikator Keberhasilan |
| :---: | ----- | ----- | ----- | ----- |
| 1 | Identifikasi & Analisis Kegagalan | Menentukan penyebab crash agar tindakan pemulihan tepat sasaran. |  • Cek docker compose ps & log   • Cek resource host (df \-h, htop)   • Catat waktu kegagalan   • Aktifkan halaman maintenance/darurat   • Hubungi tim | Penyebab teridentifikasi (host down / container crash / data corrupt / disk full / security). Tim siap.  |
| 2 | Restore host/image | Menyiapkan kembali environtment dengan benar |  • Pastikan Docker host sehat   • docker load \< image\_\[versi\].tar.gz (jika image hilang) atau git checkout \[tag\] lalu rebuild   • docker compose \-f docker-compose.prod.yml up \-d  | Contrainer kembali berjalan (docker compose ps : Up) |
| 3 | Restore Database dari Backup | Memulihkan data database dari backup .sql.gz harian apabila data corrupt atau terhapus. | \# Pilih backup terdekat sebelum insiden: ls \-lh /backup/db/ \# Restore: gunzip \-c /backup/db/\[YYYY-MM-DD\]/backup\_\[tanggal\].sql.gz | mysql \-h \[db-server-IP\] \-u \[app\_user\] \-p \[nama\_db\_prod\] \# Verifikasi: mysql \-h \[db-server-IP\] \-u \[app\_user\] \-p \[nama\_db\_prod\] \-e 'SELECT COUNT(\*) FROM \[tabel\_utama\];' | docker exec \-i postgres psql \-U \[app\_user\] \-d \[nama\_db\_prod\] Verifikasi: docker exec \-it postgres psql \-U \[app\_user\] \-d \[nama\_db\_prod\] \-c "SELECT COUNT(\*) FROM \[tabel\_utama\];"\` 4 |
| 4 | Restore file update pengguna  | Memulihkan volume storage (data kritikal) | docker run \--rm \-v berbagive\_storage:/data \-v /backup:/backup alpine tar xzf /backup/uploads\_\[tgl\].tar.gz \-C /data | Volume upload terisi kembali. Dokumen legalitas & bukti pencairan tersedia. |
| 5 | Re-Install Laravel | Menyiapkan cache symlink, & skema | docker exec \-it app php artisan config:cache docker exec \-it app php artisan route:cache docker exec \-it app php artisan storage:link docker exec \-it app php artisan migrate \--force (jika perlu sinkron skema) | Tidak ada eroro artisan. Symlink storage aktif |
| 6 | Restart Semua Sevice | Menghidupkan ulang seluruh cotrainer | docker compose \-f docker-compose.prod.yml down docker compose \-f docker-compose.prod.yml up \-d Verifikasi: docker compose ps | Item smoke test prioritas lulus. Data kritikal valid. Tidak ada error baru.  |
| 7 | Smoke Test Post-Recovery | Verifikasi fungsi dasr sebelum dibuka | Jalankan item prioritas BAB 4 (akses URL, login, health check, list campaign, donasi) → verifikasi data donasi/transaksi tidak hilang → pantau log 15 menit | Semua stakeholder menerima notifikasi. Incident Report selesai dalam 24 jam setelah recovery. |
| 8 | Notifikasi & Dokumentasi Insiden | Memberi tahu stakeholder & dokuentasi | Matikan maintenance: docker exec \-it app php artisan up   • Notifikasi pemulihan ke PM, Dosen, pengguna   • Isi Incident Report (waktu down, penyebab, tindakan, RTO aktual)   • Simpan di \[lokasi dokumen tim\]  | Semua stakeholder dapat notifikasi. Incident Report selesai \< 24 jam.  |

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
| 7 | Reverse proxy Nginx \+ SSL/HTTPS aktif (jika pakai domain publik) | \[ \] Done |  |
| 8 | Smoke test (BAB 4\) lulus semua skenario  | \[ \] Done |  |
| 9 | Backup pertama (PostgreSQL \+ volume upload) berhasil dibuat & terverifikasi  | \[ \] Done |  |
| 10 | Prosedur recovery (BAB 5\) sudah didokumentasikan & dipahami tim  | \[ \] Done |  |
| 11 | Monitoring / log container dapat diakses (docker compose logs)  | \[ \] Done |  |
| 12 | Akun superadmin awal sudah dibuat & dapat login  | \[ \] Done |  |

