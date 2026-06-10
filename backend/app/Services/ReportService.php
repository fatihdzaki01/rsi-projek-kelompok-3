<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getReportData(string $startDate, string $endDate): array
    {
        $donations = DB::select(
            'SELECT id_donasi, nomor_transaksi, tanggal_transaksi, username, nama_lengkap,
                    judul_campaign, nama_lembaga, nominal, metode_pembayaran, status_pembayaran
             FROM v_donation_transactions
             WHERE tanggal_transaksi::date BETWEEN ? AND ?
             ORDER BY tanggal_transaksi ASC',
            [$startDate, $endDate]
        );

        $withdrawals = DB::select(
            'SELECT id_pencairan, tanggal_pengajuan, tanggal_keputusan, judul_campaign,
                    nama_lembaga, urutan_ke, nominal_diajukan, nominal_disetujui,
                    status, nama_bank_tujuan, nomor_rekening_tujuan
             FROM v_withdrawal_transactions
             WHERE tanggal_pengajuan::date BETWEEN ? AND ?
             ORDER BY tanggal_pengajuan ASC',
            [$startDate, $endDate]
        );

        return [
            'donations'   => array_map(fn($r) => (array) $r, $donations),
            'withdrawals' => array_map(fn($r) => (array) $r, $withdrawals),
            'periode'     => ['mulai' => $startDate, 'selesai' => $endDate],
        ];
    }
}
