import json
import statistics
import time
from pathlib import Path

import requests


URL = "http://localhost:8080/api/v1/campaigns/1/monitoring"
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
    print("REST BENCHMARK")
    print("=" * 60)
    print(f"URL      : {URL}")
    print(f"Warm-up  : {WARMUP_REQUESTS}")
    print(f"Requests : {TOTAL_REQUESTS}")
    print()

    session = requests.Session()

    # Warm-up
    print("Menjalankan warm-up...")
    for i in range(WARMUP_REQUESTS):
        response = session.get(URL, timeout=10)

        if response.status_code != 200:
            raise RuntimeError(
                f"Warm-up gagal pada request {i + 1}: "
                f"HTTP {response.status_code}\n{response.text}"
            )

    print("Warm-up selesai.")
    print()

    latencies_ms = []
    payload_sizes = []
    statuses = []

    print("Menjalankan benchmark...")

    for i in range(TOTAL_REQUESTS):
        start = time.perf_counter()

        response = session.get(URL, timeout=10)

        elapsed_ms = (time.perf_counter() - start) * 1000

        latencies_ms.append(elapsed_ms)
        payload_sizes.append(len(response.content))
        statuses.append(response.status_code)

        print(
            f"Request {i + 1:3d}/{TOTAL_REQUESTS} | "
            f"HTTP {response.status_code} | "
            f"{elapsed_ms:8.3f} ms | "
            f"{len(response.content)} bytes"
        )

    average_latency = statistics.mean(latencies_ms)
    median_latency = statistics.median(latencies_ms)
    p95_latency = percentile(latencies_ms, 95)

    average_payload = statistics.mean(payload_sizes)
    min_payload = min(payload_sizes)
    max_payload = max(payload_sizes)

    print()
    print("=" * 60)
    print("HASIL BENCHMARK REST")
    print("=" * 60)
    print(f"Total request       : {TOTAL_REQUESTS}")
    print(f"HTTP status         : {sorted(set(statuses))}")
    print(f"Average latency     : {average_latency:.3f} ms")
    print(f"Median latency      : {median_latency:.3f} ms")
    print(f"P95 latency         : {p95_latency:.3f} ms")
    print(f"Average payload     : {average_payload:.0f} bytes")
    print(f"Min payload         : {min_payload} bytes")
    print(f"Max payload         : {max_payload} bytes")

    result = {
        "protocol": "REST",
        "url": URL,
        "total_requests": TOTAL_REQUESTS,
        "warmup_requests": WARMUP_REQUESTS,
        "status_codes": statuses,
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

    output_path = Path("benchmark/rest_result.json")
    output_path.write_text(
        json.dumps(result, indent=2),
        encoding="utf-8",
    )

    print()
    print(f"Hasil disimpan ke: {output_path}")


if __name__ == "__main__":
    main()