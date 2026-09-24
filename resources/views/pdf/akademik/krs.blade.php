@extends('pdf.layouts.base')

@section('title', 'Kartu Rencana Studi - '.$nim)

@section('content')
<h3 class="text-center" style="margin-bottom:2px;">KARTU RENCANA STUDI (KRS)</h3>
<p class="text-center" style="margin-top:0;">{{ $namaTahunAkademik }} — Semester {{ $semester }}</p>

<table style="width:100%; margin-top:10px; font-size:10px;">
    <tr>
        <td width="15%">NIM</td>
        <td width="2%">:</td>
        <td width="33%">{{ $nim }}</td>
        <td width="15%">Program Studi</td>
        <td width="2%">:</td>
        <td width="33%">{{ $namaProdi }} ({{ $jenjang }})</td>
    </tr>
    <tr>
        <td>Nama</td>
        <td>:</td>
        <td>{{ $namaMahasiswa }}</td>
        <td>Fakultas</td>
        <td>:</td>
        <td>{{ $namaFakultas }}</td>
    </tr>
    <tr>
        <td>Dosen Wali</td>
        <td>:</td>
        <td>
            {{ $namaDosenWali ?? '-' }}
            @if($nidnDosenWali)
                (NIDN {{ $nidnDosenWali }})
            @elseif($nuptkDosenWali ?? null)
                (NUPTK {{ $nuptkDosenWali }})
            @endif
        </td>
        <td>Status KRS</td>
        <td>:</td>
        <td>{{ $statusKrs }}</td>
    </tr>
</table>

<table class="data mt-10">
    <thead>
        <tr>
            <th width="4%">No</th>
            <th width="12%">Kode MK</th>
            <th>Nama Mata Kuliah</th>
            <th width="6%">SKS</th>
            <th width="14%">Kelas</th>
            <th width="16%">Jadwal</th>
            <th width="12%">Ruang</th>
            <th width="16%">Dosen Pengampu</th>
            <th width="8%">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $item)
        <tr>
            <td class="text-center">{{ $i + 1 }}</td>
            <td>{{ $item['kodeMk'] }}</td>
            <td>{{ $item['namaMk'] }}</td>
            <td class="text-center">{{ $item['sks'] }}</td>
            <td>{{ $item['kelas'] }}</td>
            <td>{{ $item['jadwal'] }}</td>
            <td>{{ $item['ruang'] }}</td>
            <td>{{ $item['dosen'] }}</td>
            <td class="text-center">{{ $item['statusAmbil'] }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-right"><strong>Total SKS Diambil</strong></td>
            <td class="text-center"><strong>{{ $totalSks }}</strong></td>
            <td colspan="5"></td>
        </tr>
    </tfoot>
</table>

<p class="mt-20" style="font-size:9px;">
    Disetujui pada: {{ $disetujuiPada ?? 'Belum disetujui' }} — Dicetak pada: {{ $dicetakPada }}
</p>

{{--
    Blok tanda tangan.

    - Mahasiswa di kiri  : pernyataan menyetujui isi KRS.
    - Dosen Wali di kanan: persetujuan akademik.

    TTD digital (PdfSigner) TIDAK dipakai di sini: KRS boleh dicetak kapan
    saja oleh setiap mahasiswa, sedangkan PdfSigner melempar error bila
    otoritas/pejabat belum dikonfigurasi — KRS yang tadinya bisa dicetak
    jadi gagal. Data dua nama ini sudah tersedia dari resolver.
--}}
<div style="margin-top:35px; width:100%;">
    <table style="width:100%;">
        <tr>
            <td width="45%" style="text-align:center; vertical-align:top;">
                <p style="margin:0;">Mahasiswa,</p>
                <br>
                <br>
                <br>
                <p style="margin:0;"><strong>{{ $namaMahasiswa }}</strong></p>
                <p style="margin:0; font-size:9px;">NIM {{ $nim }}</p>
            </td>

            <td width="10%"></td>

            <td width="45%" style="text-align:center; vertical-align:top;">
                <p style="margin:0;">
                    Dosen Wali,
                </p>
                <br>
                <br>
                <br>
                <p style="margin:0;">
                    <strong>{{ $namaDosenWali ?? '................................................' }}</strong>
                </p>
                <p style="margin:0; font-size:9px;">
                    @if($nidnDosenWali)
                        NIDN {{ $nidnDosenWali }}
                    @elseif($nuptkDosenWali ?? null)
                        NUPTK {{ $nuptkDosenWali }}
                    @else
                        NIDN ..............................
                    @endif
                </p>
            </td>
        </tr>
    </table>
</div>

{{-- QR verifikasi: hanya tampil jika KRS dicetak lewat generateArchived() --}}
@if(!empty($qrCodeBase64))
    <table style="width:100%; margin-top:25px; font-size:10px;">
        <tr>
            <td width="70%"></td>
            <td width="30%" class="text-center">
                <img src="{{ $qrCodeBase64 }}" width="70"><br>
                <span style="font-size:7px;">Pindai untuk verifikasi keaslian</span>
            </td>
        </tr>
    </table>
@endif

@endsection