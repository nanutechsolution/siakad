<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Besar - {{ $info->nim }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 16px; color: #2c3e50; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 11px; }
        .info-table { width: 100%; margin-bottom: 20px; border: none; }
        .info-table td { padding: 4px; border: none; font-size: 12px; }
        .info-table td:first-child { width: 120px; font-weight: bold; }
        .ledger-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .ledger-table th, .ledger-table td { border: 1px solid #cbd5e1; padding: 6px; }
        .ledger-table th { background-color: #f1f5f9; text-align: left; font-size: 10px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #dc2626; }
        .text-success { color: #16a34a; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Buku Besar Mahasiswa</h1>
        <p>Portal Layanan Digital BTSI - Universitas Stella Maris Sumba</p>
    </div>

    <table class="info-table">
        <tr>
            <td>NIM</td>
            <td>: {{ $info->nim }}</td>
            <td>Program Studi</td>
            <td>: {{ $info->nama_prodi }}</td>
        </tr>
        <tr>
            <td>Nama Lengkap</td>
            <td>: {{ $info->nama_mahasiswa }}</td>
            <td>Angkatan</td>
            <td>: {{ $info->angkatan }}</td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th class="text-center">Tanggal</th>
                <th>No. Referensi</th>
                <th>Keterangan</th>
                <th class="text-right">Debit (Rp)</th>
                <th class="text-right">Kredit (Rp)</th>
                <th class="text-right">Saldo Berjalan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
            <tr>
                <td class="text-center">{{ date('d/m/Y H:i', strtotime($row->created_at)) }}</td>
                <td>{{ $row->referensi_dokumen }}</td>
                <td>
                    <strong>[{{ $row->tipe_transaksi }}]</strong><br>
                    {{ $row->keterangan }}
                </td>
                <td class="text-right text-danger">{{ number_format($row->debit, 0, ',', '.') }}</td>
                <td class="text-right text-success">{{ number_format($row->kredit, 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($row->saldo_berjalan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada histori transaksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <p style="text-align: right; font-style: italic; font-size: 10px;">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>

</body>
</html>