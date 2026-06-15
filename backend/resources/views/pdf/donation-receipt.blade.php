<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Donasi - {{ $nomor_transaksi }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 24px; }
        .header h1 { color: #047857; margin: 0; font-size: 20px; }
        .header p { color: #6b7280; margin: 4px 0 0; font-size: 11px; }
        .receipt-box { border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; }
        .receipt-box table { width: 100%; border-collapse: collapse; }
        .receipt-box td { padding: 6px 8px; vertical-align: top; }
        .receipt-box td:first-child { width: 120px; color: #6b7280; }
        .receipt-box td:last-child { font-weight: 600; }
        .divider { border-top: 1px dashed #d1d5db; margin: 12px 0; }
        .footer { text-align: center; margin-top: 24px; color: #9ca3af; font-size: 10px; }
        .amount { font-size: 18px; color: #047857; }
        .status-badge { display: inline-block; background: #d1fae5; color: #065f46; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BERBAGIVE</h1>
        <p>Bukti Donasi Digital</p>
    </div>

    <div class="receipt-box">
        <table>
            <tr><td>No. Transaksi</td><td>{{ $nomor_transaksi }}</td></tr>
            <tr><td>Tanggal</td><td>{{ $tanggal_transaksi }}</td></tr>
            <tr><td>Campaign</td><td>{{ $judul_campaign }}</td></tr>
            <tr><td>Donatur</td><td>{{ $nama_tampil }}</td></tr>
            <tr><td>Metode</td><td>{{ strtoupper($metode_pembayaran) }}</td></tr>
            <tr><td>Status</td><td><span class="status-badge">BERHASIL</span></td></tr>
            <tr><td colspan="2"><div class="divider"></div></td></tr>
            <tr><td>Nominal Donasi</td><td class="amount">Rp {{ number_format($nominal, 0, ',', '.') }}</td></tr>
        </table>
    </div>

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh sistem Berbagive.
        <br>Simpan bukti ini sebagai referensi donasi Anda.
    </div>
</body>
</html>
