@extends('pdf.layouts.base')

@section('title', 'Jadwal Kuliah - '.$nim)

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">JADWAL KULIAH</h3>
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
        <td colspan="4">{{ $namaMahasiswa }}</td>
    </tr>
</table>

<table class="data mt-10">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="10%">Hari</th>
            <th width="12%">Jam</th>
            <th width="12%">Kode MK</th>
            <th>Nama Mata Kuliah</th>
            <th width="6%">SKS</th>
            <th width="14%">Kelas</th>
            <th width="12%">Ruang</th>
            <th width="18%">Dosen</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $item['hari'] }}</td>
            <td>{{ $item['jamMulai'] }} - {{ $item['jamSelesai'] }}</td>
            <td>{{ $item['kodeMk'] }}</td>
            <td>{{ $item['namaMk'] }}</td>
            <td class="text-center">{{ $item['sks'] }}</td>
            <td>{{ $item['kelas'] }}</td>
            <td>{{ $item['ruang'] }}</td>
            <td>{{ $item['dosen'] }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">Belum ada jadwal kuliah aktif pada semester ini.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ $dicetakPada }}</p>
@endsection