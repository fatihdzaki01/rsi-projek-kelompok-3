# Deployment Implementation Flow — Berbagive

**Arsitektur:** Docker multi-container (Nginx + PHP-FPM Laravel + PostgreSQL + Redis + MinIO + Queue + Scheduler) — single server + Cloudflare Tunnel HTTPS.

---

## FASE 1 — Persiapan Server (Ubuntu 22.04)

| # | Langkah | Perintah | Validasi |
|---|---------|----------|----------|
| 1 | Install Docker + Compose | `curl -fsSL https://get.docker.com \| sudo sh` | `docker --version && docker compose version` |
| 2 | Konfigurasi UFW | `ufw allow OpenSSH && ufw allow 80/tcp && ufw enable` | `ufw status verbose` → hanya 22, 80 ALLOW |
| 3 | Install Tailscale | `curl -fsSL https://tailscale.com/install.sh \| sudo sh` | `tailscale version` |

---

## FASE 2 — Persiapan Source Code

| # | Langkah | Perintah | Validasi |
|---|---------|----------|----------|
| 4 | Clone repositori | `git clone <repo> /opt/berbagive && cd /opt/berbagive` | `ls` → ada `docker-compose.yml`, `Dockerfile`, `backend/`, `frontend/` |
| 5 | Buat `.env` | `cp .env.docker .env` | File `.env` ada |
| 6 | Isi `.env` | `APP_KEY=` (kosong). Set `DB_PASSWORD`, `MINIO_ROOT_PASSWORD`, `APP_URL` | `APP_ENV=production`, `APP_DEBUG=false` |
| 7 | Build frontend | `cp frontend/.env.production frontend/.env && cd frontend && npm ci && npm run build && cd ..` | `frontend/dist/` terisi (index.html + assets) |

---

## FASE 3 — Build & Jalankan Container

| # | Langkah | Perintah | Validasi |
|---|---------|----------|----------|
| 8 | Build image | `docker compose build --pull` | `docker images` → image `berbagive-app` muncul |
| 9 | Jalankan container | `docker compose up -d` | `docker compose ps` → semua 7 container `Up` |
| 10 | Generate APP_KEY | `docker compose exec app php artisan key:generate --force` | `.env` APP_KEY terisi |
| 11 | Setup MinIO bucket | `docker compose exec minio mc alias set local http://localhost:9000 "${MINIO_ROOT_USER}" "${MINIO_ROOT_PASSWORD}" && docker compose exec minio mc mb local/berbagive --ignore-existing && docker compose exec minio mc anonymous set public local/berbagive` | Bucket `berbagive` terbuat, public |

---

## FASE 4 — Database & Finalisasi

| # | Langkah | Perintah | Validasi |
|---|---------|----------|----------|
| 12 | DDL migrasi (6 file) | `for f in ddl/rsi-mandat-{ddl,constraint,view,sp,fn,index}.sql; do docker compose exec -T db psql -U berbagive -d berbagive < "$f"; done` | `\dt` di psql → semua tabel ada |
| 13 | Insert data | `docker compose exec -T db psql -U berbagive -d berbagive < insert.sql` (atau `insert_dummy.sql`) | `SELECT count(*) FROM users;` → data terisi |
| 14 | Finalisasi | `./finalize.sh` | `php artisan optimize` sukses. Health check: `curl localhost/api/v1/health` → `{"status":"ok"}` |

---

## FASE 5 — Akses Publik (Cloudflare Tunnel)

| # | Langkah | Perintah | Validasi |
|---|---------|----------|----------|
| 15 | Tunnel Funnel | `sudo tailscale funnel --bg http://localhost:80` | `tailscale serve status` → URL `https://<random>.trycloudflare.com` |
| 16 | Update APP_URL | `.env` → `APP_URL=https://<url>.trycloudflare.com` | `docker compose restart app` |
| 17 | Uji coba | Buka URL dari perangkat eksternal | Halaman Berbagive tampil, API merespon |

---

## Diagram Container

```
                                   ┌─ Perangkat Eksternal ─┐
                                   │   Browser / Mobile    │
                                   └──────────┬────────────┘
                                              │ HTTPS
                                     ┌────────▼────────┐
                                     │  Cloudflare     │
                                     │  Tunnel         │
                                     └────────┬────────┘
                                              │ TCP :80
                                     ┌────────▼────────┐
                                     │  Nginx:1.24     │
                                     │  (berbagive-    │
                                     │   nginx)        │
                                     └──┬─────────┬────┘
                           ┌─────────────┘         └─────────────┐
                    ┌──────▼──────┐                         ┌────▼─────┐
                    │  PHP-FPM    │  :9000                   │  MinIO   │
                    │  Laravel    │ ◄─────────────────────►  │  S3      │
                    │  (app)      │                         │  Storage │
                    └──┬──────┬───┘                         └──────────┘
                       │      │
              ┌────────▼┐ ┌───▼──────┐
              │PostgreSQL│ │  Redis   │
              │ :5432    │ │  :6379   │
              └──────────┘ └──────────┘

              Container pendukung (terpisah):
              ┌─────────────┐  ┌───────────────┐
              │ Queue Worker│  │  Scheduler    │
              │ (queue:work)│  │ (schedule:run)│
              └─────────────┘  └───────────────┘
```

---

## Smoke Test Pasca-Deploy

| # | Cek | Cara Uji | Ekspektasi |
|---|-----|----------|------------|
| 1 | Akses URL | `curl -I https://<url>` | HTTP 200 |
| 2 | Health API | `curl https://<url>/api/v1/health` | `{"status":"ok"}` |
| 3 | Login | Buka `/login`, input `admin@mail.com` / `Password123` | Redirect ke `/dashboard` |
| 4 | List campaign | Buka `/campaigns` | Card campaign tampil |
| 5 | Donasi | Login DONATUR → detail campaign → sidebar → checkout | Redirect ke `/payments/checkout/{id}` |
| 6 | Responsive | Chrome DevTools 375px | Layout baik, tanpa horizontal scroll |

---

## Rollback Plan (jika gagal)

```bash
docker compose down
git stash / git checkout <tag-sebelumnya>
cp .env.backup .env
docker compose build && docker compose up -d
docker compose exec app php artisan key:generate --force
./finalize.sh
```
