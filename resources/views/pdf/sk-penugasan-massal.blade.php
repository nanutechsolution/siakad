@extends('pdf.layouts.base')

<!-- PERBAIKAN 1: title sekarang menggunakan $namaDosen -->
@section('title', 'SK Penugasan Massal - ' . $namaDosen)

@section('content')
<h3 class="text-center" style="margin-bottom:2px; text-decoration: underline;">LAMPIRAN SURAT KEPUTUSAN PENUGASAN DOSEN WALI</h3>
<p class="text-center" style="margin-top:0;">Nomor: ________________________</p>

<!-- PERBAIKAN 2: Data tabel langsung memanggil variabel DTO -->
<table style="width:100%; margin-top:15px; font-size:10px;">
    <tr>
        <td width="15%">Nama Dosen</td>
        <td width="2%">:</td>
        <td width="83%" style="font-weight: bold;">{{ $namaDosen }}</td>
    </tr>
    <tr>
        <td>NIDN / NIP</td>
        <td>:</td>
        <td>{{ $nidn }}</td>
    </tr>
    <tr>
        <td>Program Studi</td>
        <td>:</td>
        <td>{{ $namaProdi }}</td>
    </tr>
</table>

<p style="font-size:10px; margin-top:15px; margin-bottom:5px;">Ditugaskan sebagai Dosen Pembimbing Akademik (Dosen Wali) bagi mahasiswa/kelas berikut:</p>

<table class="data mt-10">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="12%">Jenis Target</th>
            <th>Nama Target / Mahasiswa</th>
            <th width="25%">Program Studi</th>
            <th width="10%">Angkatan</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $row)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td class="text-center">{{ $row->mahasiswa_id ? 'Mahasiswa' : 'Kelas' }}</td>
            <td>
                @if($row->mahasiswa_id)
                {{ $row->mahasiswa?->nim }} - {{ $row->mahasiswa?->person?->nama_lengkap }}
                @elseif($row->kelas_id)
                Kelas: {{ $row->kelas?->nama_kelas }}
                @else
                -
                @endif
            </td>
            <td>{{ $row->mahasiswa?->prodi?->nama_prodi ?? $row->kelas?->prodi?->nama_prodi ?? '-' }}</td>
            <td class="text-center">{{ $row->mahasiswa?->angkatan_id ?? $row->kelas?->angkatan_id ?? '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Belum ada penugasan pembimbing aktif.</td>
        </tr>
        @endforelse
    </tbody>
</table>

@include('pdf.partials.qr-and-signatures')
@endsection