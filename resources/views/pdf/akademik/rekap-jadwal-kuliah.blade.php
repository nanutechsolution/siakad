@extends('pdf.layouts.base')

@section('title', $judulDokumen ?? 'Rekap Jadwal Kuliah')

@section('content')

<style>
    /*
     * ============================================================
     * REKAP JADWAL KULIAH
     * A4 LANDSCAPE - RAMAH CETAK
     * ============================================================
     */

    .rekap-wrapper {
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .rekap-title {
        margin: 0 0 5px 0;
        padding: 0;
        text-align: center;
        font-size: 14pt;
        line-height: 1.2;
        font-weight: bold;
    }

    .rekap-filter {
        margin: 0 0 10px 0;
        padding: 0;
        text-align: center;
        font-size: 10pt;
        line-height: 1.35;
    }

    /*
     * ============================================================
     * RINGKASAN
     * ============================================================
     */

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 12px 0;
        table-layout: fixed;
    }

    .summary-table td {
        border: 1px solid #777;
        padding: 6px 7px;
        text-align: center;
        vertical-align: middle;
    }

    .summary-label {
        display: block;
        font-size: 8.5pt;
        color: #555;
        margin-bottom: 2px;
    }

    .summary-value {
        display: block;
        font-size: 12pt;
        font-weight: bold;
    }

    /*
     * ============================================================
     * TABEL JADWAL
     * ============================================================
     */

    .jadwal-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 10pt;
        line-height: 1.25;
    }

    .jadwal-table thead {
        display: table-header-group;
    }

    .jadwal-table th {
        border: 1px solid #000;
        background-color: #eeeeee;
        padding: 6px 5px;
        font-size: 9.5pt;
        font-weight: bold;
        text-align: center;
        vertical-align: middle;
    }

    .jadwal-table td {
        border: 1px solid #000;
        padding: 5px 5px;
        font-size: 10pt;
        vertical-align: middle;
    }

    .jadwal-table tr {
        page-break-inside: avoid;
    }

    /*
     * Lebar kolom
     *
     * Total = 100%
     */
    .col-waktu {
        width: 9%;
    }

    .col-kode {
        width: 7%;
    }

    .col-mk {
        width: 24%;
    }

    .col-prodi {
        width: 7%;
    }

    .col-sem {
        width: 5%;
    }

    .col-kelas {
        width: 8%;
    }

    .col-ruang {
        width: 8%;
    }

    .col-dosen {
        width: 25%;
    }

    .col-sks {
        width: 7%;
    }

    /*
     * ============================================================
     * ISI KOLOM
     * ============================================================
     */

    .text-center {
        text-align: center;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .waktu {
        white-space: nowrap;
        text-align: center;
        font-size: 9.5pt;
    }

    .kode-mk {
        text-align: center;
        font-size: 9.5pt;
        font-weight: bold;
        white-space: nowrap;
    }

    .nama-mk {
        font-size: 10pt;
        font-weight: bold;
        line-height: 1.3;
    }

    .prodi {
        text-align: center;
        font-size: 9.5pt;
    }

    .semester {
        text-align: center;
        font-size: 10pt;
    }

    .kelas {
        text-align: center;
        font-size: 10pt;
        font-weight: bold;
    }

    .ruang {
        text-align: center;
        font-size: 10pt;
    }

    .dosen {
        font-size: 10pt;
        line-height: 1.35;
        text-align: left;
        white-space: normal;
        word-wrap: break-word;
    }

    .sks {
        text-align: center;
        font-size: 10pt;
        font-weight: bold;
    }

    /*
     * ============================================================
     * PEMISAH HARI
     * ============================================================
     */

    .hari-row {
        page-break-inside: avoid;
        page-break-after: avoid;
    }

    .hari-cell {
        background-color: #dddddd;
        border: 1px solid #000;
        padding: 6px 8px;
        font-size: 10.5pt;
        font-weight: bold;
        text-align: left;
        letter-spacing: 0.3px;
    }

    /*
     * ============================================================
     * EMPTY STATE
     * ============================================================
     */

    .empty-row td {
        padding: 12px;
        text-align: center;
        font-size: 10pt;
    }

    /*
     * ============================================================
     * CATATAN
     * ============================================================
     */

    .document-note {
        margin: 12px 0 0 0;
        padding-top: 6px;
        border-top: 1px solid #999;
        font-size: 8.5pt;
        line-height: 1.35;
        color: #444;
        page-break-inside: avoid;
    }

    /*
     * ============================================================
     * PRINT / DOMPDF
     * ============================================================
     */

    @media print {
        .jadwal-table thead {
            display: table-header-group;
        }

        .jadwal-table tr {
            page-break-inside: avoid;
        }

        .hari-row {
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        .summary-table {
            page-break-inside: avoid;
        }
    }
</style>

<div class="rekap-wrapper">

    ```
    {{-- ============================================================
     JUDUL
     ============================================================ --}}
    <h3 class="rekap-title">
        {{ strtoupper($judulDokumen ?? 'REKAP JADWAL KULIAH') }}
    </h3>

    {{-- ============================================================
     FILTER / KONTEKS LAPORAN
     ============================================================ --}}
    @if (!empty($infoBaris))
    <div class="rekap-filter">
        @foreach ($infoBaris as $info)
        {{ $info }}@if (!$loop->last) &nbsp; | &nbsp; @endif
        @endforeach
    </div>
    @endif

    {{-- ============================================================
     RINGKASAN
     ============================================================ --}}
    <table class="summary-table">
        <tr>
            <td>
                <span class="summary-label">Total Jadwal</span>
                <span class="summary-value">
                    {{ $totalJadwal ?? 0 }}
                </span>
            </td>

            <td>
                <span class="summary-label">Total Kelas</span>
                <span class="summary-value">
                    {{ $totalKelas ?? 0 }}
                </span>
            </td>

            <td>
                <span class="summary-label">Dosen Pengampu</span>
                <span class="summary-value">
                    {{ $totalDosen ?? 0 }}
                </span>
            </td>

            <td>
                <span class="summary-label">Ruangan</span>
                <span class="summary-value">
                    {{ $totalRuang ?? 0 }}
                </span>
            </td>

            <td>
                <span class="summary-label">Hari Aktif</span>
                <span class="summary-value">
                    {{ $hariAktif ?? 0 }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ============================================================
     DATA JADWAL
     ============================================================ --}}
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

    $groupedRows = collect($rows ?? [])->groupBy('hari');
    $adaData = false;
    @endphp

    <table class="jadwal-table">

        {{-- HEADER INI AKAN DIULANG DOMPDF DI HALAMAN BERIKUTNYA --}}
        <thead>
            <tr>
                <th class="col-waktu">WAKTU</th>
                <th class="col-kode">KODE MK</th>
                <th class="col-mk">MATA KULIAH</th>
                <th class="col-prodi">PRODI</th>
                <th class="col-sem">SEM</th>
                <th class="col-kelas">KELAS</th>
                <th class="col-ruang">RUANG</th>
                <th class="col-dosen">DOSEN PENGAMPU</th>
                <th class="col-sks">SKS</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($hariUrutan as $hari)

            @if ($groupedRows->has($hari))

            @php
            $adaData = true;
            @endphp

            {{-- PEMISAH HARI --}}
            <tr class="hari-row">
                <td colspan="9" class="hari-cell">
                    {{ strtoupper($hari) }}
                </td>
            </tr>

            {{-- JADWAL --}}
            @foreach ($groupedRows->get($hari) as $row)

            <tr>

                {{-- WAKTU --}}
                <td class="waktu">
                    {{ $row['jam_mulai'] ?? '-' }}
                    -
                    {{ $row['jam_selesai'] ?? '-' }}
                </td>

                {{-- KODE MK --}}
                <td class="kode-mk">
                    {{ $row['kode_mk'] ?? '-' }}
                </td>

                {{-- MATA KULIAH --}}
                <td class="nama-mk">
                    {{ $row['nama_mk'] ?? '-' }}
                </td>

                {{-- PRODI --}}
                <td class="prodi">
                    {{ $row['prodi_kode'] ?? '-' }}
                </td>

                {{-- SEMESTER --}}
                <td class="semester">
                    {{ $row['semester'] ?? '-' }}
                </td>

                {{-- KELAS --}}
                <td class="kelas">
                    {{ $row['kelas'] ?? '-' }}
                </td>

                {{-- RUANG --}}
                <td class="ruang">
                    {{ $row['ruang'] ?? '-' }}
                </td>

                {{-- DOSEN --}}
                <td class="dosen">
                    {{ $row['dosen'] ?? '-' }}
                </td>

                {{-- SKS --}}
                <td class="sks">
                    {{ $row['sks'] ?? '-' }}
                </td>

            </tr>

            @endforeach

            @endif

            @endforeach

            @if (! $adaData)

            <tr class="empty-row">
                <td colspan="9">
                    Tidak ada data jadwal kuliah untuk filter yang dipilih.
                </td>
            </tr>

            @endif

        </tbody>
    </table>

    {{-- ============================================================
     CATATAN
     ============================================================ --}}
    @if (($totalJadwal ?? 0) > 0)
    <div class="document-note">
        Dokumen ini merupakan rekap jadwal kuliah yang dihasilkan
        melalui SIAKAD Universitas Stella Maris Sumba.
    </div>
    @endif
    ```

</div>

@endsection