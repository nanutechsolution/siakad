<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $judulDokumen ?? 'Rekap Jadwal Kuliah' }}</title>

    <style>
        @page {
            margin: 18mm 10mm 15mm 10mm;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.35;
        }

        /* =========================================================
         * RINGKASAN
         * ========================================================= */

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin: 0 0 12px 0;
        }

        .summary td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .summary-label {
            color: #64748b;
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .summary-value {
            color: #0f172a;
            font-size: 10px;
            font-weight: bold;
        }

        /* =========================================================
         * TABEL
         * ========================================================= */

        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 15px;
        }

        .table-data thead {
            display: table-header-group;
        }

        .table-data tr {
            page-break-inside: avoid;
        }

        .table-data th {
            background-color: #1e3a8a;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 0.2px;
            padding: 7px 5px;
            border: 1px solid #1e3a8a;
            text-align: center;
            vertical-align: middle;
        }

        .table-data td {
            border: 1px solid #cbd5e1;
            padding: 5px 5px;
            vertical-align: middle;
        }

        .table-data tbody tr.data-row:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* =========================================================
         * GROUP HARI
         * ========================================================= */

        .day-row td {
            background-color: #e8eef9;
            color: #1e3a8a;
            border-top: 2px solid #1e3a8a;
            border-bottom: 1px solid #94a3b8;
            padding: 6px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .day-row:first-child td {
            border-top: 1px solid #1e3a8a;
        }

        /* =========================================================
         * TYPOGRAPHY
         * ========================================================= */

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .font-semibold {
            font-weight: bold;
        }

        .text-muted {
            color: #64748b;
        }

        .kode {
            font-size: 8px;
            color: #475569;
            white-space: nowrap;
        }

        .mata-kuliah {
            font-weight: bold;
            color: #0f172a;
        }

        .kelas {
            text-align: center;
            font-weight: bold;
            color: #1e3a8a;
            white-space: nowrap;
        }

        .ruang {
            text-align: center;
            color: #475569;
        }

        .dosen {
            color: #334155;
        }

        .jam {
            text-align: center;
            white-space: nowrap;
            color: #334155;
        }

        /* =========================================================
         * EMPTY
         * ========================================================= */

        .empty-row {
            text-align: center;
            padding: 25px !important;
            color: #64748b;
            font-style: italic;
            background-color: #f8fafc;
        }

        /* =========================================================
         * FOOTNOTE
         * ========================================================= */

        .document-note {
            margin-top: 5px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 7.5px;
        }
    </style>
</head>

<body>

    {{-- =========================================================
         HEADER INSTITUSI
         ========================================================= --}}
    @include('pdf.partials.header', [
    'judulDokumen' => $judulDokumen,
    'infoBaris' => $infoBaris,
    ])


    {{-- =========================================================
         RINGKASAN DOKUMEN
         ========================================================= --}}

    @php
    $totalJadwal = count($rows);

    $totalDosen = collect($rows)
    ->pluck('dosen')
    ->flatMap(function ($dosen) {
    return array_filter(
    array_map('trim', explode(',', $dosen ?? ''))
    );
    })
    ->unique()
    ->count();

    $totalRuang = collect($rows)
    ->pluck('ruang')
    ->filter(fn ($ruang) => filled($ruang) && $ruang !== '-')
    ->unique()
    ->count();

    $totalKelas = collect($rows)
    ->pluck('prodi_semester_kelas')
    ->filter()
    ->unique()
    ->count();

    $hariAktif = collect($rows)
    ->pluck('hari')
    ->filter()
    ->unique()
    ->count();
    @endphp

    <table class="summary">
        <tr>
            <td style="width: 20%;">
                <div class="summary-label">Total Jadwal</div>
                <div class="summary-value">{{ $totalJadwal }}</div>
            </td>

            <td style="width: 20%;">
                <div class="summary-label">Total Kelas</div>
                <div class="summary-value">{{ $totalKelas }}</div>
            </td>

            <td style="width: 20%;">
                <div class="summary-label">Dosen</div>
                <div class="summary-value">{{ $totalDosen }}</div>
            </td>

            <td style="width: 20%;">
                <div class="summary-label">Ruangan</div>
                <div class="summary-value">{{ $totalRuang }}</div>
            </td>

            <td style="width: 20%;">
                <div class="summary-label">Hari Aktif</div>
                <div class="summary-value">{{ $hariAktif }}</div>
            </td>
        </tr>
    </table>


    {{-- =========================================================
         DATA JADWAL
         ========================================================= --}}

    @php
    $hariUrutan = [
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
    'Minggu',
    ];

    $groupedRows = collect($rows)
    ->sortBy(function ($row) use ($hariUrutan) {
    $hariIndex = array_search($row['hari'], $hariUrutan);

    return sprintf(
    '%02d-%s',
    $hariIndex === false ? 99 : $hariIndex,
    $row['jam_mulai'] ?? '99:99'
    );
    })
    ->groupBy('hari');
    @endphp


    <table class="table-data">

        <thead>
            <tr>
                <th style="width: 7%;">Jam</th>
                <th style="width: 9%;">Kode</th>
                <th style="width: 25%;">Mata Kuliah</th>
                <th style="width: 14%;">Prodi / Sem / Kelas</th>
                <th style="width: 10%;">Ruang</th>
                <th style="width: 25%;">Dosen Pengampu</th>
            </tr>
        </thead>

        <tbody>

            @forelse ($hariUrutan as $hari)

            @if ($groupedRows->has($hari))

            {{-- GROUP HARI --}}
            <tr class="day-row">
                <td colspan="6">
                    {{ strtoupper($hari) }}
                </td>
            </tr>


            {{-- JADWAL --}}
            @foreach ($groupedRows->get($hari) as $row)

            <tr class="data-row">

                {{-- JAM --}}
                <td class="jam">
                    {{ $row['jam_mulai'] ?? '-' }}
                    <br>
                    <span class="text-muted">
                        {{ $row['jam_selesai'] ?? '-' }}
                    </span>
                </td>


                {{-- KODE MK --}}
                <td class="kode text-center">
                    {{ $row['kode_mk'] ?? '-' }}
                </td>


                {{-- MATA KULIAH --}}
                <td class="mata-kuliah">
                    {{ $row['nama_mk'] ?? '-' }}
                </td>


                {{-- KELAS --}}
                <td class="kelas">
                    {{ $row['prodi_semester_kelas'] ?? '-' }}
                </td>


                {{-- RUANG --}}
                <td class="ruang">
                    {{ $row['ruang'] ?: '-' }}
                </td>


                {{-- DOSEN --}}
                <td class="dosen">
                    {{ $row['dosen'] ?: '-' }}
                </td>

            </tr>

            @endforeach

            @endif

            @empty

            <tr>
                <td colspan="6" class="empty-row">
                    Tidak ada data jadwal kuliah yang tersedia untuk filter ini.
                </td>
            </tr>

            @endforelse

        </tbody>

    </table>


    {{-- =========================================================
         CATATAN
         ========================================================= --}}

    @if ($totalJadwal > 0)
    <div class="document-note">
        Dokumen ini merupakan rekap jadwal perkuliahan yang dihasilkan
        dari sistem SIAKAD. Data mengikuti filter dan kondisi jadwal
        pada saat dokumen dicetak.
    </div>
    @endif


    {{-- =========================================================
         FOOTER
         ========================================================= --}}

    @include('pdf.partials.footer')

</body>

</html>