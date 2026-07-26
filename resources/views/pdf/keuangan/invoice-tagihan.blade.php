@extends('pdf.layouts.base')

@section('title', 'Tagihan - '.$kodeTransaksi)

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">TAGIHAN / INVOICE</h3>
<p class="text-center" style="margin-top:0;">No. {{ $kodeTransaksi }}</p>

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
    <tr>
        <td>Status</td>
        <td>:</td>
        <td colspan="4">
            {{ $statusBayar }}
            @if($tenggatWaktu) — Jatuh tempo {{ \Carbon\Carbon::parse($tenggatWaktu)->translatedFormat('d M Y') }} @endif
        </td>
    </tr>
</table>

<table class="data mt-10">
    <thead>
        <tr>
            <th width="6%">No</th>
            <th>Komponen Biaya</th>
            <th width="16%">Nominal Dasar</th>
            <th width="16%">Diskon</th>
            <th width="16%">Tagihan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $item['namaKomponen'] }}</td>
            <td class="text-right">{{ number_format($item['nominalDasar'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item['nominalDiskon'], 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item['nominalTagihan'], 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" class="text-right"><strong>Total Tagihan</strong></td>
            <td class="text-right"><strong>{{ number_format($totalTagihan, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td colspan="4" class="text-right">Total Terbayar</td>
            <td class="text-right">{{ number_format($totalBayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right"><strong>Sisa Tagihan</strong></td>
            <td class="text-right"><strong>{{ number_format($sisaTagihan, 0, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
</table>

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }} — Dokumen ini mencerminkan status tagihan saat dicetak dan dapat berubah bila ada pembayaran/penyesuaian baru.</p>
@endsection