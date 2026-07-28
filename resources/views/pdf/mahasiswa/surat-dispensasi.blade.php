@extends('pdf.layouts.base')

@section('title', 'Surat Keterangan Dispensasi - '.$nim)

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">SURAT KETERANGAN DISPENSASI AKADEMIK</h3>
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
    <tr>
        <td>Program Studi</td>
        <td>:</td>
        <td>{{ $namaProdi }}</td>
    </tr>
</table>

<p class="mt-20">
    diberikan dispensasi akademik jenis <strong>{{ $jenisDispensasi }}</strong>
    @if($alasan) dengan alasan: {{ $alasan }} @endif,
    berlaku sejak {{ \Carbon\Carbon::parse($berlakuMulai)->translatedFormat('d F Y') }}
    sampai dengan {{ \Carbon\Carbon::parse($berlakuSampai)->translatedFormat('d F Y') }}.
</p>

<p class="mt-10">Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

@include('pdf.partials.qr-and-signatures')

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }}</p>
@endsection