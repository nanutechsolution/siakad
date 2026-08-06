@extends('pdf.layout')

@section('content')
    <div class="judul">Surat Keputusan Penugasan Pembimbing Akademik</div>
    <p>Nomor: {{ $record->nomor_sk ?: '-' }}</p>

    <p>Berdasarkan hasil evaluasi dan kebutuhan pembimbingan akademik, dengan ini ditetapkan:</p>

    <table>
        <tr><th style="width:180px;">Jenis Pembimbing</th><td>{{ $record->jenis->label() }}</td></tr>
        <tr><th>Dosen Pembimbing</th><td>{{ $record->dosen?->person?->nama_lengkap }} (NIDN: {{ $record->dosen?->nidn }})</td></tr>
        @if ($record->kelas)
            <tr><th>Kelas</th><td>{{ $record->kelas->nama_kelas }}</td></tr>
        @endif
        @if ($record->mahasiswa)
            <tr><th>Mahasiswa</th><td>{{ $record->mahasiswa->nim }} - {{ $record->mahasiswa->person?->nama_lengkap }}</td></tr>
        @endif
        <tr><th>Tanggal Mulai</th><td>{{ optional($record->tanggal_mulai)->translatedFormat('d F Y') }}</td></tr>
        <tr><th>Semester Mulai</th><td>{{ $record->semesterMulai?->nama_tahun ?? '-' }}</td></tr>
        @if ($record->keterangan)
            <tr><th>Keterangan</th><td>{{ $record->keterangan }}</td></tr>
        @endif
    </table>

    <p style="margin-top: 16px;">Surat keputusan ini berlaku sejak tanggal ditetapkan dan akan ditinjau kembali sesuai kebutuhan.</p>

    <div class="clearfix">
        <div class="ttd">
            <p>Ditetapkan di [Kota], {{ now()->translatedFormat('d F Y') }}</p>
            <p>[Jabatan Penandatangan]</p>
            <p class="nama">[Nama Pejabat]</p>
            <p>NIP/NIDN. [___________]</p>
        </div>
    </div>
@endsection
