<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan UNMARIS</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #2c3e50; }
        .header p { margin: 5px 0 0; font-size: 12px; }
        .summary-box { display: table; width: 100%; margin-bottom: 20px; }
        .summary-item { display: table-cell; padding: 10px; border: 1px solid #ddd; text-align: center; background: #f9fafb; }
        .summary-item span { display: block; font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .summary-item strong { display: block; font-size: 14px; margin-top: 5px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f3f4f6; text-align: left; font-size: 11px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Laporan Rekapitulasi Keuangan</h1>
        <p>Portal Layanan Digital BTSI - Universitas Stella Maris Sumba</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <span>Total Tagihan</span>
            <strong>Rp {{ number_format($summary['total_tagihan'], 0, ',', '.') }}</strong>
        </div>
        <div class="summary-item">
            <span>Total Terbayar</span>
            <strong>Rp {{ number_format($summary['total_bayar'], 0, ',', '.') }}</strong>
        </div>
        <div class="summary-item">
            <span>Total Piutang</span>
            <strong>Rp {{ number_format($summary['total_piutang'], 0, ',', '.') }}</strong>
        </div>
        <div class="summary-item">
            <span>Collection Rate</span>
            <strong>{{ $summary['collection_rate'] }}%</strong>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Program Studi</th>
                <th>Angkatan</th>
                <th class="text-right">Jml Mhs</th>
                <th class="text-right">Total Tagihan</th>
                <th class="text-right">Total Terbayar</th>
                <th class="text-right">Piutang</th>
                <th class="text-center">Status (Lunas/Cicil/Belum)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row->nama_prodi }}</td>
                <td>{{ $row->angkatan }}</td>
                <td class="text-right">{{ $row->total_mahasiswa }}</td>
                <td class="text-right">{{ number_format($row->total_tagihan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row->total_piutang, 0, ',', '.') }}</td>
                <td class="text-center">{{ $row->count_lunas }} / {{ $row->count_cicil }} / {{ $row->count_belum }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>