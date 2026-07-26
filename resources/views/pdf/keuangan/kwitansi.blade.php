@extends('pdf.layouts.base')

@section('title', 'Kwitansi - '.($nomorDokumen ?? $pembayaranId))

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">KWITANSI PEMBAYARAN</h3>
<p class="text-center" style="margin-top:0;">No. {{ $nomorDokumen ?? '-' }}</p>

<table style="width:100%; margin-top:10px; font-size:10px;">
    <tr>
        <td width="15%">NIM</td>
        <td width="2%">:</td>
        <td width="33%">{{ $nim }}</td>
        <td width="15%">Tahun Akademik</td>
        <td width="2%">:</td>
        <td width="33%">{{ $namaTahunAkademik }}</td>
    </tr>
    <tr>
        <td>Nama</td>
        <td>:</td>
        <td>{{ $namaMahasiswa }}</td>
        <td>Program Studi</td>
        <td>:</td>
        <td>{{ $namaProdi }}</td>
    </tr>
</table>

<table class="data mt-10">
    <tr>
        <th width="30%">Keterangan</th>
        <td>{{ $namaTagihan }}</td>
    </tr>
    <tr>
        <th>Nominal Dibayar</th>
        <td><strong>Rp {{ number_format($nominalBayar, 0, ',', '.') }}</strong></td>
    </tr>
    <tr>
        <th>Metode Pembayaran</th>
        <td>{{ $metodePembayaran }}</td>
    </tr>
    <tr>
        <th>Tanggal Bayar</th>
        <td>{{ \Carbon\Carbon::parse($tanggalBayar)->translatedFormat('d F Y H:i') }}</td>
    </tr>
    @if($keteranganPengirim)
    <tr>
        <th>Keterangan Pengirim</th>
        <td>{{ $keteranganPengirim }}</td>
    </tr>
    @endif
</table>

<table style="width:100%; margin-top:40px; font-size:10px;">
    <tr>
        @foreach($signers as $signer)
        <td class="text-center" style="width:{{ 100 / count($signers) }}%;">
            <p>{{ $signer['label'] }}</p>
            <div style="height:50px;"></div>
            <p><strong>{{ $signer['namaLengkap'] }}</strong></p>
        </td>
        @endforeach
    </tr>
</table>

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }} — Dokumen ini sah sebagai bukti pembayaran resmi.</p>
@endsection