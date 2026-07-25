@extends('pdf.layouts.base')

@section('title', 'KHS - '.$nim)

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">KARTU HASIL STUDI (KHS)</h3>
<p class="text-center" style="margin-top:0;">{{ $namaTahunAkademik }} — Semester {{ $semester }}</p>

<table style="width:100%; margin-top:10px; font-size:10px;">
    <tr>
        <td width="15%">NIM</td>
        <td width="2%">:</td>
        <td width="33%">{{ $nim }}</td>
        <td width="15%">Program Studi</td>
        <td width="2%">:</td>
        <td width="33%">{{ $namaProdi }}</td>
    </tr>
    <tr>
        <td>Nama</td>
        <td>:</td>
        <td>{{ $namaMahasiswa }}</td>
        <td>Status</td>
        <td>:</td>
        <td>{{ $statusKuliah }}</td>
    </tr>
</table>

<table class="data mt-10">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="14%">Kode MK</th>
            <th>Nama Mata Kuliah</th>
            <th width="8%">SKS</th>
            <th width="10%">Nilai Huruf</th>
            <th width="10%">Nilai Indeks</th>
            <th width="10%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $item['kodeMk'] }}</td>
            <td>{{ $item['namaMk'] }}</td>
            <td class="text-center">{{ $item['sks'] }}</td>
            <td class="text-center">{{ $item['nilaiHuruf'] }}</td>
            <td class="text-center">{{ $item['nilaiIndeks'] }}</td>
            <td class="text-center">{{ $item['statusAmbil'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<table style="width:50%; margin-top:15px; font-size:10px;">
    <tr>
        <td width="50%">SKS Semester Ini</td>
        <td width="10%">:</td>
        <td width="40%">{{ $sksSemester }}</td>
    </tr>
    <tr>
        <td>Total SKS Kumulatif</td>
        <td>:</td>
        <td>{{ $sksTotal }}</td>
    </tr>
    <tr>
        <td>IPS (Indeks Prestasi Semester)</td>
        <td>:</td>
        <td><strong>{{ $ips }}</strong></td>
    </tr>
    <tr>
        <td>IPK (Indeks Prestasi Kumulatif)</td>
        <td>:</td>
        <td><strong>{{ $ipk }}</strong></td>
    </tr>
</table>

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }} — Dokumen versi berjalan (Semi-Permanent), dapat berubah bila terjadi revisi nilai resmi.</p>
@endsection