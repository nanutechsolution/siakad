<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Rekap Jadwal Kuliah</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.4;
        }

        /* PERBAIKAN: Mengubah selector dari global menjadi class khusus (.table-data) */
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .table-data th {
            background-color: #1e3a8a;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 8px 6px;
            border: 1px solid #1e3a8a;
        }

        .table-data td {
            border: 1px solid #cbd5e1;
            /* Slate 300 */
            padding: 6px 6px;
            vertical-align: middle;
        }

        /* Zebra Striping */
        .table-data tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Proteksi page break */
        .table-data tr {
            page-break-inside: avoid;
        }

        /* Helper Utilities */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-semibold {
            font-weight: 600;
        }

        .text-muted {
            color: #64748b;
            font-size: 8.5px;
        }

        .empty-row {
            text-align: center;
            padding: 20px !important;
            color: #64748b;
            font-style: italic;
            background-color: #f1f5f9;
        }
    </style>
</head>

<body>
    <!-- Menampilkan partial header -->
    @include('pdf.partials.header', ['judulDokumen' => 'Rekap Jadwal Kuliah'])
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 7%;">Hari</th>
                <th style="width: 10%;">Mulai</th>
                <th style="width: 10%;">Selesai</th>
                <th style="width: 8%;">Kode MK</th>
                <th style="width: 20%;">Mata Kuliah</th>
                <th style="width: 5%;">SKS</th>
                <th style="width: 22%;">Dosen Pengampu</th>
                <th style="width: 10%;">Prodi</th>
                <th style="width: 8%;">Ruang</th>
                <th style="width: 5%;">Kelas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td class="font-semibold">{{ $row['hari'] }}</td>
                <td class="text-center">{{ $row['jam_mulai'] }}</td>
                <td class="text-center">{{ $row['jam_selesai'] }}</td>
                <td class="text-muted">{{ $row['kode_mk'] }}</td>
                <td class="font-semibold">{{ $row['nama_mk'] }}</td>
                <td class="text-center font-semibold">{{ $row['sks'] }}</td>
                <td>{{ $row['dosen'] ?: '-' }}</td>
                <td>{{ $row['prodi'] }}</td>
                <td class="text-center text-muted">{{ $row['ruang'] ?: '-' }}</td>
                <td class="text-center font-semibold">{{ $row['kelas'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="empty-row">
                    Tidak ada data jadwal kuliah yang tersedia untuk filter ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Menampilkan partial footer -->
    @include('pdf.partials.footer')
</body>

</html>