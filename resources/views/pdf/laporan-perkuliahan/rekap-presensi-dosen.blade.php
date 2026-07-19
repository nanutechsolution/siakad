<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi Dosen</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Rekap Presensi Dosen</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Nama Dosen</th>
                <th>Mata Kuliah</th>
                <th>Jumlah Pertemuan</th>
                <th>Terlaksana</th>
                <th>Tidak Terlaksana</th>
                <th>Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['nama_dosen'] }}</td>
                    <td>{{ $row['mata_kuliah'] }}</td>
                    <td>{{ $row['jumlah_pertemuan'] }}</td>
                    <td>{{ $row['terlaksana'] }}</td>
                    <td>{{ $row['tidak_terlaksana'] }}</td>
                    <td>{{ $row['persentase'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="6">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
