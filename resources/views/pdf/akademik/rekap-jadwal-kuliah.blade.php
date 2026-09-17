@extends('pdf.layouts.base')

@section('title', $judulDokumen ?? 'Rekap Jadwal Kuliah')

@section('content')

{{-- ================================================================
     JUDUL
     ================================================================ --}}

<h3
    class="text-center"
    style="
        margin: 0 0 2px 0;
        padding: 0;
        font-size: 14pt;
    ">
    {{ strtoupper($judulDokumen ?? 'REKAP JADWAL KULIAH') }}
</h3>


{{-- ================================================================
     FILTER / INFO
     ================================================================ --}}

@if (!empty($infoBaris))

<p
    class="text-center"
    style="
            margin: 0;
            padding: 0;
            font-size: 10px;
        ">
    @foreach ($infoBaris as $index => $info)

    {{ $info }}

    @if (!$loop->last)
    &nbsp; | &nbsp;
    @endif

    @endforeach
</p>

@endif


{{-- ================================================================
     RINGKASAN
     ================================================================ --}}

<table
    style="
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        margin-bottom: 10px;
        font-size: 9px;
        page-break-inside: avoid;
    ">
    <tr>

        <td
            style="
                width: 20%;
                border: 1px solid #cbd5e1;
                padding: 6px;
                text-align: center;
            ">
            <strong>Total Jadwal</strong>
            <br>
            {{ $totalJadwal ?? 0 }}
        </td>


        <td
            style="
                width: 20%;
                border: 1px solid #cbd5e1;
                padding: 6px;
                text-align: center;
            ">
            <strong>Total Kelas</strong>
            <br>
            {{ $totalKelas ?? 0 }}
        </td>


        <td
            style="
                width: 20%;
                border: 1px solid #cbd5e1;
                padding: 6px;
                text-align: center;
            ">
            <strong>Dosen</strong>
            <br>
            {{ $totalDosen ?? 0 }}
        </td>


        <td
            style="
                width: 20%;
                border: 1px solid #cbd5e1;
                padding: 6px;
                text-align: center;
            ">
            <strong>Ruangan</strong>
            <br>
            {{ $totalRuang ?? 0 }}
        </td>


        <td
            style="
                width: 20%;
                border: 1px solid #cbd5e1;
                padding: 6px;
                text-align: center;
            ">
            <strong>Hari Aktif</strong>
            <br>
            {{ $hariAktif ?? 0 }}
        </td>

    </tr>
</table>


{{-- ================================================================
     DATA
     ================================================================ --}}

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

$groupedRows = collect($rows ?? [])
->groupBy('hari');

@endphp


{{-- ================================================================
     TABEL JADWAL
     ================================================================ --}}

<table
    class="jadwal-table"
    style="
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5px;
        page-break-inside: auto;
    ">

    {{-- ============================================================
         HEADER TABEL
         ============================================================ --}}

    <thead style="display: table-header-group;">

        <tr>

            <th
                style="
                    width: 8%;
                    background: #1e3a8a;
                    color: #fff;
                    border: 1px solid #1e3a8a;
                    padding: 6px;
                    text-align: center;
                ">
                JAM
            </th>


            <th
                style="
                    width: 9%;
                    background: #1e3a8a;
                    color: #fff;
                    border: 1px solid #1e3a8a;
                    padding: 6px;
                    text-align: center;
                ">
                KODE
            </th>


            <th
                style="
                    width: 25%;
                    background: #1e3a8a;
                    color: #fff;
                    border: 1px solid #1e3a8a;
                    padding: 6px;
                    text-align: center;
                ">
                MATA KULIAH
            </th>


            <th
                style="
                    width: 14%;
                    background: #1e3a8a;
                    color: #fff;
                    border: 1px solid #1e3a8a;
                    padding: 6px;
                    text-align: center;
                ">
                PRODI / SEM / KELAS
            </th>


            <th
                style="
                    width: 9%;
                    background: #1e3a8a;
                    color: #fff;
                    border: 1px solid #1e3a8a;
                    padding: 6px;
                    text-align: center;
                ">
                RUANG
            </th>


            <th
                style="
                    width: 25%;
                    background: #1e3a8a;
                    color: #fff;
                    border: 1px solid #1e3a8a;
                    padding: 6px;
                    text-align: center;
                ">
                DOSEN PENGAMPU
            </th>


            <th
                style="
                    width: 5%;
                    background: #1e3a8a;
                    color: #fff;
                    border: 1px solid #1e3a8a;
                    padding: 6px;
                    text-align: center;
                ">
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

        <tr
            style="
                        page-break-inside: avoid;
                        page-break-after: avoid;
                    ">

            <td
                colspan="7"
                style="
                            background: #e8eef9;
                            color: #1e3a8a;
                            border-top: 2px solid #1e3a8a;
                            border-bottom: 1px solid #94a3b8;
                            padding: 6px;
                            font-weight: bold;
                            font-size: 9px;
                        ">
                {{ strtoupper($hari) }}
            </td>

        </tr>


        {{-- ====================================================
                     JADWAL
                     ==================================================== --}}

        @foreach ($groupedRows->get($hari) as $row)

        <tr
            style="
                            page-break-inside: avoid;
                        ">

            <td
                style="
                                border: 1px solid #cbd5e1;
                                padding: 5px;
                                text-align: center;
                                white-space: nowrap;
                            ">
                {{ $row['jam_mulai'] ?? '-' }}

                <br>

                <span style="color: #64748b;">
                    {{ $row['jam_selesai'] ?? '-' }}
                </span>
            </td>


            <td
                style="
                                border: 1px solid #cbd5e1;
                                padding: 5px;
                                text-align: center;
                            ">
                {{ $row['kode_mk'] ?? '-' }}
            </td>


            <td
                style="
                                border: 1px solid #cbd5e1;
                                padding: 5px;
                                font-weight: bold;
                            ">
                {{ $row['nama_mk'] ?? '-' }}
            </td>


            <td
                style="
                                border: 1px solid #cbd5e1;
                                padding: 5px;
                                text-align: center;
                                font-weight: bold;
                                color: #1e3a8a;
                            ">
                {{ $row['prodi_semester_kelas'] ?? '-' }}
            </td>


            <td
                style="
                                border: 1px solid #cbd5e1;
                                padding: 5px;
                                text-align: center;
                            ">
                {{ $row['ruang'] ?? '-' }}
            </td>


            <td
                style="
                                border: 1px solid #cbd5e1;
                                padding: 5px;
                            ">
                {{ $row['dosen'] ?? '-' }}
            </td>


            <td
                style="
                                border: 1px solid #cbd5e1;
                                padding: 5px;
                                text-align: center;
                            ">
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

        <tr>

            <td
                colspan="7"
                style="
                        border: 1px solid #cbd5e1;
                        padding: 20px;
                        text-align: center;
                        color: #64748b;
                    ">
                Tidak ada data jadwal kuliah
                untuk filter yang dipilih.
            </td>

        </tr>

        @endif

    </tbody>

</table>


{{-- ================================================================
     CATATAN
     ================================================================ --}}

@if (($totalJadwal ?? 0) > 0)

<p
    style="
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            color: #64748b;
            font-size: 8px;
            page-break-inside: avoid;
        ">
    Dokumen ini merupakan rekap jadwal perkuliahan
    yang dihasilkan dari Sistem Informasi Akademik (SIAKAD).
    Data mengikuti filter dan kondisi jadwal pada saat dokumen dicetak.
</p>

@endif

@endsection