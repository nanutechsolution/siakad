@extends('pdf.layouts.base')

@section('title', 'SK Pembimbing Akademik - '.$namaPembimbing)

@section('content')

<h3 class="text-center" style="margin-bottom:2px;">
    SURAT KEPUTUSAN
</h3>

<p class="text-center" style="margin-top:0;">
    PENETAPAN PEMBIMBING AKADEMIK
</p>

<table style="width:100%; margin-top:20px; font-size:10px;">
    <tr>
        <td width="20%">Nomor</td>
        <td width="2%">:</td>
        <td>{{ $nomorDokumen ?? '-' }}</td>
    </tr>
    <tr>
        <td>Program Studi</td>
        <td>:</td>
        <td>{{ $namaProdi }}</td>
    </tr>
    <tr>
        <td>Fakultas</td>
        <td>:</td>
        <td>{{ $namaFakultas }}</td>
    </tr>
    <tr>
        <td>Tahun Akademik</td>
        <td>:</td>
        <td>{{ $tahunAkademik }}</td>
    </tr>
</table>


<p class="mt-20">
    <strong>Menetapkan:</strong>
</p>

<p style="text-align: justify; font-size:10px;">
    Bahwa nama yang tersebut di bawah ini ditetapkan sebagai
    Pembimbing Akademik pada Program Studi
    {{ $namaProdi }} Fakultas {{ $namaFakultas }}.
</p>


<table style="width:100%; margin-top:15px; font-size:10px;">
    <tr>
        <td width="20%">Nama</td>
        <td width="2%">:</td>
        <td>{{ $namaPembimbing }}</td>
    </tr>

    <tr>
        <td>NIP</td>
        <td>:</td>
        <td>{{ $nipPembimbing ?? '-' }}</td>
    </tr>

    @if(!empty($jabatanPembimbing))
    <tr>
        <td>Jabatan</td>
        <td>:</td>
        <td>{{ $jabatanPembimbing }}</td>
    </tr>
    @endif

    <tr>
        <td>Program Studi</td>
        <td>:</td>
        <td>{{ $namaProdi }}</td>
    </tr>

    <tr>
        <td>Jumlah Mahasiswa</td>
        <td>:</td>
        <td>{{ $jumlahMahasiswa }} mahasiswa</td>
    </tr>
</table>


<p style="text-align: justify; font-size:10px; margin-top:20px;">
    Keputusan ini berlaku sejak tanggal ditetapkan dan akan digunakan
    sebagaimana mestinya. Apabila di kemudian hari terdapat kekeliruan
    dalam keputusan ini, akan dilakukan perbaikan sebagaimana mestinya.
</p>


{{-- Tanda tangan --}}
<div style="margin-top:35px; width:100%;">

    @foreach($signers ?? [] as $signer)

    <div style="width:45%; margin-left:auto; text-align:center;">

        <p style="margin:0;">
            {{ $signer['label'] }}
        </p>

        <br>
        <br>
        <br>

        <p style="margin:0;">
            <strong>
                {{ $signer['namaLengkap'] }}
            </strong>
        </p>

    </div>

    @endforeach

</div>


{{-- QR --}}
@if(!empty($qrCodeBase64))

<div style="margin-top:15px; text-align:center;">

    <img
        src="{{ $qrCodeBase64 }}"
        width="75"
        alt="QR Verifikasi">

    <div style="font-size:8px; margin-top:3px;">
        Dokumen dapat diverifikasi melalui QR Code
    </div>

</div>

@endif
@endsection