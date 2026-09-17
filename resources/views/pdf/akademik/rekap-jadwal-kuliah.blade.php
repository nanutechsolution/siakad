@extends('pdf.layouts.base')

@section('title', $judulDokumen ?? 'Rekap Jadwal Kuliah')

@section('content')

<style>
    /*
    |--------------------------------------------------------------------------
    | KHUSUS REKAP JADWAL KULIAH
    |--------------------------------------------------------------------------
    |
    | Kop surat hanya muncul pada halaman pertama.
    | Kop TIDAK menggunakan position: fixed.
    |
    */

    @page {
        size: A4 landscape;
        margin: 40px 35px 55px 35px;
    }

    /*
    |--------------------------------------------------------------------------
    | MATIKAN HEADER FIXED DARI BASE
    |--------------------------------------------------------------------------
    |
    | Ini penting agar kop dari pdf.layouts.base tidak muncul
    | kembali di halaman 2, 3, dst.
    |
    */

    header {
        display: none !important;
    }

    /*
    |--------------------------------------------------------------------------
    | MAIN
    |--------------------------------------------------------------------------
    */

    main {
        margin: 0 !important;
        padding: 0 !important;
    }

    /*
    |--------------------------------------------------------------------------
    | KOP SURAT
    |--------------------------------------------------------------------------
    |
    | Kop ini adalah bagian normal dari content.
    | Karena bukan fixed, otomatis hanya muncul sekali.
    |
    */

    .rekap-kop {
        width: 100%;
        margin: 0 0 8px 0;
        padding: 0 0 7px 0;
        border-bottom: 2px solid #000;
        page-break-inside: avoid;
    }

    .rekap-kop-table {
        width: 100%;
        border-collapse: collapse;
    }

    .rekap-kop-logo {
        width: 12%;
        text-align: center;
        vertical-align: middle;
    }

    .rekap-kop-logo img {
        max-width: 65px;
        max-height: 65px;
    }

    .rekap-kop-content {
        width: 88%;
        text-align: center;
        vertical-align: middle;
    }

    .rekap-kop-universitas {
        margin: 0;
        padding: 0;
        font-size: 17pt;
        line-height: 1.15;
        font-weight: bold;
    }

    .rekap-kop-alamat {
        margin: 2px 0 0 0;
        padding: 0;
        font-size: 9pt;
        line-height: 1.25;
    }

    .rekap-kop-kontak {
        margin: 1px 0 0 0;
        padding: 0;
        font-size: 8.5pt;
        line-height: 1.25;
    }

    /*
    |--------------------------------------------------------------------------
    | JUDUL
    |--------------------------------------------------------------------------
    */

    .rekap-title {
        margin: 0 0 2px 0;
        padding: 0;
        text-align: center;
        font-size: 14pt;
        line-height: 1.2;
        font-weight: bold;
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER / INFO
    |--------------------------------------------------------------------------
    */

    .rekap-filter {
        margin: 0;
        padding: 0;
        text-align: center;
        font-size: 10pt;
        line-height: 1.35;
    }

    /*
    |--------------------------------------------------------------------------
    | RINGKASAN
    |--------------------------------------------------------------------------
    */

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 10px;
        font-size: 9pt;
        page-break-inside: avoid;
    }

    .summary-table td {
        width: 20%;
        border: 1px solid #cbd5e1;
        padding: 6px;
        text-align: center;
        vertical-align: middle;
    }

    /*
    |--------------------------------------------------------------------------
    | TABEL JADWAL
    |--------------------------------------------------------------------------
    */

    .jadwal-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10pt;
        page-break-inside: auto;
        table-layout: fixed;
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER TABEL DIULANG SETIAP HALAMAN
    |--------------------------------------------------------------------------
    |
    | Ini BUKAN kop surat.
    | Header kolom JAM/KODE/MATA KULIAH tetap muncul di halaman 2 dst.
    |
    */

    .jadwal-table thead {
        display: table-header-group;
    }

    .jadwal-table tbody {
        display: table-row-group;
    }

    .jadwal-table tr {
        page-break-inside: avoid;
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER KOLOM
    |--------------------------------------------------------------------------
    */

    .jadwal-table th {
        background: #1e3a8a;
        color: #fff;
        border: 1px solid #1e3a8a;
        padding: 6px;
        text-align: center;
        vertical-align: middle;
        font-size: 9.5pt;
        font-weight: bold;
    }

    /*
    |--------------------------------------------------------------------------
    | LEBAR KOLOM
    |--------------------------------------------------------------------------
    */

    .col-jam {
        width: 8%;
    }

    .col-kode {
        width: 9%;
    }

    .col-mk {
        width: 25%;
    }

    .col-prodi {
        width: 14%;
    }

    .col-ruang {
        width: 9%;
    }

    .col-dosen {
        width: 25%;
    }

    .col-sks {
        width: 5%;
    }

    /*
    |--------------------------------------------------------------------------
    | BODY TABEL
    |--------------------------------------------------------------------------
    */

    .jadwal-table td {
        border: 1px solid #cbd5e1;
        padding: 5px;
        font-size: 10pt;
        vertical-align: middle;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /*
    |--------------------------------------------------------------------------
    | JAM
    |--------------------------------------------------------------------------
    */

    .cell-jam {
        text-align: center;
        white-space: nowrap;
    }

    .cell-jam-selesai {
        color: #64748b;
    }

    /*
    |--------------------------------------------------------------------------
    | KODE
    |--------------------------------------------------------------------------
    */

    .cell-kode {
        text-align: center;
        font-weight: 500;
    }

    /*
    |--------------------------------------------------------------------------
    | MATA KULIAH
    |--------------------------------------------------------------------------
    */

    .cell-mk {
        font-weight: bold;
        line-height: 1.3;
    }

    /*
    |--------------------------------------------------------------------------
    | PRODI / SEM / KELAS
    |--------------------------------------------------------------------------
    */

    .cell-prodi {
        text-align: center;
        font-weight: bold;
        color: #1e3a8a;
        line-height: 1.3;
    }

    /*
    |--------------------------------------------------------------------------
    | RUANG
    |--------------------------------------------------------------------------
    */

    .cell-ruang {
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | DOSEN
    |--------------------------------------------------------------------------
    */

    .cell-dosen {
        line-height: 1.35;
        white-space: normal;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /*
    |--------------------------------------------------------------------------
    | SKS
    |--------------------------------------------------------------------------
    */

    .cell-sks {
        text-align: center;
        font-weight: bold;
    }

    /*
    |--------------------------------------------------------------------------
    | HEADER HARI
    |--------------------------------------------------------------------------
    */

    .hari-row {
        page-break-inside: avoid;
        page-break-after: avoid;
    }

    .hari-cell {
        background: #e8eef9;
        color: #1e3a8a;
        border-top: 2px solid #1e3a8a;
        border-bottom: 1px solid #94a3b8;
        padding: 6px;
        font-weight: bold;
        font-size: 10pt;
    }

    /*
    |--------------------------------------------------------------------------
    | DATA KOSONG
    |--------------------------------------------------------------------------
    */

    .empty-row td {
        border: 1px solid #cbd5e1;
        padding: 20px;
        text-align: center;
        color: #64748b;
        font-size: 10pt;
    }

    /*
    |--------------------------------------------------------------------------
    | CATATAN
    |--------------------------------------------------------------------------
    */

    .document-note {
        margin-top: 12px;
        padding-top: 6px;
        border-top: 1px solid #cbd5e1;
        color: #64748b;
        font-size: 8pt;
        line-height: 1.35;
        page-break-inside: avoid;
    }
</style>

{{-- ================================================================
KOP SURAT
================================================================

```
 PENTING:
 Kop ini bukan fixed.
 Jadi hanya muncul sekali, di awal dokumen.
 ================================================================ --}}
```

<div class="rekap-kop">

    ```
    <table class="rekap-kop-table">

        <tr>

            {{-- LOGO --}}
            <td class="rekap-kop-logo">

                @if (file_exists(public_path('favicons/logo-unmaris.svg')))

                <img
                    src="{{ public_path('favicons/logo-unmaris.svg') }}"
                    alt="Logo UNMARIS">

                @elseif (file_exists(public_path('images/logo-unmaris.png')))

                <img
                    src="{{ public_path('images/logo-unmaris.png') }}"
                    alt="Logo UNMARIS">

                @endif

            </td>


            {{-- IDENTITAS UNIVERSITAS --}}
            <td class="rekap-kop-content">

                <div class="rekap-kop-universitas">
                    UNIVERSITAS STELLA MARIS SUMBA
                </div>

                <div class="rekap-kop-alamat">
                    {{ config(
                    'app.institution_address',
                    'Sumba, Nusa Tenggara Timur'
                ) }}
                </div>

                @if (config('app.institution_contact'))

                <div class="rekap-kop-kontak">
                    {{ config('app.institution_contact') }}
                </div>

                @endif

            </td>

        </tr>

    </table>
    ```

</div>

{{-- ================================================================
JUDUL
================================================================ --}}

<h3 class="rekap-title">

    ```
    {{ strtoupper($judulDokumen ?? 'REKAP JADWAL KULIAH') }}
    ```

</h3>

{{-- ================================================================
FILTER / INFO
================================================================ --}}

@if (!empty($infoBaris))

```
<div class="rekap-filter">

    @foreach ($infoBaris as $info)

    {{ $info }}

    @if (!$loop->last)

    &nbsp; | &nbsp;

    @endif

    @endforeach

</div>
```

@endif

{{-- ================================================================
RINGKASAN
================================================================ --}}

<table class="summary-table">

    ```
    <tr>

        <td>

            <strong>Total Jadwal</strong>

            <br>

            {{ $totalJadwal ?? 0 }}

        </td>


        <td>

            <strong>Total Kelas</strong>

            <br>

            {{ $totalKelas ?? 0 }}

        </td>


        <td>

            <strong>Dosen</strong>

            <br>

            {{ $totalDosen ?? 0 }}

        </td>


        <td>

            <strong>Ruangan</strong>

            <br>

            {{ $totalRuang ?? 0 }}

        </td>


        <td>

            <strong>Hari Aktif</strong>

            <br>

            {{ $hariAktif ?? 0 }}

        </td>

    </tr>
    ```

</table>

{{-- ================================================================
DATA
================================================================ --}}

@php

```
$hariUrutan = [
'Senin',
'Selasa',
'Rabu',
'Kamis',
'Jumat',
'Sabtu',
'Minggu',
];

$groupedRows = collect($rows ?? [])
->groupBy('hari');
```

@endphp

{{-- ================================================================
TABEL JADWAL
================================================================ --}}

<table class="jadwal-table">

    ```
    {{-- ============================================================
     HEADER TABEL
     ============================================================ --}}

    <thead>

        <tr>

            <th class="col-jam">
                JAM
            </th>

            <th class="col-kode">
                KODE
            </th>

            <th class="col-mk">
                MATA KULIAH
            </th>

            <th class="col-prodi">
                PRODI / SEM / KELAS
            </th>

            <th class="col-ruang">
                RUANG
            </th>

            <th class="col-dosen">
                DOSEN PENGAMPU
            </th>

            <th class="col-sks">
                SKS
            </th>

        </tr>

    </thead>


    {{-- ============================================================
     BODY
     ============================================================ --}}

    <tbody>

        @php
        $adaData = false;
        @endphp


        @foreach ($hariUrutan as $hari)

        @if ($groupedRows->has($hari))

        @php
        $adaData = true;
        @endphp


        {{-- ====================================================
                 HEADER HARI
                 ==================================================== --}}

        <tr class="hari-row">

            <td
                colspan="7"
                class="hari-cell">

                {{ strtoupper($hari) }}

            </td>

        </tr>


        {{-- ====================================================
                 JADWAL
                 ==================================================== --}}

        @foreach ($groupedRows->get($hari) as $row)

        <tr>

            {{-- JAM --}}
            <td class="cell-jam">

                {{ $row['jam_mulai'] ?? '-' }}

                <br>

                <span class="cell-jam-selesai">

                    {{ $row['jam_selesai'] ?? '-' }}

                </span>

            </td>


            {{-- KODE --}}
            <td class="cell-kode">

                {{ $row['kode_mk'] ?? '-' }}

            </td>


            {{-- MATA KULIAH --}}
            <td class="cell-mk">

                {{ $row['nama_mk'] ?? '-' }}

            </td>


            {{-- PRODI / SEM / KELAS --}}
            <td class="cell-prodi">

                {{ $row['prodi_semester_kelas'] ?? '-' }}

            </td>


            {{-- RUANG --}}
            <td class="cell-ruang">

                {{ $row['ruang'] ?? '-' }}

            </td>


            {{-- DOSEN --}}
            <td class="cell-dosen">

                {{ $row['dosen'] ?? '-' }}

            </td>


            {{-- SKS --}}
            <td class="cell-sks">

                {{ $row['sks'] ?? '-' }}

            </td>

        </tr>

        @endforeach

        @endif

        @endforeach


        {{-- ============================================================
         DATA KOSONG
         ============================================================ --}}

        @if (! $adaData)

        <tr class="empty-row">

            <td colspan="7">

                Tidak ada data jadwal kuliah
                untuk filter yang dipilih.

            </td>

        </tr>

        @endif

    </tbody>
    ```

</table>

{{-- ================================================================
CATATAN
================================================================ --}}

@if (($totalJadwal ?? 0) > 0)

<div class="document-note">

    Dokumen ini merupakan rekap jadwal perkuliahan
    yang dihasilkan dari Sistem Informasi Akademik (SIAKAD).
    Data mengikuti filter dan kondisi jadwal pada saat dokumen dicetak.

</div>

@endif

@endsection