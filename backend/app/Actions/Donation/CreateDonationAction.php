<?php

namespace App\Actions\Donation;

use App\Models\Notifikasi;
use App\Services\DonationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDonationAction
{
    public function __construct(private DonationService $service) {}

    public function execute(int $userId, array $payload): array
    {
        $campaign = DB::selectOne(
            'SELECT id_campaign, judul, status FROM campaign WHERE id_campaign = ? AND deleted_at IS NULL',
            [$payload['id_campaign']]
        );

        if (!$campaign) {
            throw new \RuntimeException('Campaign tidak ditemukan');
        }

        if ($campaign->status !== 'aktif') {
            throw new \RuntimeException('Campaign tidak menerima donasi');
        }

        if ($payload['nominal'] < 5000) {
            throw new \RuntimeException('Nominal minimal Rp 5.000');
        }

        $donasi = $this->service->createDonation($userId, $payload);

        $paymentToken = (string) Str::uuid();

        Notifikasi::kirim([
            'id_penerima_user' => $userId,
            'judul' => 'Donasi menunggu pembayaran',
            'pesan' => 'Donasi sebesar Rp ' . number_format($payload['nominal'], 0, ',', '.') . ' untuk "' . $campaign->judul . '" sedang menunggu pembayaran.',
            'tipe' => 'donasi_pending',
            'related_donasi_id' => $donasi->id_donasi,
        ]);

        return [
            'id_donasi'              => $donasi->id_donasi,
            'nomor_transaksi'        => $donasi->nomor_transaksi,
            'status_pembayaran'      => $donasi->status_pembayaran,
            'batas_waktu_pembayaran' => now()->addHours(24)->toIso8601String(),
            'metode_pembayaran'      => $donasi->metode_pembayaran,
            'payment_token'          => $paymentToken,
            'mock_payment_url'       => url('/api/v1/donations/' . $donasi->id_donasi . '/mock-pay?token=' . $paymentToken),
        ];
    }
}
