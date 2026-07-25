@extends('pdf.layouts.base')

@section('title', 'Kartu Ujian '.$jenisUjian.' - '.$nim)

@section('content')
    <h3 class="text-center" style="margin-bottom:2px;">KARTU PESERTA UJIAN {{ $jenisUjian }}</h3>

    <table style="width:100%; margin-top:10px; font-size:10px;">
        <tr>
            <td width="15%">NIM</td><td width="2%">:</td><td width="33%">{{ $nim }}</td>
            <td width="15%">Program Studi</td><td width="2%">:</td><td width="33%">{{ $namaProdi }}</td>
        </tr>
        <tr>
            <td>Nama</td><td>:</td><td colspan="4">{{ $namaMahasiswa }}</td>
        </tr>
    </table>

    <table class="data mt-10">
        <thead>
            <tr>
                <th width="4%">No</th>
                <th width="12%">Kode MK</th>
                <th>Nama Mata Kuliah</th>
                <th width="12%">Tanggal</th>
                <th width="12%">Jam</th>
                <th width="10%">Metode</th>
                <th width="12%">Ruang</th>
                <th width="10%">No. Kursi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item['kodeMk'] }}</td>
                    <td>{{ $item['namaMk'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($item['tanggal'])->translatedFormat('d M Y') }}</td>
                    <td>{{ $item['jamMulai'] }} - {{ $item['jamSelesai'] }}</td>
                    <td>{{ $item['metode'] }}</td>
                    <td>{{ $item['ruang'] }}</td>
                    <td class="text-center">{{ $item['nomorKursi'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }} — Kartu ini wajib dibawa saat pelaksanaan ujian.</p>
@endsection