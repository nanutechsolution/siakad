@extends('pdf.layouts.base')

@section('title', 'Surat Keterangan Pindah Prodi - '.$nim)

@section('content')
    <h3 class="text-center" style="margin-bottom:2px;">SURAT KETERANGAN PINDAH PROGRAM STUDI</h3>
    <p class="text-center" style="margin-top:0;">Nomor: {{ $nomorDokumen ?? '-' }}</p>

    <p class="mt-20">Yang bertanda tangan di bawah ini menerangkan bahwa:</p>

    <table style="width:100%; margin-top:10px; font-size:10px;">
        <tr><td width="25%">Nama</td><td width="2%">:</td><td>{{ $namaMahasiswa }}</td></tr>
        <tr><td>NIM</td><td>:</td><td>{{ $nim }}</td></tr>
    </table>

    <p class="mt-20">
        telah dipindahkan dari Program Studi <strong>{{ $prodiAsal }}</strong> ke Program Studi <strong>{{ $prodiTujuan }}</strong>
        terhitung sejak tanggal {{ \Carbon\Carbon::parse($tanggalBerlaku)->translatedFormat('d F Y') }}.
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