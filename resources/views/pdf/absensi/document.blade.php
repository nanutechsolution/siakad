@extends('pdf.layouts.base')

@section('title', 'Pusat Absensi')

@section('content')
@php
    $title = match ($mode) {
        'online' => 'HASIL ABSENSI ONLINE',
        'template' => 'TEMPLATE DAFTAR HADIR',
        default => 'DAFTAR HADIR PERKULIAHAN',
    };
    $columns = match ($mode) {
        'online' => ['No', 'NIM', 'Nama Mahasiswa', 'Status', 'Jam', 'Keterangan'],
        'template' => array_merge(['No', 'NIM', 'Nama Mahasiswa'], array_map(fn ($number) => 'P' . $number, $pertemuan)),
        default => ['No', 'NIM', 'Nama Mahasiswa', 'Tanda Tangan / Keterangan'],
    };
@endphp
<style>
    .doc-title { text-align: center; font-size: 14pt; font-weight: bold; margin: 0 0 12px; }
    .meta { width: 100%; border-collapse: collapse; margin: 0 0 10px; }
    .meta td { padding: 2px 4px; font-size: 9pt; vertical-align: top; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th, table.data td { border: 1px solid #222; padding: 5px; font-size: {{ $mode === 'template' ? '8pt' : '9pt' }}; }
    table.data th { text-align: center; background: #e8eef5; }
    table.data thead { display: table-header-group; }
    table.data tr { page-break-inside: avoid; }
    .summary { font-size: 9pt; margin: 8px 0; }
    .center { text-align: center; }
</style>
<h1 class="doc-title">{{ $title }}</h1>
<table class="meta">
    <tr><td width="17%">Tahun Akademik</td><td width="33%">: {{ $akademik['tahun_akademik'] }} ({{ $akademik['semester'] }})</td><td width="17%">Program Studi</td><td>: {{ $akademik['prodi'] }}</td></tr>
    <tr><td>Mata Kuliah</td><td>: {{ trim($akademik['kode_mk'] . ' - ' . $akademik['mata_kuliah'], ' -') }}</td><td>Kelas / SKS</td><td>: {{ trim($akademik['kelas'] . ' / ' . $akademik['sks'], ' /') }}</td></tr>
    <tr><td>Dosen Pengampu</td><td>: {{ $akademik['dosen'] }}</td><td>Ruang</td><td>: {{ $akademik['ruang'] }}</td></tr>
    <tr><td>Hari / Jam</td><td>: {{ trim($akademik['hari'] . ' ' . $akademik['jam']) }}</td><td>Pertemuan / Tanggal</td><td>: {{ $akademik['pertemuan'] ? 'P' . $akademik['pertemuan'] : '—' }} / {{ $akademik['tanggal'] ?: '—' }}</td></tr>
</table>
@if ($mode === 'online')
<p class="summary">Hadir: {{ $summary['hadir'] }} &nbsp; Izin: {{ $summary['izin'] }} &nbsp; Sakit: {{ $summary['sakit'] }} &nbsp; Alpa: {{ $summary['alpa'] }} &nbsp; Belum presensi/tercatat: {{ $summary['belum_presensi'] }}</p>
@endif
<table class="data">
    <thead><tr>@foreach ($columns as $column)<th>{{ $column }}</th>@endforeach</tr></thead>
    <tbody>
    @forelse ($rows as $row)
        <tr>
            <td class="center">{{ $row['no'] }}</td><td>{{ $row['nim'] }}</td><td>{{ $row['nama'] }}</td>
            @if ($mode === 'online')
                <td>{{ $row['status'] }}</td><td class="center">{{ $row['waktu'] }}</td><td>{{ $row['keterangan'] }}</td>
            @elseif ($mode === 'template')
                @foreach ($pertemuan as $number)<td>&nbsp;</td>@endforeach
            @else
                <td style="height: 28px"></td>
            @endif
        </tr>
    @empty
        <tr><td class="center" colspan="{{ count($columns) }}">Tidak ada mahasiswa pada data yang dipilih.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
