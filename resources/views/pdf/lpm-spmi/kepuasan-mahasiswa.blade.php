<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kepuasan Mahasiswa</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Kepuasan Mahasiswa</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Kelompok</th>
                <th>Pertanyaan</th>
                <th>Jumlah Responden</th>
                <th>Rata-rata Skor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['kelompok'] }}</td>
                    <td>{{ $row['pertanyaan'] }}</td>
                    <td>{{ $row['jumlah_responden'] }}</td>
                    <td>{{ $row['rata_rata_skor'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
