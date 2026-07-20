<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Evaluasi Dosen Oleh Mahasiswa</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Evaluasi Dosen Oleh Mahasiswa (EDOM)</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Nama Dosen</th>
                <th>Mata Kuliah</th>
                <th>Jumlah Responden</th>
                <th>Total Mahasiswa</th>
                <th>Response Rate</th>
                <th>Rata-rata Nilai</th>
                <th>Jumlah Saran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['nama_dosen'] }}</td>
                    <td>{{ $row['mata_kuliah'] }}</td>
                    <td>{{ $row['jumlah_responden'] }}</td>
                    <td>{{ $row['total_mahasiswa_kelas'] }}</td>
                    <td>{{ $row['response_rate'] }}%</td>
                    <td>{{ $row['rata_rata_nilai'] }}</td>
                    <td>{{ $row['jumlah_saran'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
