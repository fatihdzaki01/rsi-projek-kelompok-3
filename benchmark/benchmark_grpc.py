import json
import statistics
import sys
import time
from pathlib import Path

import grpc


# Supaya Python bisa menemukan generated protobuf files
GRPC_SERVICE_DIR = Path(__file__).resolve().parent.parent / "grpc-service"
sys.path.insert(0, str(GRPC_SERVICE_DIR))

import monitoring_pb2
import monitoring_pb2_grpc


GRPC_HOST = "localhost:8090"
CAMPAIGN_ID = 1
TOTAL_REQUESTS = 100
WARMUP_REQUESTS = 10


def percentile(values, percentile):
    values = sorted(values)

    if not values:
        return 0.0

    rank = (percentile / 100) * (len(values) - 1)
    lower = int(rank)
    upper = min(lower + 1, len(values) - 1)

    if lower == upper:
        return values[lower]

    weight = rank - lower
    return values[lower] + (values[upper] - values[lower]) * weight


def main():
    print("=" * 60)
    print("gRPC BENCHMARK")
    print("=" * 60)
    print(f"Target       : {GRPC_HOST}")
    print(f"Campaign ID  : {CAMPAIGN_ID}")
    print(f"Warm-up      : {WARMUP_REQUESTS}")
    print(f"Requests     : {TOTAL_REQUESTS}")
    print()

    channel = grpc.insecure_channel(GRPC_HOST)
    stub = monitoring_pb2_grpc.CampaignMonitoringServiceStub(channel)
    request = monitoring_pb2.CampaignMonitoringRequest(
        id_campaign=CAMPAIGN_ID
    )

    # Tes koneksi + warm-up
    print("Menjalankan warm-up...")

    for i in range(WARMUP_REQUESTS):
        try:
            stub.GetCampaignMonitoring(request, timeout=10)
        except grpc.RpcError as e:
            raise RuntimeError(
                f"Warm-up gagal pada request {i + 1}: "
                f"{e.code()} - {e.details()}"
            ) from e

    print("Warm-up selesai.")
    print()

    latencies_ms = []
    payload_sizes = []

    print("Menjalankan benchmark...")

    for i in range(TOTAL_REQUESTS):
        start = time.perf_counter()

        try:
            response = stub.GetCampaignMonitoring(
                request,
                timeout=10,
            )
        except grpc.RpcError as e:
            print(
                f"Request {i + 1:3d}/{TOTAL_REQUESTS} | "
                f"GAGAL | {e.code()} | {e.details()}"
            )
            raise

        elapsed_ms = (time.perf_counter() - start) * 1000
        payload_size = response.ByteSize()

        latencies_ms.append(elapsed_ms)
        payload_sizes.append(payload_size)

        print(
            f"Request {i + 1:3d}/{TOTAL_REQUESTS} | "
            f"{elapsed_ms:8.3f} ms | "
            f"{payload_size} bytes"
        )

    average_latency = statistics.mean(latencies_ms)
    median_latency = statistics.median(latencies_ms)
    p95_latency = percentile(latencies_ms, 95)

    average_payload = statistics.mean(payload_sizes)
    min_payload = min(payload_sizes)
    max_payload = max(payload_sizes)

    print()
    print("=" * 60)
    print("HASIL BENCHMARK gRPC")
    print("=" * 60)
    print(f"Total request       : {TOTAL_REQUESTS}")
    print(f"Average latency     : {average_latency:.3f} ms")
    print(f"Median latency      : {median_latency:.3f} ms")
    print(f"P95 latency         : {p95_latency:.3f} ms")
    print(f"Average payload     : {average_payload:.0f} bytes")
    print(f"Min payload         : {min_payload} bytes")
    print(f"Max payload         : {max_payload} bytes")

    result = {
        "protocol": "gRPC",
        "target": GRPC_HOST,
        "campaign_id": CAMPAIGN_ID,
        "total_requests": TOTAL_REQUESTS,
        "warmup_requests": WARMUP_REQUESTS,
        "latencies_ms": latencies_ms,
        "payload_sizes_bytes": payload_sizes,
        "summary": {
            "average_latency_ms": average_latency,
            "median_latency_ms": median_latency,
            "p95_latency_ms": p95_latency,
            "average_payload_bytes": average_payload,
            "min_payload_bytes": min_payload,
            "max_payload_bytes": max_payload,
        },
    }

    output_path = Path(__file__).parent / "grpc_result.json"
    output_path.write_text(
        json.dumps(result, indent=2),
        encoding="utf-8",
    )

    print()
    print(f"Hasil disimpan ke: {output_path}")

    channel.close()


if __name__ == "__main__":
    main()