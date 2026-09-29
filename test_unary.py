import sys
from pathlib import Path

sys.path.insert(0, str(Path("grpc-service").resolve()))

import grpc
import monitoring_pb2
import monitoring_pb2_grpc


channel = grpc.insecure_channel("localhost:8090")

stub = monitoring_pb2_grpc.CampaignMonitoringServiceStub(channel)

req = monitoring_pb2.CampaignMonitoringRequest(
    id_campaign=1
)

res = stub.GetCampaignMonitoring(
    req,
    timeout=5
)

print("=== HASIL UNARY RPC ===")
print(f"id_campaign    : {res.id_campaign}")
print(f"judul          : {res.judul}")
print(f"status         : {res.status}")
print(f"dana_terkumpul : Rp {res.dana_terkumpul:,}")
print(f"target_dana    : Rp {res.target_dana:,}")
print(f"progress       : {res.progress_persen:.1f}%")
print(f"jumlah_donatur : {res.jumlah_donatur}")
print(f"hari_tersisa   : {res.hari_tersisa} hari")
print(f"nama_lembaga   : {res.nama_lembaga}")
print(f"nama_kategori  : {res.nama_kategori}")
print(f"donatur_terbaru: {len(res.donatur_terbaru)} orang")

for d in res.donatur_terbaru[:5]:
    print(f"  - {d.nama} | Rp {d.nominal:,} | {d.created_at}")