import os
import time
import logging
from concurrent import futures
from datetime import datetime, date
import grpc
import psycopg2
from psycopg2.extras import RealDictCursor

import monitoring_pb2
import monitoring_pb2_grpc

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")

# 1. Koneksi Database PostgreSQL
def get_db_connection():
    return psycopg2.connect(
        host=os.getenv("DB_HOST", "localhost"),
        port=int(os.getenv("DB_PORT", "5434")),  # 5434 jika dari host lokal, 5432 jika di dalam container
        dbname=os.getenv("DB_DATABASE", "berbagive"),
        user=os.getenv("DB_USERNAME", "berbagive"),
        password=os.getenv("DB_PASSWORD", "berbagive123")
    )

# 2. Query Data Monitoring
def query_monitoring_data(campaign_id: int):
    conn = get_db_connection()
    try:
        with conn.cursor(cursor_factory=RealDictCursor) as cur:
            # Query Campaign + Komunitas + Kategori
            cur.execute("""
                SELECT 
                    c.id_campaign, c.judul, c.status, c.target_dana,
                    c.dana_terkumpul, c.tanggal_mulai, c.tanggal_selesai,
                    k.nama_lembaga, COALESCE(kc.nama_kategori, '') as nama_kategori
                FROM campaign c
                JOIN komunitas k ON k.id_komunitas = c.id_komunitas
                LEFT JOIN kategori_campaign kc ON kc.id_kategori = c.id_kategori
                WHERE c.id_campaign = %s;
            """, (campaign_id,))
            campaign = cur.fetchone()
            if not campaign:
                return None

            # Query Jumlah Donatur Unik
            cur.execute("""
                SELECT COUNT(DISTINCT id_user) as jumlah_donatur
                FROM donasi
                WHERE id_campaign = %s AND status_pembayaran = 'berhasil';
            """, (campaign_id,))
            jumlah_donatur = cur.fetchone()["jumlah_donatur"]

            # Query Donatur Terbaru (Maks 20)
            cur.execute("""
                SELECT 
                    d.is_anonim, d.nama_tampil, u.nama_lengkap, d.created_at, d.nominal
                FROM donasi d
                JOIN users u ON u.id_user = d.id_user
                WHERE d.id_campaign = %s AND d.status_pembayaran = 'berhasil'
                ORDER BY d.created_at DESC
                LIMIT 20;
            """, (campaign_id,))
            donatur_rows = cur.fetchall()

            # Hitung progress & sisa hari
            target = campaign["target_dana"] or 0
            terkumpul = campaign["dana_terkumpul"] or 0
            progress = round((terkumpul / target * 100), 2) if target > 0 else 0.0

            hari_tersisa = 0
            if campaign["tanggal_selesai"]:
                delta = (campaign["tanggal_selesai"] - date.today()).days
                hari_tersisa = max(0, delta)

            donatur_list = []
            for r in donatur_rows:
                nama = "Anonim" if r["is_anonim"] else (r["nama_tampil"] or r["nama_lengkap"] or "Donatur")
                donatur_list.append({
                    "nama": nama,
                    "created_at": r["created_at"].strftime("%Y-%m-%d %H:%M:%S") if r["created_at"] else "",
                    "nominal": int(r["nominal"] or 0)
                })

            return {
                "id_campaign": campaign["id_campaign"],
                "judul": campaign["judul"],
                "status": campaign["status"],
                "nama_lembaga": campaign["nama_lembaga"],
                "nama_kategori": campaign["nama_kategori"],
                "target_dana": int(target),
                "dana_terkumpul": int(terkumpul),
                "progress_persen": float(progress),
                "jumlah_donatur": int(jumlah_donatur),
                "hari_tersisa": int(hari_tersisa),
                "tanggal_mulai": str(campaign["tanggal_mulai"] or ""),
                "tanggal_selesai": str(campaign["tanggal_selesai"] or ""),
                "donatur_terbaru": donatur_list
            }
    finally:
        conn.close()

# 3. Helper Mapping Dict ke Proto Message
def build_grpc_message(data: dict) -> monitoring_pb2.CampaignMonitoringUpdate:
    donatur_items = [
        monitoring_pb2.DonaturItem(
            nama=d["nama"],
            created_at=d["created_at"],
            nominal=d["nominal"]
        ) for d in data["donatur_terbaru"]
    ]
    return monitoring_pb2.CampaignMonitoringUpdate(
        id_campaign=data["id_campaign"],
        judul=data["judul"],
        status=data["status"],
        nama_lembaga=data["nama_lembaga"],
        nama_kategori=data["nama_kategori"],
        target_dana=data["target_dana"],
        dana_terkumpul=data["dana_terkumpul"],
        progress_persen=data["progress_persen"],
        jumlah_donatur=data["jumlah_donatur"],
        hari_tersisa=data["hari_tersisa"],
        tanggal_mulai=data["tanggal_mulai"],
        tanggal_selesai=data["tanggal_selesai"],
        donatur_terbaru=donatur_items
    )

# 4. Implementasi Servicer
class CampaignMonitoringServicer(monitoring_pb2_grpc.CampaignMonitoringServiceServicer):
    def GetCampaignMonitoring(self, request, context):
        logging.info(f"Unary request received for campaign_id: {request.id_campaign}")
        data = query_monitoring_data(request.id_campaign)
        if not data:
            context.abort(grpc.StatusCode.NOT_FOUND, f"Campaign #{request.id_campaign} tidak ditemukan")
        return build_grpc_message(data)

    def StreamCampaignMonitoring(self, request, context):
        logging.info(f"Streaming started for campaign_id: {request.id_campaign}")
        campaign_id = request.id_campaign
        while context.is_active():
            try:
                data = query_monitoring_data(campaign_id)
                if not data:
                    context.abort(grpc.StatusCode.NOT_FOUND, f"Campaign #{campaign_id} tidak ditemukan")
                    return
                yield build_grpc_message(data)
                time.sleep(3)  # Push pembaruan setiap 3 detik
            except GeneratorExit:
                logging.info(f"Client disconnected from stream for campaign_id: {campaign_id}")
                break
            except Exception as e:
                logging.error(f"Error in stream: {e}")
                break

# 5. Runner
def serve():
    server = grpc.server(futures.ThreadPoolExecutor(max_workers=10))
    monitoring_pb2_grpc.add_CampaignMonitoringServiceServicer_to_server(
        CampaignMonitoringServicer(), server
    )
    server.add_insecure_port("[::]:50051")
    server.start()
    logging.info("🚀 gRPC Server running on port :50051")
    server.wait_for_termination()

if __name__ == "__main__":
    serve()
