import os
import time
import logging
from concurrent import futures
from datetime import datetime, date
import grpc
import psycopg2
from psycopg2.extras import RealDictCursor
from dotenv import load_dotenv

import monitoring_pb2 as pb2
import monitoring_pb2_grpc as pb2_grpc

load_dotenv()

logging.basicConfig(level=logging.INFO, format="%(asctime)s [%(levelname)s] %(message)s")

# 1. Helper Koneksi Database
def get_db_connection():
    return psycopg2.connect(
        host=os.getenv("DB_HOST", "localhost"),
        port=int(os.getenv("DB_PORT", "5434")),  # 5434 dari host lokal, 5432 di dalam container
        dbname=os.getenv("DB_DATABASE", "berbagive"),
        user=os.getenv("DB_USERNAME", "berbagive"),
        password=os.getenv("DB_PASSWORD", "berbagive123")
    )

# 2. Query Data Monitoring dari PostgreSQL
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
            jumlah_donatur = cur.fetchone()["jumlah_donatur"] or 0

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
                selesai = campaign["tanggal_selesai"]
                if isinstance(selesai, datetime):
                    selesai = selesai.date()
                hari_tersisa = max(0, (selesai - date.today()).days)

            donatur_items = []
            for r in donatur_rows:
                nama = "Anonim" if r["is_anonim"] else (r["nama_tampil"] or r["nama_lengkap"] or "Donatur")
                created_at_str = r["created_at"].strftime("%Y-%m-%d %H:%M:%S") if r["created_at"] else ""
                donatur_items.append(pb2.DonaturItem(
                    nama=nama,
                    created_at=created_at_str,
                    nominal=int(r["nominal"] or 0)
                ))

            return pb2.CampaignMonitoringUpdate(
                id_campaign=campaign["id_campaign"],
                judul=campaign["judul"],
                status=campaign["status"],
                nama_lembaga=campaign["nama_lembaga"],
                nama_kategori=campaign["nama_kategori"] or "",
                target_dana=int(target),
                dana_terkumpul=int(terkumpul),
                progress_persen=float(progress),
                jumlah_donatur=int(jumlah_donatur),
                hari_tersisa=int(hari_tersisa),
                tanggal_mulai=str(campaign["tanggal_mulai"] or ""),
                tanggal_selesai=str(campaign["tanggal_selesai"] or ""),
                donatur_terbaru=donatur_items
            )
    finally:
        conn.close()

# 3. Implementasi Servicer gRPC
class CampaignMonitoringServicer(pb2_grpc.CampaignMonitoringServiceServicer):
    def GetCampaignMonitoring(self, request, context):
        logging.info(f"Unary request received for id_campaign: {request.id_campaign}")
        update = query_monitoring_data(request.id_campaign)
        if not update:
            context.abort(grpc.StatusCode.NOT_FOUND, f"Campaign #{request.id_campaign} tidak ditemukan")
        return update

    def StreamCampaignMonitoring(self, request, context):
        logging.info(f"Stream request received for id_campaign: {request.id_campaign}")
        campaign_id = request.id_campaign
        while context.is_active():
            try:
                update = query_monitoring_data(campaign_id)
                if not update:
                    context.abort(grpc.StatusCode.NOT_FOUND, f"Campaign #{campaign_id} tidak ditemukan")
                    return
                yield update
                time.sleep(3)  # Push setiap 3 detik
            except GeneratorExit:
                logging.info(f"Client disconnected from stream #{campaign_id}")
                break
            except Exception as e:
                logging.error(f"Error streaming campaign #{campaign_id}: {e}")
                break

# 4. Entrypoint Server
def serve():
    server = grpc.server(futures.ThreadPoolExecutor(max_workers=10))
    pb2_grpc.add_CampaignMonitoringServiceServicer_to_server(
        CampaignMonitoringServicer(), server
    )
    server.add_insecure_port("[::]:50051")
    server.start()
    logging.info("🚀 gRPC Server berjalan di port :50051...")
    server.wait_for_termination()

if __name__ == "__main__":
    serve()
