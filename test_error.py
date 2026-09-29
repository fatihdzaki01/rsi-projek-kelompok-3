import sys
from pathlib import Path

sys.path.insert(0, str(Path("grpc-service").resolve()))

import grpc
import monitoring_pb2
import monitoring_pb2_grpc


channel = grpc.insecure_channel("localhost:8090")

stub = monitoring_pb2_grpc.CampaignMonitoringServiceStub(channel)

req = monitoring_pb2.CampaignMonitoringRequest(
    id_campaign=9999
)

try:
    res = stub.GetCampaignMonitoring(req, timeout=5)

    print("Tidak ada error (tidak diharapkan)")

except grpc.RpcError as e:
    print("=== HASIL UJI ERROR ===")
    print(f"gRPC Status Code : {e.code()}")
    print(f"Pesan Error      : {e.details()}")