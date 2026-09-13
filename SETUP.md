# Setup Guide — Berbagive (gRPC Branch)

Panduan ini untuk menjalankan environment dari awal di mesin baru.

---

## Prerequisites

- [Docker](https://docs.docker.com/get-docker/) + Docker Compose
- Git

---

## Langkah-langkah

### 1. Clone repo & masuk ke folder

```bash
git clone <url-repo>
cd rsi-projek-kelompok-3
```

### 2. Buat file `.env`

```bash
cp .env.docker .env
```

Lalu edit `.env`, isi nilai berikut:

```env
DB_PASSWORD=isi_password_bebas        # wajib diisi
MINIO_ROOT_PASSWORD=isi_password_bebas # wajib diisi
HTTP_PORT=8080                         # ganti kalau port 80 sudah dipakai
```

> **Catatan:** Kalau port 5434 di host sudah dipakai PostgreSQL lain, ganti baris
> `- "5434:5432"` di `docker-compose.yml` ke port lain (misal `5435:5432`).

### 3. Jalankan deploy script

```bash
./deploy.sh
```

Script ini akan:
- Build image Docker
- Start semua container (`app`, `nginx`, `db`, `redis`, `minio`, dll)
- Generate `APP_KEY` otomatis

### 4. Import DDL ke database (urutan wajib)

```bash
docker compose exec -T db psql -U berbagive -d berbagive < ddl/rsi-mandat-ddl.sql
docker compose exec -T db psql -U berbagive -d berbagive < ddl/rsi-mandat-constraint.sql
docker compose exec -T db psql -U berbagive -d berbagive < ddl/rsi-mandat-view.sql
docker compose exec -T db psql -U berbagive -d berbagive < ddl/rsi-mandat-sp.sql
docker compose exec -T db psql -U berbagive -d berbagive < ddl/rsi-mandat-fn.sql
docker compose exec -T db psql -U berbagive -d berbagive < ddl/rsi-mandat-index.sql
```

### 5. Jalankan finalize script

```bash
./finalize.sh
```

Script ini membuat tabel Laravel tambahan, fix sequences, dan optimasi cache.

### 6. Verifikasi aplikasi jalan

```bash
curl http://localhost:${HTTP_PORT:-8080}/api/v1/health
```

Expected response: `{"status":"ok", ...}`

### 7. Jalankan gRPC service + Envoy

```bash
docker compose up grpc-service envoy -d
```

Verifikasi:
- `berbagive-envoy` → jalan di port **8090**
- `berbagive-grpc` → akan error `server.py not found` sampai Orang B push kodenya — **normal**

---

## Menjalankan ulang dari awal (reset environment)

Kalau mau reset penuh (hapus semua data):

```bash
docker compose down -v   # hapus container + volume
./deploy.sh              # setup ulang dari awal
# ulangi langkah 4-7
```

---

## Port yang digunakan

| Service | Host Port | Keterangan |
|---|---|---|
| Nginx (frontend + API) | 8080 | Sesuai `HTTP_PORT` di `.env` |
| PostgreSQL | 5434 | Untuk koneksi langsung dari host |
| MinIO | 9000 / 9001 | Object storage |
| Envoy (gRPC-Web proxy) | 8090 | Frontend Vue connect ke sini |
| gRPC server | 50051 | Internal Docker saja |

---

## Troubleshooting

**Port sudah dipakai (`address already in use`)**
→ Ganti port di `docker-compose.yml` atau stop service yang konflik.

**`DB_PASSWORD` error saat `./deploy.sh`**
→ Pastikan `.env` sudah diisi, bukan masih kosong.

**`grpc-service` terus restart**
→ Normal sampai Orang B push `server.py`. Bisa diabaikan.
