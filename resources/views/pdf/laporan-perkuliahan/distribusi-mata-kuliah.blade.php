<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Distribusi Mata Kuliah</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Distribusi Mata Kuliah</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Kode MK</th>
                <th>Nama MK</th>
                <th>SKS</th>
                <th>Semester Kurikulum</th>
                <th>Jumlah Kelas</th>
                <th>Jumlah Peserta</th>
                <th>Dosen Pengampu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['kode_mk'] }}</td>
                    <td>{{ $row['nama_mk'] }}</td>
                    <td>{{ $row['sks'] }}</td>
                    <td>{{ $row['semester_kurikulum'] }}</td>
                    <td>{{ $row['jumlah_kelas'] }}</td>
                    <td>{{ $row['jumlah_peserta'] }}</td>
                    <td>{{ $row['dosen_pengampu'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
