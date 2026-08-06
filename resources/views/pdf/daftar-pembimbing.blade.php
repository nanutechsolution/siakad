@extends('pdf.layout')

@section('content')
    <div class="judul">Daftar Pembimbing Akademik Aktif</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis</th>
                <th>Kelas</th>
                <th>Mahasiswa</th>
                <th>Dosen</th>
                <th>Tanggal Mulai</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->jenis->label() }}</td>
                    <td>{{ $r->kelas?->nama_kelas ?? '-' }}</td>
                    <td>{{ $r->mahasiswa ? $r->mahasiswa->nim.' - '.$r->mahasiswa->person?->nama_lengkap : '-' }}</td>
                    <td>{{ $r->dosen?->person?->nama_lengkap }} ({{ $r->dosen?->nidn }})</td>
                    <td>{{ optional($r->tanggal_mulai)->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">Tidak ada data untuk filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
