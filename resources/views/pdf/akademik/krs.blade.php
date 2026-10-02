@extends('pdf.layouts.base')

@section('title', 'Kartu Rencana Studi - '.$nim)

@php
// Skala otomatis: makin banyak mata kuliah, makin rapat agar tetap 1 lembar.
$jumlahMk = count($items);

if ($jumlahMk <= 10) {
    $fs=9.5; $pad=4;
    } elseif ($jumlahMk <=14) {
    $fs=9; $pad=3;
    } elseif ($jumlahMk <=18) {
    $fs=8.5; $pad=2;
    } elseif ($jumlahMk <=24) {
    $fs=8; $pad=1.5;
    } else {
    $fs=7.5; $pad=1;
    }
    @endphp

    @push('styles')
    <style>
    /* Paksa A4 portrait (menimpa A4 landscape pada layout base). */
    @page {
    size: A4 portrait;
    margin: 145px 36px 65px 36px;
    }

    .krs-judul {
    font-size: 12pt;
    margin: 0 0 1px 0;
    text-align: center;
    }

    .krs-periode {
    font-size: {{ $fs + 0.5 }}pt;
    margin: 0 0 6px 0;
    text-align: center;
    }

    table.krs-info {
    width: 100%;
    border-collapse: collapse;
    margin-top: 4px;
    }

    table.krs-info td {
    font-size: {{ $fs }}pt;
    padding: 1px 2px;
    vertical-align: top;
    }

    table.data.krs {
    margin-top: 6px;
    }

    table.data.krs th,
    table.data.krs td {
    font-size: {{ $fs }}pt;
    padding: {{ $pad }}px 4px;
    line-height: 1.15;
    }

    .krs-meta {
    font-size: 8pt;
    margin: 6px 0 0 0;
    }

    table.krs-ttd {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    page-break-inside: avoid;
    }

    table.krs-ttd td {
    font-size: {{ $fs }}pt;
    text-align: center;
    vertical-align: top;
    padding: 0;
    }

    .krs-ttd-ruang {
    height: 40px;
    }

    .krs-ttd p {
    margin: 0;
    }

    .krs-qr-cap {
    font-size: 6.5pt;
    }
    </style>
    @endpush

    @section('content')
    <div class="avoid-break">
        <h3 class="krs-judul">KARTU RENCANA STUDI (KRS)</h3>
        <p class="krs-periode">{{ $namaTahunAkademik }} — Semester {{ $semester }}</p>

        <table class="krs-info">
            <tr>
                <td width="14%">NIM</td>
                <td width="2%">:</td>
                <td width="34%">{{ $nim }}</td>
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
                    @if(!empty($nidnDosenWali))
                    (NIDN {{ $nidnDosenWali }})
                    @elseif(!empty($nuptkDosenWali))
                    (NUPTK {{ $nuptkDosenWali }})
                    @endif
                </td>
                <td>Status KRS</td>
                <td>:</td>
                <td>{{ $statusKrs }}</td>
            </tr>
        </table>

        <table class="data krs">
            <thead>
                <tr>
                    <th width="4%">No</th>
                    <th width="11%">Kode MK</th>
                    <th>Nama Mata Kuliah</th>
                    <th width="6%">SKS</th>
                    <th width="11%">Kelas</th>
                    <th width="15%">Jadwal</th>
                    <th width="10%">Ruang</th>
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

        <p class="krs-meta">
            Disetujui pada: {{ $disetujuiPada ?? 'Belum disetujui' }} — Dicetak pada: {{ $dicetakPada }}
        </p>

        {{--
        Blok tanda tangan + QR dalam satu baris (3 kolom) agar hemat tinggi.

        TTD digital (PdfSigner) TIDAK dipakai di sini: KRS boleh dicetak kapan
        saja oleh setiap mahasiswa, sedangkan PdfSigner melempar error bila
        otoritas/pejabat belum dikonfigurasi.
    --}}
        <table class="krs-ttd">
            <tr>
                <td width="36%">
                    <p>Mahasiswa,</p>
                    <div class="krs-ttd-ruang"></div>
                    <p><strong>{{ $namaMahasiswa }}</strong></p>
                    <p style="font-size:8pt;">NIM {{ $nim }}</p>
                </td>

                <td width="28%">
                    @if(!empty($qrCodeBase64))
                    <img src="{{ $qrCodeBase64 }}" width="62"><br>
                    <span class="krs-qr-cap">Pindai untuk verifikasi keaslian</span>
                    @endif
                </td>

                <td width="36%">
                    <p>Dosen Wali,</p>
                    <div class="krs-ttd-ruang"></div>
                    <p><strong>{{ $namaDosenWali ?? '................................................' }}</strong></p>
                    <p style="font-size:8pt;">
                        @if(!empty($nidnDosenWali))
                        NIDN {{ $nidnDosenWali }}
                        @elseif(!empty($nuptkDosenWali))
                        NUPTK {{ $nuptkDosenWali }}
                        @else
                        NIDN ..............................
                        @endif
                    </p>
                </td>
            </tr>
        </table>
    </div>
    @endsection