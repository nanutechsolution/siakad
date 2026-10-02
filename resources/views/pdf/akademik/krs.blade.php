@extends('pdf.layouts.base')

@section('title', 'Kartu Rencana Studi - '.$nim)

@php
// Skala otomatis berdasarkan jumlah mata kuliah.
$jumlahMk = count($items);

if ($jumlahMk <= 12) {
    $fs=9; $pad=3;
    } elseif ($jumlahMk <=16) {
    $fs=8.5; $pad=2.5;
    } elseif ($jumlahMk <=20) {
    $fs=8; $pad=2;
    } else {
    $fs=7.5; $pad=1.5;
    }
    @endphp

    @push('styles')
    <style>
    /* Paksa A4 portrait (menimpa A4 landscape pada layout base). */
    @page {
    size: A4 portrait;
    margin: 145px 36px 60px 36px;
    }

    /* Kop disesuaikan untuk lebar portrait agar tidak membungkus berlebihan. */
    .kop-table .logo-col { width: 12%; }
    .kop-table .text-col { width: 76%; }
    .kop-table .dummy-col { width: 12%; }
    .kop-table .logo-col img { max-width: 56px; }
    .institusi { font-size: 13pt; letter-spacing: 0; margin-bottom: 1px; }
    .akreditasi { font-size: 8.5pt; margin-bottom: 2px; }
    .kontak { font-size: 7.5pt; line-height: 1.2; }
    .garis-ganda { margin-top: 6px; }

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
    table-layout: fixed;
    margin-top: 6px;
    }

    table.data.krs th,
    table.data.krs td {
    font-size: {{ $fs }}pt;
    padding: {{ $pad }}px 3px;
    line-height: 1.15;
    word-wrap: break-word;
    }

    table.data.krs tr {
    page-break-inside: auto;
    }

    .krs-meta {
    font-size: 8pt;
    margin: 6px 0 0 0;
    }

    table.krs-ttd {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
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
    <div>
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
                    <th width="9%">Kode MK</th>
                    <th width="25%">Nama Mata Kuliah</th>
                    <th width="5%">SKS</th>
                    <th width="8%">Kelas</th>
                    <th width="17%">Jadwal</th>
                    <th width="9%">Ruang</th>
                    <th width="17%">Dosen Pengampu</th>
                    <th width="6%">Status</th>
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
        Tanda tangan + QR dalam satu baris (3 kolom) agar hemat tinggi.
        TTD digital (PdfSigner) TIDAK dipakai: KRS boleh dicetak kapan saja
        oleh setiap mahasiswa, sedangkan PdfSigner melempar error bila
        pejabat belum dikonfigurasi.
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