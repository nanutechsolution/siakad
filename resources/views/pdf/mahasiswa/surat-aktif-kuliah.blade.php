@extends('pdf.layouts.base')

@section('title', 'Surat Keterangan Aktif Kuliah - '.$nim)

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">SURAT KETERANGAN AKTIF KULIAH</h3>
<p class="text-center" style="margin-top:0;">Nomor: {{ $nomorDokumen ?? '-' }}</p>

<p class="mt-20">Yang bertanda tangan di bawah ini menerangkan bahwa:</p>

<table style="width:100%; margin-top:10px; font-size:10px;">
    <tr>
        <td width="25%">Nama</td>
        <td width="2%">:</td>
        <td>{{ $namaMahasiswa }}</td>
    </tr>
    <tr>
        <td>NIM</td>
        <td>:</td>
        <td>{{ $nim }}</td>
    </tr>
    @if($tempatLahir && $tanggalLahir)
    <tr>
        <td>Tempat, Tanggal Lahir</td>
        <td>:</td>
        <td>{{ $tempatLahir }}, {{ \Carbon\Carbon::parse($tanggalLahir)->translatedFormat('d F Y') }}</td>
    </tr>
    @endif
    <tr>
        <td>Program Studi</td>
        <td>:</td>
        <td>{{ $namaProdi }} ({{ $jenjang }})</td>
    </tr>
    <tr>
        <td>Fakultas</td>
        <td>:</td>
        <td>{{ $namaFakultas }}</td>
    </tr>
</table>

<p class="mt-20">
    adalah benar terdaftar sebagai mahasiswa <strong>AKTIF</strong> pada {{ $namaTahunAkademik }} Semester {{ $semester }} di institusi kami.
</p>

<p class="mt-10">Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

<table style="width:100%; margin-top:40px; font-size:10px;">
    <tr>
        <td width="60%"></td>
        <td width="40%" class="text-center">
            @foreach($signers as $signer)
            <p>{{ $signer['label'] }}</p>
            <div style="height:50px;"></div>
            <p><strong>{{ $signer['namaLengkap'] }}</strong></p>
            @endforeach
        </td>
    </tr>
</table>

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }}</p>
@endsection