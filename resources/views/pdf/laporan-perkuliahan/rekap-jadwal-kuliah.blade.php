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
            letter-spacing: 0.3px;
            padding: 8px 6px;
            border: 1px solid #1e3a8a;
            text-align: center;
        }

        .table-data td {
            border: 1px solid #cbd5e1;
            padding: 6px;
            vertical-align: middle;
        }

        .table-data tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .table-data thead {
            display: table-header-group;
        }

        .table-data tr {
            page-break-inside: avoid;
        }

        .text-center {
            text-align: center;
        }

        .font-semibold {
            font-weight: bold;
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

    @include('pdf.partials.header', [
    'judulDokumen' => $judulDokumen,
    'infoBaris' => $infoBaris,
    ])

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 8%;">Hari</th>
                <th style="width: 13%;">Jam</th>
                <th style="width: 10%;">Kode MK</th>
                <th style="width: 24%;">Nama Mata Kuliah</th>
                <th style="width: 15%;">Prodi/Sem/Kelas</th>
                <th style="width: 10%;">Ruangan</th>
                <th style="width: 20%;">Dosen Pengampu</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
            <tr>
                <td class="font-semibold">
                    {{ $row['hari'] }}
                </td>

                <td class="text-center">
                    {{ $row['jam_mulai'] }} - {{ $row['jam_selesai'] }}
                </td>

                <td class="text-muted">
                    {{ $row['kode_mk'] }}
                </td>

                <td class="font-semibold">
                    {{ $row['nama_mk'] }}
                </td>

                <td class="text-center font-semibold">
                    {{ $row['prodi_semester_kelas'] }}
                </td>

                <td class="text-center text-muted">
                    {{ $row['ruang'] ?: '-' }}
                </td>

                <td>
                    {{ $row['dosen'] ?: '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="empty-row">
                    Tidak ada data jadwal kuliah yang tersedia untuk filter ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdf.partials.footer')

</body>

</html>