import sys
import grpc
import monitoring_pb2 as pb2
import monitoring_pb2_grpc as pb2_grpc

def test_unary(stub, campaign_id=1):
    print(f"\n--- [1] Testing Unary RPC: GetCampaignMonitoring (id_campaign={campaign_id}) ---")
    try:
        response = stub.GetCampaignMonitoring(pb2.CampaignMonitoringRequest(id_campaign=campaign_id))
        print("✅ Unary RPC Success! Response:")
        print(f"  ID Campaign    : {response.id_campaign}")
        print(f"  Judul          : {response.judul}")
        print(f"  Status         : {response.status}")
        print(f"  Lembaga        : {response.nama_lembaga}")
        print(f"  Kategori       : {response.nama_kategori}")
        print(f"  Target Dana    : Rp {response.target_dana:,}")
        print(f"  Dana Terkumpul : Rp {response.dana_terkumpul:,}")
        print(f"  Progress       : {response.progress_persen}%")
        print(f"  Jumlah Donatur : {response.jumlah_donatur}")
        print(f"  Hari Tersisa   : {response.hari_tersisa} hari")
        print(f"  Donatur Terbaru ({len(response.donatur_terbaru)} orang):")
        for idx, d in enumerate(response.donatur_terbaru, 1):
            print(f"    {idx}. {d.nama} - Rp {d.nominal:,} ({d.created_at})")
    except grpc.RpcError as e:
        print(f"❌ RPC Failed: [{e.code()}] {e.details()}")

def test_stream(stub, campaign_id=1, max_messages=3):
    print(f"\n--- [2] Testing Server-Streaming: StreamCampaignMonitoring (id_campaign={campaign_id}) ---")
    print(f"Mendengarkan stream (maks {max_messages} pesan)... Tekan Ctrl+C untuk berhenti.")
    try:
        count = 0
        stream = stub.StreamCampaignMonitoring(pb2.CampaignMonitoringRequest(id_campaign=campaign_id))
        for update in stream:
            count += 1
            print(f"📡 [Stream #{count}] Dana: Rp {update.dana_terkumpul:,} | Donatur: {update.jumlah_donatur} | Donatur Terbaru: {[d.nama for d in update.donatur_terbaru[:3]]}")
            if count >= max_messages:
                print(f"✅ Selesai menerima {max_messages} update streaming.")
                break
    except grpc.RpcError as e:
        print(f"❌ Stream RPC Failed: [{e.code()}] {e.details()}")
    except KeyboardInterrupt:
        print("\n⏹ Stream dihentikan oleh user.")

def main():
    target = "localhost:50051"
    campaign_id = int(sys.argv[1]) if len(sys.argv) > 1 else 1
    
    print(f"Menghubungkan ke gRPC Server di {target}...")
    with grpc.insecure_channel(target) as channel:
        stub = pb2_grpc.CampaignMonitoringServiceStub(channel)
        
        # Test Unary
        test_unary(stub, campaign_id)
        
        # Test Streaming
        test_stream(stub, campaign_id, max_messages=3)

if __name__ == "__main__":
    main()
