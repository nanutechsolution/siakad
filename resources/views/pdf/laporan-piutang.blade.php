<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Piutang UNMARIS</title>
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

        .danger {
            color: #dc2626;
        }
    </style>
</head>

<body>


    @include('pdf.partials.header', [
    'judulDokumen' => 'Laporan Rekapitulasi Keuangan',
    'infoBaris' => [
    'Dicetak pada: '.now()->format('d/m/Y H:i'),
    ],
    ])



    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIM</th>
                <th>Nama Mahasiswa</th>
                <th>Program Studi</th>
                <th>Angkatan</th>
                <th class="text-right">Total Tagihan</th>
                <th class="text-right">Terbayar</th>
                <th class="text-right">Sisa Tagihan</th>
                <th class="text-center">Jatuh Tempo</th>
                <th class="text-center">Keterlambatan</th>
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
                <td class="text-right">{{ number_format($row->total_tagihan, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($row->total_bayar, 0, ',', '.') }}</td>
                <td class="text-right danger">{{ number_format($row->sisa_tagihan, 0, ',', '.') }}</td>
                <td class="text-center">{{ $row->tenggat_waktu ? date('d-m-Y', strtotime($row->tenggat_waktu)) : '-' }}</td>
                <td class="text-center">
                    @if(is_null($row->hari_terlambat))
                    -
                    @elseif($row->hari_terlambat > 0)
                    {{ $row->hari_terlambat }} Hari
                    @else
                    Belum
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">Tidak ada data piutang pada filter ini.</td>
            </tr>
            @endforelse

            @if($data->count() > 0)
            <tr class="total-row">
                <td colspan="7" class="text-right">TOTAL PIUTANG KESELURUHAN</td>
                <td class="text-right danger">Rp {{ number_format($total_keseluruhan, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
            @endif
        </tbody>
    </table>

</body>

</html>