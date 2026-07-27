@extends('pdf.layouts.base')

@section('title', 'Transkrip Akademik Final - '.$nim)

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">TRANSKRIP AKADEMIK</h3>
<p class="text-center" style="margin-top:0;">Nomor: {{ $nomorDokumen ?? '-' }}</p>

<table style="width:100%; margin-top:10px; font-size:10px;">
    <tr>
        <td width="15%">Nama</td>
        <td width="2%">:</td>
        <td width="33%">{{ $namaMahasiswa }}</td>
        <td width="15%">Program Studi</td>
        <td width="2%">:</td>
        <td width="33%">{{ $namaProdi }} ({{ $jenjang }})</td>
    </tr>
    <tr>
        <td>NIM</td>
        <td>:</td>
        <td>{{ $nim }}</td>
        <td>Fakultas</td>
        <td>:</td>
        <td>{{ $namaFakultas }}</td>
    </tr>
    @if($tempatLahir && $tanggalLahir)
    <tr>
        <td>Tempat, Tanggal Lahir</td>
        <td>:</td>
        <td>{{ $tempatLahir }}, {{ \Carbon\Carbon::parse($tanggalLahir)->translatedFormat('d F Y') }}</td>
        <td>Angkatan</td>
        <td>:</td>
        <td>{{ $angkatan }}</td>
    </tr>
    @endif
    <tr>
        <td>Kurikulum</td>
        <td>:</td>
        <td colspan="4">{{ $namaKurikulum }}</td>
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
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $item['kodeMk'] }}</td>
            <td>{{ $item['namaMk'] }} @if($item['konversi']) <em>(konversi)</em> @endif</td>
            <td class="text-center">{{ $item['sks'] }}</td>
            <td class="text-center">{{ $item['nilaiHuruf'] }}</td>
            <td class="text-center">{{ $item['nilaiIndeks'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right"><strong>Total SKS Ditempuh</strong></td>
            <td class="text-center"><strong>{{ $totalSks }}</strong></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<table style="width:60%; margin-top:15px; font-size:10px;">
    <tr>
        <td width="60%">Syarat Minimal Kelulusan</td>
        <td width="5%">:</td>
        <td width="35%">{{ $syaratSks }} SKS</td>
    </tr>
    <tr>
        <td>Indeks Prestasi Kumulatif (IPK)</td>
        <td>:</td>
        <td><strong>{{ $ipk }}</strong></td>
    </tr>
</table>

@include('pdf.partials.qr-and-signatures')

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }} — Transkrip ini sah dan diterbitkan resmi oleh sistem SIAKAD.</p>
@endsection