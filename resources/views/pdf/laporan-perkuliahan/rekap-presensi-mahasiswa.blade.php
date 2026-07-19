<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Presensi Mahasiswa</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        .status-aman { color: #16794c; font-weight: bold; }
        .status-peringatan { color: #92620a; font-weight: bold; }
        .status-tidak { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Rekap Presensi Mahasiswa</h2>
    <p>Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Mata Kuliah</th>
                <th>Total Pertemuan</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Sakit</th>
                <th>Alpha</th>
                <th>Persentase</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $statusClass = match ($row['status']) {
                        'Aman' => 'status-aman',
                        'Peringatan' => 'status-peringatan',
                        default => 'status-tidak',
                    };
                @endphp
                <tr>
                    <td>{{ $row['nim'] }}</td>
                    <td>{{ $row['nama_mahasiswa'] }}</td>
                    <td>{{ $row['mata_kuliah'] }}</td>
                    <td>{{ $row['total_pertemuan'] }}</td>
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['alpha'] }}</td>
                    <td>{{ $row['persentase_kehadiran'] }}%</td>
                    <td class="{{ $statusClass }}">{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
