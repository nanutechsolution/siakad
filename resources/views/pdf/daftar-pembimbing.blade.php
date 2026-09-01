@extends('pdf.layouts.base')

@section('title', 'Daftar Rekap Pembimbing Akademik')

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">DAFTAR REKAPITULASI PEMBIMBING AKADEMIK AKTIF</h3>

@if(!empty($filters['prodi_id']) || !empty($filters['angkatan_id']))
<p class="text-center" style="margin-top:0; font-size:10px;">
    @if(!empty($filters['prodi_id'])) Prodi ID: {{ $filters['prodi_id'] }} @endif
    @if(!empty($filters['prodi_id']) && !empty($filters['angkatan_id'])) | @endif
    @if(!empty($filters['angkatan_id'])) Angkatan: {{ $filters['angkatan_id'] }} @endif
</p>
@else
<p class="text-center" style="margin-top:0; font-size:10px;">Keseluruhan Data</p>
@endif

<table class="data mt-10">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="20%">Dosen Pembimbing</th>
            <th width="10%">NIDN</th>
            <th width="8%">Target</th>
            <th>Nama Target (Kelas/Mhs)</th>
            <th width="15%">Program Studi</th>
            <th width="8%">Angkatan</th>
            <th width="10%">Mulai</th>
        </tr>
    </thead>
    <tbody>
        @forelse($records as $index => $row)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $row->dosen?->person?->nama_lengkap ?? '-' }}</td>
            <td class="text-center">{{ $row->dosen?->nidn ?? '-' }}</td>
            <td class="text-center">{{ $row->mahasiswa_id ? 'Mahasiswa' : 'Kelas' }}</td>
            <td>
                @if($row->mahasiswa_id)
                {{ $row->mahasiswa?->nim }} - {{ $row->mahasiswa?->person?->nama_lengkap }}
                @elseif($row->kelas_id)
                {{ $row->kelas?->nama_kelas }}
                @else
                -
                @endif
            </td>
            <td>{{ $row->mahasiswa?->prodi?->nama_prodi ?? $row->kelas?->prodi?->nama_prodi ?? '-' }}</td>
            <td class="text-center">{{ $row->mahasiswa?->angkatan_id ?? $row->kelas?->angkatan_id ?? '-' }}</td>
            <td class="text-center">{{ $row->tanggal_mulai ? \Carbon\Carbon::parse($row->tanggal_mulai)->format('d/m/Y') : '-' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">Tidak ada data penugasan yang ditemukan.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<p class="mt-20" style="font-size:9px;">Dicetak pada: {{ now()->translatedFormat('d F Y H:i:s') }}</p>
@endsection