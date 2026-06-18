<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; }
    h2 { font-size: 14px; margin-bottom: 4px; }
    p  { margin: 2px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
    th { background: #eee; }
    .section { margin-top: 20px; }
</style>
</head>
<body>
    <h2>Laporan Keuangan Platform Berbagive</h2>
    <p>Periode: {{ $periode['mulai'] }} s/d {{ $periode['selesai'] }}</p>

    <div class="section">
        <h2>Transaksi Donasi</h2>
        <table>
            <thead>
                <tr>
                    <th>No. Transaksi</th><th>Tanggal</th><th>Username</th>
                    <th>Campaign</th><th>Lembaga</th><th>Nominal</th>
                    <th>Metode</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($donations as $d)
                <tr>
                    <td>{{ $d['nomor_transaksi'] }}</td>
                    <td>{{ $d['tanggal_transaksi'] }}</td>
                    <td>{{ $d['username'] }}</td>
                    <td>{{ $d['judul_campaign'] }}</td>
                    <td>{{ $d['nama_lembaga'] }}</td>
                    <td>{{ number_format($d['nominal'], 0, ',', '.') }}</td>
                    <td>{{ $d['metode_pembayaran'] }}</td>
                    <td>{{ $d['status_pembayaran'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Transaksi Pencairan</h2>
        <table>
            <thead>
                <tr>
                    <th>Campaign</th><th>Lembaga</th><th>Tgl Pengajuan</th>
                    <th>Nominal Diajukan</th><th>Nominal Disetujui</th><th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($withdrawals as $w)
                <tr>
                    <td>{{ $w['judul_campaign'] }}</td>
                    <td>{{ $w['nama_lembaga'] }}</td>
                    <td>{{ $w['tanggal_pengajuan'] }}</td>
                    <td>{{ number_format($w['nominal_diajukan'], 0, ',', '.') }}</td>
                    <td>{{ $w['nominal_disetujui'] ? number_format($w['nominal_disetujui'], 0, ',', '.') : '-' }}</td>
                    <td>{{ $w['status'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
