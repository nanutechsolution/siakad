@extends('pdf.layout')

@section('content')
    <div class="judul">Laporan Monitoring Pembimbing Akademik</div>
    <p>Tanggal cetak: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table style="width: 60%;">
        <tr><th>Total Mahasiswa Aktif</th><td>{{ $total }}</td></tr>
        <tr><th>Sudah Punya Dosen Wali</th><td>{{ $sudah }}</td></tr>
        <tr><th>Belum Punya Dosen Wali</th><td>{{ $belum }}</td></tr>
    </table>

    <p class="subjudul">Top Beban Bimbingan Dosen</p>
    <table>
        <thead><tr><th>No</th><th>Dosen</th><th>NIDN</th><th>Jumlah Bimbingan</th></tr></thead>
        <tbody>
            @forelse ($bebanDosen as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['dosen']->person?->nama_lengkap }}</td>
                    <td>{{ $item['dosen']->nidn }}</td>
                    <td>{{ $item['total'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;">Belum ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="subjudul">Mahasiswa Belum Punya Dosen Wali ({{ $mahasiswaTanpaWali->count() }})</p>
    <table>
        <thead><tr><th>No</th><th>NIM</th><th>Nama</th><th>Program Studi</th><th>Angkatan</th></tr></thead>
        <tbody>
            @forelse ($mahasiswaTanpaWali as $i => $m)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $m->nim }}</td>
                    <td>{{ $m->person?->nama_lengkap }}</td>
                    <td>{{ $m->prodi?->nama_prodi }}</td>
                    <td>{{ $m->angkatan_id }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">Semua mahasiswa sudah punya wali.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection
