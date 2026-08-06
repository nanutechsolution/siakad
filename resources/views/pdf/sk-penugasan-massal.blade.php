@extends('pdf.layout')

@section('content')
    <div class="judul">Surat Keputusan Penugasan Pembimbing Akademik</div>
    <p>Dosen Pembimbing: <strong>{{ $dosen->person?->nama_lengkap }}</strong> (NIDN: {{ $dosen->nidn }})</p>
    <p>Jumlah penugasan aktif: <strong>{{ $records->count() }}</strong></p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis</th>
                <th>Kelas</th>
                <th>Mahasiswa</th>
                <th>Tanggal Mulai</th>
                <th>Nomor SK</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->jenis->label() }}</td>
                    <td>{{ $r->kelas?->nama_kelas ?? '-' }}</td>
                    <td>{{ $r->mahasiswa ? $r->mahasiswa->nim.' - '.$r->mahasiswa->person?->nama_lengkap : '-' }}</td>
                    <td>{{ optional($r->tanggal_mulai)->format('d-m-Y') }}</td>
                    <td>{{ $r->nomor_sk ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;">Tidak ada penugasan aktif untuk dosen ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="ttd">
            <p>Ditetapkan di [Kota], {{ now()->translatedFormat('d F Y') }}</p>
            <p>[Jabatan Penandatangan]</p>
            <p class="nama">[Nama Pejabat]</p>
            <p>NIP/NIDN. [___________]</p>
        </div>
    </div>
@endsection
