# grpc-service

Python gRPC server untuk monitoring campaign real-time (Berbagive).

## Struktur

```
grpc-service/
├── proto/
│   └── monitoring.proto    # kontrak gRPC (dibuat Orang A)
├── server.py               # gRPC server (dibuat Orang B)
├── requirements.txt
├── Dockerfile
└── README.md
```

## Cara Jalankan

### Build & jalankan service gRPC + Envoy saja
```bash
docker compose up grpc-service envoy
```

### Jalankan semua service
```bash
docker compose up
```

### Verifikasi build berhasil (tanpa menjalankan)
```bash
docker compose build grpc-service
```

## Test gRPC Server (butuh grpcurl)

Install grpcurl:
```bash
go install github.com/fullstorydev/grpcurl/cmd/grpcurl@latest
```

Test unary RPC:
```bash
grpcurl -plaintext -d '{"id_campaign": 1}' localhost:50051 monitoring.CampaignMonitoringService/GetCampaignMonitoring
```

Test server-streaming RPC:
```bash
grpcurl -plaintext -d '{"id_campaign": 1}' localhost:50051 monitoring.CampaignMonitoringService/StreamCampaignMonitoring
```

## Catatan Teknis

- gRPC server listen di port **50051** (internal Docker, tidak di-expose ke host)
- Envoy listen di port **8090** (di-expose ke host) — port 8080 sudah dipakai Nginx
- Envoy CORS allow: `localhost:5173` (Vue dev server) dan `localhost:8080`
- Kode Python (`monitoring_pb2.py`, `monitoring_pb2_grpc.py`) di-generate otomatis saat `docker build` — tidak perlu generate manual
