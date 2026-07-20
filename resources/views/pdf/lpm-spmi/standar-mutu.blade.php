<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Standar Mutu</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Standar Mutu</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Kode Standar</th>
                <th>Nama Standar</th>
                <th>Kategori</th>
                <th>Target Pencapaian</th>
                <th>Versi</th>
                <th>Jumlah Indikator</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['kode_standar'] }}</td>
                    <td>{{ $row['nama_standar'] }}</td>
                    <td>{{ $row['kategori'] }}</td>
                    <td>{{ $row['target_pencapaian'] }}</td>
                    <td>{{ $row['versi'] }}</td>
                    <td>{{ $row['jumlah_indikator'] }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
