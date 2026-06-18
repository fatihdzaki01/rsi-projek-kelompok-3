<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinancialReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        return [
            new DonationSheet($this->data['donations']),
            new WithdrawalSheet($this->data['withdrawals']),
        ];
    }
}

class DonationSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $rows) {}

    public function title(): string { return 'Transaksi Donasi'; }

    public function headings(): array
    {
        return ['ID', 'Nomor Transaksi', 'Tanggal', 'Username', 'Nama Lengkap',
                'Campaign', 'Lembaga', 'Nominal', 'Metode', 'Status'];
    }

    public function array(): array
    {
        return array_map(fn($r) => array_values($r), $this->rows);
    }
}

class WithdrawalSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(private array $rows) {}

    public function title(): string { return 'Transaksi Pencairan'; }

    public function headings(): array
    {
        return ['ID', 'Tgl Pengajuan', 'Tgl Keputusan', 'Campaign', 'Lembaga',
                'Urutan', 'Nominal Diajukan', 'Nominal Disetujui', 'Status',
                'Bank Tujuan', 'No. Rekening'];
    }

    public function array(): array
    {
        return array_map(fn($r) => array_values($r), $this->rows);
    }
}
