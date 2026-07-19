<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ruang & Penggunaan Kelas</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Ruang &amp; Penggunaan Kelas</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Nama Ruang</th>
                <th>Kapasitas</th>
                <th>Jumlah Jadwal</th>
                <th>Total Jam Penggunaan</th>
                <th>Prodi</th>
                <th>Mata Kuliah</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['nama_ruang'] }}</td>
                    <td>{{ $row['kapasitas'] }}</td>
                    <td>{{ $row['jumlah_jadwal'] }}</td>
                    <td>{{ $row['total_jam_penggunaan'] }}</td>
                    <td>{{ $row['prodi'] }}</td>
                    <td>{{ $row['mata_kuliah'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
