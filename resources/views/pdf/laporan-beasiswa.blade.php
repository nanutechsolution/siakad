<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Beasiswa UNMARIS</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #2c3e50;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #cbd5e1;
            padding: 6px;
        }

        th {
            background-color: #f1f5f9;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #e2e8f0;
            font-weight: bold;
        }

        .success {
            color: #16a34a;
        }
    </style>
</head>

<body>

    @include('pdf.partials.header', [
    'judulDokumen' => 'Laporan Beasiswa & Potongan Mahasiswa',
    'infoBaris' => [
    'Periode Akademik: '.($tahun_akademik ?? 'Semua Periode'),
    'Dicetak pada: '.now()->format('d/m/Y H:i'),
    ],
    ])

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Program Studi</th>
                <th class="text-center">Angkatan</th>
                <th>Nama Beasiswa</th>
                <th>No. SK</th>
                <th class="text-right">Total Potongan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->nim }}</td>
                <td>{{ $row->nama_mahasiswa }}</td>
                <td>{{ $row->nama_prodi }}</td>
                <td class="text-center">{{ $row->angkatan }}</td>
                <td>
                    <strong>{{ $row->nama_beasiswa }}</strong><br>
                    <span style="font-size: 9px; color: #64748b;">{{ $row->kategori }}</span>
                </td>
                <td>{{ $row->nomor_sk ?? '-' }}</td>
                <td class="text-right success">{{ number_format($row->total_potongan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada mahasiswa penerima beasiswa pada filter ini.</td>
            </tr>
            @endforelse

            @if($data->count() > 0)
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL NILAI BEASISWA DIBERIKAN KESELURUHAN</td>
                <td class="text-right success">Rp {{ number_format($total_potongan, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

</body>

</html>