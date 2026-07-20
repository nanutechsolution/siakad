<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Capaian Pembelajaran</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <h2>Capaian Pembelajaran</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Kode Indikator</th>
                <th>Nama Indikator</th>
                <th>Standar</th>
                <th>Prodi</th>
                <th>Tahun</th>
                <th>Target</th>
                <th>Capaian</th>
                <th>% Capaian</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['kode_indikator'] }}</td>
                    <td>{{ $row['nama_indikator'] }}</td>
                    <td>{{ $row['standar'] }}</td>
                    <td>{{ $row['prodi'] }}</td>
                    <td>{{ $row['tahun'] }}</td>
                    <td>{{ $row['target_nilai'] }}</td>
                    <td>{{ $row['capaian_nilai'] }}</td>
                    <td>{{ $row['persen_capaian'] }}%</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
