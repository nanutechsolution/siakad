@php
$kopSurat = app(\App\Services\Pdf\KopSuratResolver::class)->resolve();
$logoDataUri = null;

if (! empty($kopSurat['logoAbsolutePath']) && is_file($kopSurat['logoAbsolutePath'])) {
$mime = mime_content_type($kopSurat['logoAbsolutePath']) ?: 'image/png';
$logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($kopSurat['logoAbsolutePath']));
}
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Kartu Rencana Studi - {{ $nim }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Times New Roman', 'Liberation Serif', Times, serif;
            font-size: 10pt;
            line-height: 1.25;
            color: #000;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* KOP */
        table.kop {
            width: 100%;
            border-collapse: collapse;
        }

        table.kop td {
            padding: 0;
            vertical-align: middle;
            text-align: center;
        }

        table.kop .logo-col {
            width: 14%;
        }

        table.kop .logo-col img {
            display: block;
            max-width: 64px;
            height: auto;
            margin: 0 auto;
        }

        table.kop .text-col {
            width: 72%;
        }

        table.kop .dummy-col {
            width: 14%;
        }

        .institusi {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 1px 0;
        }

        .akreditasi {
            font-size: 9pt;
            font-weight: bold;
            margin: 0 0 2px 0;
        }

        .kontak {
            font-size: 8pt;
            margin: 0;
            line-height: 1.25;
        }

        .garis-ganda {
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin: 6px 0 8px 0;
        }

        /* JUDUL */
        .krs-judul {
            font-size: 12.5pt;
            font-weight: bold;
            text-align: center;
            margin: 0 0 1px 0;
        }

        .krs-periode {
            font-size: 10pt;
            text-align: center;
            margin: 0 0 6px 0;
        }

        /* IDENTITAS */
        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.info td {
            font-size: 9.5pt;
            padding: 1px 2px;
            vertical-align: top;
        }

        /* TABEL MATA KULIAH */
        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data th,
        table.data td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 8.5pt;
            line-height: 1.2;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        table.data th {
            background: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        table.data tr {
            page-break-inside: avoid;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .krs-meta {
            font-size: 8pt;
            margin: 6px 0 0 0;
        }

        /* TANDA TANGAN + QR */
        table.ttd {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }

        table.ttd td {
            font-size: 9.5pt;
            text-align: center;
            vertical-align: top;
            padding: 0;
        }

        table.ttd p {
            margin: 0;
        }

        .ttd-ruang {
            height: 48px;
        }

        .ttd-kecil {
            font-size: 8pt;
        }

        .qr-cap {
            font-size: 6.5pt;
        }

        /* FOOTER */
        .krs-footer {
            margin-top: 10px;
            padding-top: 4px;
            border-top: 1px solid #999;
            text-align: center;
            font-family: Arial, 'Liberation Sans', Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #555;
        }
    </style>
</head>

<body>
    <div id="krs-page">

        {{-- KOP SURAT --}}
        <table class="kop">
            <tr>
                <td class="logo-col">
                    @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="Logo">
                    @endif
                </td>

                <td class="text-col">
                    <div class="institusi">{{ $kopSurat['nama'] ?? 'NAMA INSTITUSI' }}</div>

                    @if(! empty($kopSurat['akreditasi']))
                    <div class="akreditasi">
                        "{{ $kopSurat['akreditasi'] }}"
                        @if(! empty($kopSurat['nomorAkreditasi']))
                        | SK: {{ $kopSurat['nomorAkreditasi'] }}
                        @endif
                    </div>
                    @endif

                    <div class="kontak">{{ $kopSurat['alamat'] ?? '' }}</div>
                    <div class="kontak">
                        Telp: {{ $kopSurat['telepon'] ?? '-' }}
                        | Surel: {{ $kopSurat['email'] ?? '-' }}
                        | Laman: {{ $kopSurat['website'] ?? '-' }}
                    </div>
                </td>

                <td class="dummy-col"></td>
            </tr>
        </table>
        <div class="garis-ganda"></div>

        {{-- JUDUL --}}
        <h3 class="krs-judul">KARTU RENCANA STUDI (KRS)</h3>
        <p class="krs-periode">{{ $namaTahunAkademik }} — Semester {{ $semester }}</p>

        {{-- IDENTITAS --}}
        <table class="info">
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
                    @if(! empty($nidnDosenWali))
                    (NIDN {{ $nidnDosenWali }})
                    @elseif(! empty($nuptkDosenWali))
                    (NUPTK {{ $nuptkDosenWali }})
                    @endif
                </td>
                <td>Status KRS</td>
                <td>:</td>
                <td>{{ $statusKrs }}</td>
            </tr>
        </table>

        {{-- TABEL MATA KULIAH --}}
        <table class="data">
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

        {{-- TANDA TANGAN + QR --}}
        <table class="ttd">
            <tr>
                <td width="36%">
                    <p>Mahasiswa,</p>
                    <div class="ttd-ruang"></div>
                    <p><strong>{{ $namaMahasiswa }}</strong></p>
                    <p class="ttd-kecil">NIM {{ $nim }}</p>
                </td>

                <td width="28%">
                    @if(! empty($qrCodeBase64))
                    <img src="{{ $qrCodeBase64 }}" width="66" alt="QR"><br>
                    <span class="qr-cap">Pindai untuk verifikasi keaslian</span>
                    @endif
                </td>

                <td width="36%">
                    <p>Dosen Wali,</p>
                    <div class="ttd-ruang"></div>
                    <p><strong>{{ $namaDosenWali ?? '................................................' }}</strong></p>
                    <p class="ttd-kecil">
                        @if(! empty($nidnDosenWali))
                        NIDN {{ $nidnDosenWali }}
                        @elseif(! empty($nuptkDosenWali))
                        NUPTK {{ $nuptkDosenWali }}
                        @else
                        NIDN ..............................
                        @endif
                    </p>
                </td>
            </tr>
        </table>

        <div class="krs-footer">
            Dicetak melalui SIAKAD pada {{ now()->translatedFormat('d F Y H:i') }} WITA
        </div>
    </div>

    {{-- Paksa 1 lembar: ukur tinggi asli, kecilkan skala bila melebihi area cetak. --}}
    <script>
        (function() {
            var page = document.getElementById('krs-page');
            // Tinggi area cetak: 297mm - 2 x 10mm margin, dalam px (96 dpi), dikurangi cadangan 8px.
            var maxPx = Math.floor((297 - 20) / 25.4 * 96) - 8;
            var tinggi = page.offsetHeight;

            if (tinggi > maxPx) {
                page.style.zoom = Math.max(0.5, (maxPx / tinggi) * 0.97);
            }
        })();
    </script>
</body>

</html>