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

print("=== HASIL SERVER STREAMING RPC ===")
print("(Menunggu 3 paket streaming, interval ~3 detik...)")

stream = stub.StreamCampaignMonitoring(req)

for i, res in enumerate(stream, start=1):
    terbaru = (
        res.donatur_terbaru[0].nama
        if res.donatur_terbaru
        else "-"
    )

    print(
        f"[Paket {i}] "
        f"dana={res.dana_terkumpul:,} | "
        f"donatur={res.jumlah_donatur} | "
        f"terbaru={terbaru}"
    )

    if i >= 3:
        break

print("Streaming dihentikan setelah 3 paket.")