@extends('pdf.layout')

@section('content')
    <div class="judul">Daftar Bimbingan Akademik</div>
    <p>Dosen: <strong>{{ $dosen->person?->nama_lengkap }}</strong> (NIDN: {{ $dosen->nidn }})</p>
    <p>Total bimbingan aktif: <strong>{{ $records->count() }}</strong></p>

    <table>
        <thead>
            <tr><th>No</th><th>Jenis</th><th>Kelas</th><th>Mahasiswa</th><th>Sejak</th></tr>
        </thead>
        <tbody>
            @forelse ($records as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->jenis->label() }}</td>
                    <td>{{ $r->kelas?->nama_kelas ?? '-' }}</td>
                    <td>{{ $r->mahasiswa ? $r->mahasiswa->nim.' - '.$r->mahasiswa->person?->nama_lengkap : '-' }}</td>
                    <td>{{ optional($r->tanggal_mulai)->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">Belum ada bimbingan aktif.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
